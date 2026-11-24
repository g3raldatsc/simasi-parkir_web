<?php

namespace App\Services;

use App\Models\MapCell;
use App\Models\Slot;
use Illuminate\Support\Collection;
use SplQueue;

class ParkingMapService
{
    public const ENTRANCE_X = 8;

    public const ENTRANCE_Y = 0;

    /**
     * Get all grid cells formatted for the map layout.
     *
     * @return Collection<int, array{
     *     id: int,
     *     x: int,
     *     y: int,
     *     type: string,
     *     label: ?string,
     *     slot: ?array{
     *         id: int,
     *         number: int,
     *         block_code: string,
     *         block_name: string,
     *         is_active: bool,
     *         is_occupied: bool,
     *         vehicle_plate: ?string
     *     }
     * }>
     */
    public function getGridCells(): Collection
    {
        $cells = MapCell::query()
            ->with(['slot.block', 'slot.activeTransaction.vehicle'])
            ->orderBy('y', 'desc')
            ->orderBy('x', 'asc')
            ->get();

        return $cells->map(function (MapCell $cell): array {
            $slotData = null;

            if ($cell->slot !== null) {
                $activeTx = $cell->slot->activeTransaction;
                $slotData = [
                    'id' => $cell->slot->id,
                    'number' => $cell->slot->number,
                    'block_code' => $cell->slot->block?->code ?? '',
                    'block_name' => $cell->slot->block?->name ?? '',
                    'is_active' => (bool) $cell->slot->is_active,
                    'is_occupied' => $activeTx !== null,
                    'vehicle_plate' => $activeTx?->vehicle?->plate_number,
                ];
            }

            return [
                'id' => $cell->id,
                'x' => $cell->x,
                'y' => $cell->y,
                'type' => $cell->type,
                'label' => $cell->label,
                'slot' => $slotData,
            ];
        });
    }

    /**
     * Get statistics of parking slots.
     *
     * @return array{
     *     total: int,
     *     occupied: int,
     *     available: int,
     *     percentage: float,
     *     blocks: array<string, array{code: string, name: string, total: int, available: int, occupied: int}>
     * }
     */
    public function getStatistics(): array
    {
        $slots = Slot::query()
            ->with(['block', 'activeTransaction'])
            ->where('is_active', true)
            ->get();

        $total = $slots->count();
        $occupied = $slots->filter(fn (Slot $s) => $s->activeTransaction !== null)->count();
        $available = $total - $occupied;
        $percentage = $total > 0 ? round(($occupied / $total) * 100, 1) : 0.0;

        $blocks = [];
        foreach ($slots as $slot) {
            $code = $slot->block->code;
            if (! isset($blocks[$code])) {
                $blocks[$code] = [
                    'code' => $code,
                    'name' => $slot->block->name,
                    'total' => 0,
                    'occupied' => 0,
                    'available' => 0,
                ];
            }

            $blocks[$code]['total']++;
            if ($slot->activeTransaction !== null) {
                $blocks[$code]['occupied']++;
            } else {
                $blocks[$code]['available']++;
            }
        }

        ksort($blocks);

        return [
            'total' => $total,
            'occupied' => $occupied,
            'available' => $available,
            'percentage' => $percentage,
            'blocks' => $blocks,
        ];
    }

    /**
     * Find shortest path using BFS from start cell to target cell.
     *
     * @return array{path: list<array{x: int, y: int}>, distance: int}|null
     */
    public function findShortestPath(int $startX, int $startY, int $targetX, int $targetY): ?array
    {
        $cells = MapCell::query()->get(['x', 'y', 'type'])->keyBy(fn ($c) => "{$c->x}:{$c->y}");

        $startKey = "{$startX}:{$startY}";
        $targetKey = "{$targetX}:{$targetY}";

        if (! isset($cells[$targetKey])) {
            return null;
        }

        // Target must not be an obstacle
        if ($cells[$targetKey]->type === MapCell::TYPE_OBSTACLE) {
            return null;
        }

        $queue = new SplQueue;
        $queue->enqueue([$startX, $startY]);

        $visited = [$startKey => true];
        $parent = [];

        $directions = [
            [0, 1],  // Up
            [0, -1], // Down
            [-1, 0], // Left
            [1, 0],  // Right
        ];

        $found = false;

        while (! $queue->isEmpty()) {
            [$currX, $currY] = $queue->dequeue();

            if ($currX === $targetX && $currY === $targetY) {
                $found = true;
                break;
            }

            foreach ($directions as [$dx, $dy]) {
                $nextX = $currX + $dx;
                $nextY = $currY + $dy;
                $nextKey = "{$nextX}:{$nextY}";

                if (isset($visited[$nextKey])) {
                    continue;
                }

                if (! isset($cells[$nextKey])) {
                    continue;
                }

                $nextType = $cells[$nextKey]->type;

                // Cannot pass obstacles
                if ($nextType === MapCell::TYPE_OBSTACLE) {
                    continue;
                }

                // Cannot pass through other slots unless it's the target destination
                if ($nextType === MapCell::TYPE_SLOT && ($nextX !== $targetX || $nextY !== $targetY)) {
                    continue;
                }

                $visited[$nextKey] = true;
                $parent[$nextKey] = [$currX, $currY];
                $queue->enqueue([$nextX, $nextY]);
            }
        }

        if (! $found) {
            return null;
        }

        // Reconstruct path
        $path = [];
        $curr = [$targetX, $targetY];

        while ($curr !== null) {
            $path[] = ['x' => $curr[0], 'y' => $curr[1]];
            $currKey = "{$curr[0]}:{$curr[1]}";
            $curr = $parent[$currKey] ?? null;
        }

        $path = array_reverse($path);

        return [
            'path' => $path,
            'distance' => count($path) - 1,
        ];
    }

