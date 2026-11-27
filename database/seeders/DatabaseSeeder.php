<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan penting: blok dan denah harus ada sebelum slot
        $this->call([
            UserSeeder::class,
            BlockSeeder::class,
            MapCellSeeder::class,
            SlotSeeder::class,
            // Siappaun nanti tambahkan: DemoTransactionSeeder
        ]);
    }
}
