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

        DB::table('blocks')->upsert([
            ['code' => 'A', 'name' => 'Blok A', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'B', 'name' => 'Blok B', 'created_at' => $now, 'updated_at' => $now],
        ], ['code'], ['name', 'updated_at']);

        $mapCells = [
            ['x' => 0, 'y' => 1, 'type' => 'entrance', 'label' => 'Pintu Masuk'],
        ];

        foreach (['A' => 1, 'B' => 2] as $blockCode => $y) {
            for ($number = 1; $number <= 4; $number++) {
                $mapCells[] = [
                    'x' => $number,
                    'y' => $y,
                    'type' => 'slot',
                    'label' => "{$blockCode}-{$number}",
                ];
            }
        }

        DB::table('map_cells')->upsert($mapCells, ['x', 'y'], ['type', 'label']);

        $blockIds = DB::table('blocks')
            ->whereIn('code', ['A', 'B'])
            ->pluck('id', 'code');
        $mapCellIds = DB::table('map_cells')
            ->get(['id', 'x', 'y'])
            ->mapWithKeys(fn (object $cell): array => ["{$cell->x}:{$cell->y}" => $cell->id]);

        $slots = [];

        foreach (['A' => 1, 'B' => 2] as $blockCode => $y) {
            for ($number = 1; $number <= 4; $number++) {
                $slots[] = [
                    'block_id' => $blockIds[$blockCode],
                    'map_cell_id' => $mapCellIds["{$number}:{$y}"],
                    'number' => $number,
                    'is_active' => true,
                    'notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
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