    /**
     * Recommend the nearest available slot using BFS from entrance.
     *
     * @return array{
     *     slot_id: int,
     *     label: string,
     *     block_code: string,
     *     block_name: string,
     *     distance: int,
     *     x: int,
     *     y: int,
     *     path: list<array{x: int, y: int}>
     * }|null
     */
    public function findNearestAvailableSlot(int $startX = self::ENTRANCE_X, int $startY = self::ENTRANCE_Y): ?array
    {
        $slots = Slot::query()
            ->with(['block', 'mapCell', 'activeTransaction'])
            ->where('is_active', true)
            ->get();

        $availableSlotsByCoord = [];
        foreach ($slots as $slot) {
            if ($slot->activeTransaction === null && $slot->mapCell !== null) {
                $coord = "{$slot->mapCell->x}:{$slot->mapCell->y}";
                $availableSlotsByCoord[$coord] = $slot;
            }
        }

        if (empty($availableSlotsByCoord)) {
            return null;
        }

        $cells = MapCell::query()->get(['x', 'y', 'type'])->keyBy(fn ($c) => "{$c->x}:{$c->y}");

        $queue = new SplQueue;
        $queue->enqueue([$startX, $startY]);

        $startKey = "{$startX}:{$startY}";
        $visited = [$startKey => true];
        $parent = [];

        $directions = [
            [0, 1],
            [0, -1],
            [-1, 0],
            [1, 0],
        ];

        $targetCoord = null;

        while (! $queue->isEmpty()) {
            [$currX, $currY] = $queue->dequeue();
            $currCoord = "{$currX}:{$currY}";

            if (isset($availableSlotsByCoord[$currCoord])) {
                $targetCoord = $currCoord;
                break;
            }

            foreach ($directions as [$dx, $dy]) {
                $nextX = $currX + $dx;
                $nextY = $currY + $dy;
                $nextKey = "{$nextX}:{$nextY}";

                if (isset($visited[$nextKey])) {
                    continue;
                }

                if (! isset($cells[$nextKey])) {
                    continue;
                }

                $nextType = $cells[$nextKey]->type;

                if ($nextType === MapCell::TYPE_OBSTACLE) {
                    continue;
                }

                // If it is a slot but not available, it cannot be traversed
                if ($nextType === MapCell::TYPE_SLOT && ! isset($availableSlotsByCoord[$nextKey])) {
                    continue;
                }

                $visited[$nextKey] = true;
                $parent[$nextKey] = [$currX, $currY];
                $queue->enqueue([$nextX, $nextY]);
            }
        }

        if ($targetCoord === null) {
            return null;
        }

        $targetSlot = $availableSlotsByCoord[$targetCoord];

        // Reconstruct path
        $path = [];
        $curr = [$targetSlot->mapCell->x, $targetSlot->mapCell->y];

        while ($curr !== null) {
            $path[] = ['x' => $curr[0], 'y' => $curr[1]];
            $currKey = "{$curr[0]}:{$curr[1]}";
            $curr = $parent[$currKey] ?? null;
        }

        $path = array_reverse($path);

        return [
            'slot_id' => $targetSlot->id,
            'label' => "{$targetSlot->block->code}-{$targetSlot->number}",
            'block_code' => $targetSlot->block->code,
            'block_name' => $targetSlot->block->name,
            'distance' => count($path) - 1,
            'x' => $targetSlot->mapCell->x,
            'y' => $targetSlot->mapCell->y,
            'path' => $path,
        ];
    }
}
