<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use App\Services\ParkingMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParkingMapController extends Controller
{
    public function __construct(
        protected ParkingMapService $mapService,
    ) {}

    /**
     * Display the interactive parking map.
     */
    public function index(): View
    {
        $cells = $this->mapService->getGridCells();
        $stats = $this->mapService->getStatistics();
        $nearest = $this->mapService->findNearestAvailableSlot();

        return view('peta', [
            'cells' => $cells,
            'stats' => $stats,
            'nearest' => $nearest,
            'gridWidth' => 13,
            'gridHeight' => 14,
            'entrance' => [
                'x' => ParkingMapService::ENTRANCE_X,
                'y' => ParkingMapService::ENTRANCE_Y,
            ],
        ]);
    }

    /**
     * Get shortest path from entrance to specific coordinates or slot.
     */
    public function path(Request $request): JsonResponse
    {
        $request->validate([
            'x' => 'nullable|integer|between:1,13',
            'y' => 'nullable|integer|between:1,14',
            'slot_id' => 'nullable|exists:slots,id',
        ]);

        $x = $request->integer('x');
        $y = $request->integer('y');

        if ($request->filled('slot_id')) {
            $slot = Slot::with('mapCell')->find($request->integer('slot_id'));
            if ($slot && $slot->mapCell) {
                $x = $slot->mapCell->x;
                $y = $slot->mapCell->y;
            }
        }

        if ($x === 0 || $y === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Koordinat slot tujuan tidak valid.',
            ], 422);
        }

        $result = $this->mapService->findShortestPath(
            ParkingMapService::ENTRANCE_X,
            ParkingMapService::ENTRANCE_Y,
            $x,
            $y
        );

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Rute menuju lokasi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'target' => ['x' => $x, 'y' => $y],
            'distance' => $result['distance'],
            'path' => $result['path'],
        ]);
    }

    /**
     * Get nearest available slot and its path from entrance.
     */
    public function nearest(): JsonResponse
    {
        $result = $this->mapService->findNearestAvailableSlot();

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada slot parkir yang tersedia saat ini.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get live statistics and slot statuses.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'stats' => $this->mapService->getStatistics(),
            'cells' => $this->mapService->getGridCells(),
        ]);
    }
}
