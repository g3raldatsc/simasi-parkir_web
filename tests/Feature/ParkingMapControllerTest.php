<?php

namespace Tests\Feature;

use App\Models\Slot;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ParkingMapControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_peta_page_can_be_rendered(): void
    {
        $this->seed();

        $response = $this->get(route('peta'));

        $response->assertOk();
        $response->assertSee('Peta Parkir Interaktif');
        $response->assertSee('Total Slot');
        $response->assertSee('Gerbang Parkir');
        $response->assertViewHas('cells');
        $response->assertViewHas('stats');
    }

    public function test_peta_path_api_returns_shortest_path(): void
    {
        $this->seed();

        $slot = Slot::with('mapCell')->first();
        $this->assertNotNull($slot);

        $response = $this->getJson(route('peta.path', [
            'x' => $slot->mapCell->x,
            'y' => $slot->mapCell->y,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'target' => ['x', 'y'],
            'distance',
            'path',
        ]);
    }

    public function test_peta_nearest_api_returns_nearest_slot(): void
    {
        $this->seed();

        $response = $this->getJson(route('peta.nearest'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'slot_id',
                'label',
                'block_code',
                'block_name',
                'distance',
                'x',
                'y',
                'path',
            ],
        ]);
    }

    public function test_peta_status_api_returns_live_statistics(): void
    {
        $this->seed();

        $response = $this->getJson(route('peta.status'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'stats' => [
                'total' => 58,
            ],
        ]);
    }
}
