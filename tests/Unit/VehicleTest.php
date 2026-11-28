<?php

namespace Tests\Unit;

use App\Models\Vehicle;
use PHPUnit\Framework\TestCase;

class VehicleTest extends TestCase
{
    public function test_normalize_menghapus_spasi_dan_tanda_hubung_serta_membesarkan_huruf(): void
    {
        $kasus = [
            'P1234XL'      => 'P1234XL',
            'p 1234 xl'    => 'P1234XL',
            ' N-4563 kk '  => 'N4563KK',
            'm4512hi'      => 'M4512HI',
            'B 1 A'        => 'B1A',
        ];

        foreach ($kasus as $masukan => $diharapkan) {
            $this->assertSame($diharapkan, Vehicle::normalize($masukan), "Masukan: '{$masukan}'");
        }
    }

    public function test_pola_plat_menerima_plat_yang_valid(): void
    {
        foreach (['P1234XL', 'N4563KK', 'P9988L', 'M4512HI', 'P4567CJ', 'B1', 'AB1234', 'D12ABC'] as $plat) {
            $this->assertSame(1, preg_match(Vehicle::PLATE_PATTERN, $plat), "Seharusnya valid: {$plat}");
        }
    }

    public function test_pola_plat_menolak_plat_yang_tidak_valid(): void
    {
        foreach (['1234', 'PXL', 'P12345', 'P1234XLAB', 'ABC1', 'p1234xl', 'P 1234'] as $plat) {
            $this->assertSame(0, preg_match(Vehicle::PLATE_PATTERN, $plat), "Seharusnya tidak valid: {$plat}");
        }
    }
}
