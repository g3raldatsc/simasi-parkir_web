<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_demo_seeder_creates_related_records_and_is_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@simasi.test',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->assertTrue(Hash::check(
            'password',
            DB::table('users')->where('email', 'admin@simasi.test')->value('password'),
        ));

        $this->assertDatabaseCount('blocks', 2);
        $this->assertDatabaseCount('map_cells', 9);
        $this->assertDatabaseCount('slots', 8);
        $this->assertDatabaseCount('vehicles', 3);
        $this->assertDatabaseCount('parking_transactions', 3);
        $this->assertSame(
            2,
            DB::table('parking_transactions')->whereNull('exited_at')->count(),
        );
    }
}
