<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParkingDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $blocksData = [
            ['code' => 'A', 'name' => 'Blok A'],
            ['code' => 'B', 'name' => 'Blok B'],
            ['code' => 'C', 'name' => 'Blok C'],
            ['code' => 'D', 'name' => 'Blok D'],
            ['code' => 'E', 'name' => 'Blok E'],
            ['code' => 'F', 'name' => 'Blok F'],
            ['code' => 'G', 'name' => 'Blok G'],
            ['code' => 'H', 'name' => 'Blok H'],
            ['code' => 'I', 'name' => 'Blok I'],
            ['code' => 'J', 'name' => 'Blok J'],
            ['code' => 'K', 'name' => 'Blok K'],
        ];

        foreach ($blocksData as &$block) {
            $block['created_at'] = $now;
            $block['updated_at'] = $now;
        }
        unset($block);

        DB::table('blocks')->upsert($blocksData, ['code'], ['name', 'updated_at']);

        // Define slot definitions: [blockCode => ['y' => row, 'x_range' => [min, max]]]
        $slotDefinitions = [
            'A' => ['y' => 2, 'x_start' => 1, 'x_end' => 5],
            'B' => ['y' => 4, 'x_start' => 1, 'x_end' => 5],
            'C' => ['y' => 6, 'x_start' => 1, 'x_end' => 5],
            'D' => ['y' => 6, 'x_start' => 9, 'x_end' => 13],
            'E' => ['y' => 8, 'x_start' => 1, 'x_end' => 5],
            'F' => ['y' => 8, 'x_start' => 9, 'x_end' => 13],
            'G' => ['y' => 10, 'x_start' => 1, 'x_end' => 5],
            'H' => ['y' => 10, 'x_start' => 9, 'x_end' => 13],
            'I' => ['y' => 12, 'x_start' => 1, 'x_end' => 5],
            'J' => ['y' => 12, 'x_start' => 9, 'x_end' => 13],
            'K' => ['y' => 14, 'x_start' => 1, 'x_end' => 8],
        ];

        // Define obstacles
        $obstacles = [];
        // Pos: x 9..13, y 1..2
        for ($x = 9; $x <= 13; $x++) {
            for ($y = 1; $y <= 2; $y++) {
                $obstacles["{$x}:{$y}"] = 'Pos';
            }
        }
        // Gudang: x 9..13, y 3..4
        for ($x = 9; $x <= 13; $x++) {
            for ($y = 3; $y <= 4; $y++) {
                $obstacles["{$x}:{$y}"] = 'Gudang';
            }
        }
        // Gedung: x 9..13, y 14
        for ($x = 9; $x <= 13; $x++) {
            $obstacles["{$x}:14"] = 'Gedung';
        }

        // Map cells list
        $mapCells = [
            ['x' => 8, 'y' => 0, 'type' => 'entrance', 'label' => 'Gerbang Parkir'],
        ];

        // Track slot coordinates
        $slotCoordinates = [];
        foreach ($slotDefinitions as $code => $def) {
            $slotNum = 1;
            for ($x = $def['x_start']; $x <= $def['x_end']; $x++) {
                $coord = "{$x}:{$def['y']}";
                $slotCoordinates[$coord] = [
                    'block' => $code,
                    'number' => $slotNum,
                    'label' => "{$code}-{$slotNum}",
                ];
                $slotNum++;
            }
        }

        // Generate 13 x 14 grid
        for ($y = 1; $y <= 14; $y++) {
            for ($x = 1; $x <= 13; $x++) {
                $coord = "{$x}:{$y}";

                if (isset($slotCoordinates[$coord])) {
                    $mapCells[] = [
                        'x' => $x,
                        'y' => $y,
                        'type' => 'slot',
                        'label' => $slotCoordinates[$coord]['label'],
                    ];
                } elseif (isset($obstacles[$coord])) {
                    $mapCells[] = [
                        'x' => $x,
                        'y' => $y,
                        'type' => 'obstacle',
                        'label' => $obstacles[$coord],
                    ];
                } else {
                    $mapCells[] = [
                        'x' => $x,
                        'y' => $y,
                        'type' => 'road',
                        'label' => null,
                    ];
                }
            }
        }

        DB::table('map_cells')->upsert($mapCells, ['x', 'y'], ['type', 'label']);

        $blockIds = DB::table('blocks')->pluck('id', 'code');
        $mapCellIds = DB::table('map_cells')
            ->get(['id', 'x', 'y'])
            ->mapWithKeys(fn (object $cell): array => ["{$cell->x}:{$cell->y}" => $cell->id]);

        $slots = [];
        foreach ($slotCoordinates as $coord => $info) {
            $slots[] = [
                'block_id' => $blockIds[$info['block']],
                'map_cell_id' => $mapCellIds[$coord],
                'number' => $info['number'],
                'is_active' => true,
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('slots')->upsert(
            $slots,
            ['block_id', 'number'],
            ['map_cell_id', 'is_active', 'notes', 'updated_at'],
        );

        DB::table('vehicles')->upsert([
            ['plate_number' => 'P 1234 AB', 'owner_name' => 'Andi Pratama', 'created_at' => $now, 'updated_at' => $now],
            ['plate_number' => 'P 5678 CD', 'owner_name' => 'Budi Santoso', 'created_at' => $now, 'updated_at' => $now],
            ['plate_number' => 'P 9012 EF', 'owner_name' => 'Citra Lestari', 'created_at' => $now, 'updated_at' => $now],
        ], ['plate_number'], ['owner_name', 'updated_at']);

        if (DB::table('parking_transactions')->exists()) {
            return;
        }

        $userIds = DB::table('users')
            ->whereIn('email', ['admin@simasi.test', 'petugas@simasi.test'])
            ->pluck('id', 'email');
        $vehicleIds = DB::table('vehicles')
            ->whereIn('plate_number', ['P 1234 AB', 'P 5678 CD', 'P 9012 EF'])
            ->pluck('id', 'plate_number');
        $slotIds = DB::table('slots')
            ->join('blocks', 'blocks.id', '=', 'slots.block_id')
            ->whereIn('blocks.code', ['A', 'B'])
            ->get(['blocks.code', 'slots.number', 'slots.id'])
            ->mapWithKeys(fn (object $slot): array => ["{$slot->code}:{$slot->number}" => $slot->id]);

        DB::table('parking_transactions')->insert([
            [
                'vehicle_id' => $vehicleIds['P 1234 AB'],
                'slot_id' => $slotIds['A:1'],
                'entry_user_id' => $userIds['petugas@simasi.test'],
                'exit_user_id' => null,
                'entered_at' => $now->copy()->subHours(2),
                'exited_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'vehicle_id' => $vehicleIds['P 5678 CD'],
                'slot_id' => $slotIds['B:2'],
                'entry_user_id' => $userIds['petugas@simasi.test'],
                'exit_user_id' => null,
                'entered_at' => $now->copy()->subHour(),
                'exited_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'vehicle_id' => $vehicleIds['P 9012 EF'],
                'slot_id' => $slotIds['A:2'],
                'entry_user_id' => $userIds['petugas@simasi.test'],
                'exit_user_id' => $userIds['admin@simasi.test'],
                'entered_at' => $now->copy()->subHours(5),
                'exited_at' => $now->copy()->subHours(3),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
