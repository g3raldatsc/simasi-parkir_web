<?php

namespace Tests\Feature;

use App\Models\MapCell;
use App\Models\Slot;
use App\Services\RouteFinder;
use Database\Seeders\BlockSeeder;
use Database\Seeders\MapCellSeeder;
use Database\Seeders\SlotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteFinderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BlockSeeder::class);
        $this->seed(MapCellSeeder::class);
        $this->seed(SlotSeeder::class);
    }

    private function slot(string $blok, int $nomor): Slot
    {
        return Slot::with(['block', 'mapCell'])
            ->whereHas('block', fn ($q) => $q->where('code', $blok))
            ->where('number', $nomor)
            ->firstOrFail();
    }

    private function semuaSlot()
    {
        return Slot::with(['block', 'mapCell'])->get();
    }

    public function test_semua_jalan_dan_slot_terjangkau_dari_gerbang(): void
    {
        $jarak = (new RouteFinder())->distances();

        // 99 jalan + 58 slot + 1 gerbang
        $this->assertCount(158, $jarak);
    }

    public function test_jarak_gerbang_ke_slot_a5_adalah_5(): void
    {
        $this->assertSame(5, (new RouteFinder())->distanceTo($this->slot('A', 5)));
    }

    public function test_a5_menjadi_rekomendasi_saat_semua_slot_kosong(): void
    {
        $rekomendasi = (new RouteFinder())->nearest($this->semuaSlot());

        $this->assertSame('A-5', $rekomendasi->label);
    }

    public function test_rute_dan_instruksi_ke_a5_sesuai_laporan_algoritma(): void
    {
        $finder = new RouteFinder();
        $jalur  = $finder->routeTo($this->slot('A', 5));

        $this->assertSame([
            ['x' => 7, 'y' => 0],
            ['x' => 7, 'y' => 1],
            ['x' => 7, 'y' => 2],
            ['x' => 6, 'y' => 2],
            ['x' => 5, 'y' => 2],
            ['x' => 4, 'y' => 2],
        ], $jalur);

        $this->assertSame(
            ['Maju (Utara) 2 langkah', 'Kiri (Barat) 3 langkah'],
            $finder->instructions($jalur)
        );
    }

    public function test_rute_hanya_melewati_jalan_dan_berakhir_di_slot(): void
    {
        $jenis  = MapCell::all()->mapWithKeys(fn ($c) => [$c->x . ',' . $c->y => $c->type]);
        $finder = new RouteFinder();

        foreach ($this->semuaSlot() as $slot) {
            $jalur = $finder->routeTo($slot);
            $this->assertNotEmpty($jalur, "Slot {$slot->label} tidak terjangkau");

            $terakhir = count($jalur) - 1;
            foreach ($jalur as $i => $titik) {
                $tipe = $jenis[$titik['x'] . ',' . $titik['y']];

                if ($i === 0) {
                    $this->assertSame('gate', $tipe);
                } elseif ($i === $terakhir) {
                    $this->assertSame('slot', $tipe);
                } else {
                    $this->assertSame('road', $tipe, "Rute ke {$slot->label} melewati sel yang bukan jalan");
                }
            }
        }
    }

    public function test_panjang_rute_sama_dengan_manhattan_distance_pada_denah_ini(): void
    {
        $finder  = new RouteFinder();
        $gerbang = $finder->gate();

        foreach ($this->semuaSlot() as $slot) {
            $manhattan = abs($slot->mapCell->x - $gerbang['x'])
                       + abs($slot->mapCell->y - $gerbang['y']);

            $this->assertSame($manhattan, $finder->distanceTo($slot), "Jarak slot {$slot->label}");
            $this->assertCount($manhattan + 1, $finder->routeTo($slot));   // jalur memuat gerbang
        }
    }

    public function test_urutan_seri_ditentukan_kode_blok_lalu_nomor_slot(): void
    {
        $finder = new RouteFinder();

        // A-3, B-5, dan D-1 sama-sama berjarak 7 dari gerbang
        $a3 = $this->slot('A', 3);
        $b5 = $this->slot('B', 5);
        $d1 = $this->slot('D', 1);

        foreach ([$a3, $b5, $d1] as $slot) {
            $this->assertSame(7, $finder->distanceTo($slot));
        }

        // Apa pun urutan masukannya, A-3 selalu menang
        foreach ([[$a3, $b5, $d1], [$d1, $b5, $a3], [$b5, $a3, $d1]] as $urutan) {
            $this->assertSame('A-3', $finder->nearest(collect($urutan))->label);
        }

        // Tanpa kandidat, tidak ada rekomendasi
        $this->assertNull($finder->nearest(collect()));
    }
}