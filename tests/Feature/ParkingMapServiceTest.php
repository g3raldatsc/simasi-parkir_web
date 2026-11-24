<?php

namespace Tests\Feature;

use App\Services\ParkingMapService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ParkingMapServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_parking_map_service_grid_and_bfs_pathfinding(): void
    {
        $this->seed();

        $service = new ParkingMapService;

        // 1. Grid cells
        $cells = $service->getGridCells();
        $this->assertCount(183, $cells);

        // 2. Statistics
        $stats = $service->getStatistics();
        $this->assertSame(58, $stats['total']);
        $this->assertSame(2, $stats['occupied']);
        $this->assertSame(56, $stats['available']);
        $this->assertCount(11, $stats['blocks']);

        // 3. Path from entrance (8,0) to slot A-1 at (1, 2)
        // Note: A-1 is at (1, 2). Path should start at [8, 0] and end at [1, 2].
        $result = $service->findShortestPath(8, 0, 1, 2);
        $this->assertNotNull($result);
        $this->assertSame(['x' => 8, 'y' => 0], $result['path'][0]);
        $this->assertSame(['x' => 1, 'y' => 2], end($result['path']));
        $this->assertGreaterThan(0, $result['distance']);

        // 4. Nearest available slot
        $nearest = $service->findNearestAvailableSlot();
        $this->assertNotNull($nearest);
        $this->assertSame(['x' => 8, 'y' => 0], $nearest['path'][0]);
        $this->assertGreaterThan(0, $nearest['distance']);
    }
}
