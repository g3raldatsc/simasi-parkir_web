<?php

namespace Database\Seeders;

use App\Models\Block;
use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range('A', 'K') as $kode) {
            // firstOrCreate: nama yang sudah diubah admin tidak ditimpa saat seeder diulang
            Block::firstOrCreate(['code' => $kode], ['name' => "Blok {$kode}"]);
        }
    }
}
