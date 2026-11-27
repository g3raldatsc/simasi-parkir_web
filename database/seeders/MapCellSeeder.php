<?php

namespace Database\Seeders;

use App\Models\MapCell;
use Illuminate\Database\Seeder;

class MapCellSeeder extends Seeder
{
    private const KOLOM = 13;
    private const BARIS = 15;
    private const GERBANG_X = 7;

    private const BARIS_SLOT_KIRI  = [2, 4, 6, 8, 10, 12];   // blok A,B,C,E,G,I  (x 0-4)
    private const BARIS_SLOT_KANAN = [6, 8, 10, 12];    // blok D,F,H,J      (x 8-12)

    public function run(): void
    {
        $sel = [];
        for ($y = 0; $y < self::BARIS; $y++) {
            for ($x = 0; $x < self::KOLOM; $x++) {
                [$type, $label] = $this->klasifikasi($x, $y);
                $sel[] = ['x' => $x, 'y' => $y, 'type' => $type, 'label' => $label];
            }
        }

        // baris dengan (x, y) yang sama diperbarui, bukan digandakan
        MapCell::upsert($sel, ['x', 'y'], ['type', 'label']);
    }

    private function klasifikasi(int $x, int $y): array
    {
        if ($y === 0) { // baris gerbang
            return $x === self::GERBANG_X ? ['gate', 'Gerbang'] : ['building', null];
        }
        if ($y === 14) {    // blok K dan gedung
            return $x <= 7 ? ['slot', null] : ['building', 'Gedung'];
        }
        if ($x <= 4 && in_array($y, self::BARIS_SLOT_KIRI, true)) {
            return ['slot', null];
        }
        if ($x >= 8 && in_array($y, self::BARIS_SLOT_KANAN, true)) {
            return ['slot', null];
        }
        if ($x >= 8 && $y <= 4) {   // pos dan gudang di sisi kanan bawah
            return ['building', $y <= 2 ? 'Pos' : 'Gudang'];
        }
        return ['road', null];
    }
}
