<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\MapCell;
use App\Models\Slot;
use Illuminate\Database\Seeder;

class SlotSeeder extends Seeder
{
    public function run(): void
    {
        // blok => [baris y, kolom awal x, jumlah slot]
        $tata = [
            'A' => [2, 0, 5],  'B' => [4, 0, 5],
            'C' => [6, 0, 5],  'D' => [6, 8, 5],
            'E' => [8, 0, 5],  'F' => [8, 8, 5],
            'G' => [10, 0, 5], 'H' => [10, 8, 5],
            'I' => [12, 0, 5], 'J' => [12, 8, 5],
            'K' => [14, 0, 8],
        ];

        $blok = Block::pluck('id', 'code');
        $sel  = MapCell::where('type', 'slot')->get()
                    ->keyBy(fn ($c) => $c->x . '-' . $c->y);

        foreach ($tata as $kode => [$y, $x0, $jumlah]) {
            for ($i = 0; $i < $jumlah; $i++) {
                $cell = $sel[($x0 + $i) . '-' . $y];

                // firstOrCreate: status aktif yang diubah admin tidak ditimpa saat seeder diulang
                Slot::firstOrCreate(
                    ['block_id' => $blok[$kode], 'number' => $i + 1],
                    ['map_cell_id' => $cell->id, 'is_active' => true]
                );
            }
        }
    }
}
