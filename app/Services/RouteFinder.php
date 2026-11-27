<?php

namespace App\Services;

use App\Models\MapCell;
use App\Models\Slot;
use Illuminate\Support\Collection;
use RuntimeException;
use SplQueue;

/**
 * Menghitung jarak dan rute dari gerbang ke slot dengan Breadth-First Search.
 * Jarak diukur dalam jumlah langkah (satu langkah = satu sel denah).
 */
class RouteFinder
{
    // Urutan arah sama dengan kode konsol: utara (y+1), selatan (y-1), barat (x-1), timur (x+1)
    private const ARAH = [[0, 1], [0, -1], [-1, 0], [1, 0]];

    /** @var array<string, string>|null  "x,y" => jenis sel */
    private ?array $grid = null;

    /** @var array{x: int, y: int}|null */
    private ?array $gerbang = null;

    /** @var array<string, int>|null  "x,y" => jarak langkah dari gerbang */
    private ?array $jarak = null;

    /** @var array<string, string>  "x,y" => "x,y" sel asal pergerakan */
    private array $induk = [];

    /** Koordinat gerbang: ['x' => 7, 'y' => 0] */
    public function gate(): array
    {
        $this->muatDenah();

        return $this->gerbang;
    }

    /**
     * Jarak dari gerbang ke setiap sel yang dapat dicapai (jalan dan slot).
     * Kunci berbentuk "x,y".
     */
    public function distances(): array
    {
        $this->telusuri();

        return $this->jarak;
    }

    /** Jarak gerbang ke slot, atau null bila slot tidak terjangkau. */
    public function distanceTo(Slot $slot): ?int
    {
        $slot->loadMissing('mapCell');

        return $this->distances()[$this->kunci($slot->mapCell->x, $slot->mapCell->y)] ?? null;
    }

    /**
     * Slot terdekat dari kumpulan slot yang diberikan.
     * Urutan: jarak, lalu kode blok, lalu nomor slot (agar hasil selalu konsisten).
     */
    public function nearest(Collection $slots): ?Slot
    {
        $slots->each(fn (Slot $s) => $s->loadMissing(['block', 'mapCell']));

        return $slots
            ->filter(fn (Slot $s) => $this->distanceTo($s) !== null)
            ->sort(fn (Slot $a, Slot $b) => [$this->distanceTo($a), $a->block->code, $a->number]
                                        <=> [$this->distanceTo($b), $b->block->code, $b->number])
            ->first();
    }

    /** Slot kosong terdekat dari gerbang, atau null bila area parkir penuh. */
    public function recommend(): ?Slot
    {
        return $this->nearest(Slot::available()->with(['block', 'mapCell'])->get());
    }

    /**
     * Rute dari gerbang ke slot: daftar koordinat berurutan, dimulai dari gerbang
     * dan diakhiri slot. Kosong bila slot tidak terjangkau.
     *
     * @return array<int, array{x: int, y: int}>
     */
    public function routeTo(Slot $slot): array
    {
        $this->telusuri();
        $slot->loadMissing('mapCell');

        $kunci = $this->kunci($slot->mapCell->x, $slot->mapCell->y);
        if (! isset($this->jarak[$kunci])) {
            return [];
        }

        // Rekonstruksi mundur dari slot ke gerbang lewat sel asal, lalu dibalik
        $jalur = [];
        for ($k = $kunci; $k !== null; $k = $this->induk[$k] ?? null) {
            $jalur[] = $this->titik($k);
        }

        return array_reverse($jalur);
    }

    /**
     * Mengubah rute menjadi instruksi teks. Langkah berurutan yang searah digabung.
     * Contoh: ["Maju (Utara) 2 langkah", "Kiri (Barat) 3 langkah"]
     *
     * @param  array<int, array{x: int, y: int}>  $jalur
     * @return array<int, string>
     */
    public function instructions(array $jalur): array
    {
        $segmen = [];   // setiap elemen: [arah, jumlah langkah]

        for ($i = 1; $i < count($jalur); $i++) {
            $dx = $jalur[$i]['x'] - $jalur[$i - 1]['x'];
            $dy = $jalur[$i]['y'] - $jalur[$i - 1]['y'];

            $arah = match (true) {
                $dy > 0 => 'Maju (Utara)',
                $dy < 0 => 'Mundur (Selatan)',
                $dx > 0 => 'Kanan (Timur)',
                default => 'Kiri (Barat)',
            };

            $terakhir = count($segmen) - 1;
            if ($terakhir >= 0 && $segmen[$terakhir][0] === $arah) {
                $segmen[$terakhir][1]++;
            } else {
                $segmen[] = [$arah, 1];
            }
        }

        return array_map(fn (array $s) => "{$s[0]} {$s[1]} langkah", $segmen);
    }

    // ------------------------------------------------------------------
    //  Bagian internal
    // ------------------------------------------------------------------

    /** BFS dari gerbang. Dijalankan sekali, hasilnya dipakai ulang. */
    private function telusuri(): void
    {
        if ($this->jarak !== null) {
            return;
        }

        $this->muatDenah();

        $awal      = $this->kunci($this->gerbang['x'], $this->gerbang['y']);
        $jarak     = [$awal => 0];
        $induk     = [];
        $antrean   = new SplQueue();
        $antrean->enqueue($this->gerbang);

        while (! $antrean->isEmpty()) {
            $cur      = $antrean->dequeue();
            $kunciCur = $this->kunci($cur['x'], $cur['y']);

            foreach (self::ARAH as [$dx, $dy]) {
                $nx = $cur['x'] + $dx;
                $ny = $cur['y'] + $dy;
                $k  = $this->kunci($nx, $ny);

                // Di luar denah atau sudah dikunjungi
                if (! isset($this->grid[$k]) || isset($jarak[$k])) {
                    continue;
                }

                $jenis = $this->grid[$k];
                if ($jenis !== 'road' && $jenis !== 'slot') {
                    continue;   // bangunan dan gerbang tidak dapat dilalui
                }

                $jarak[$k] = $jarak[$kunciCur] + 1;
                $induk[$k] = $kunciCur;

                // Slot hanya dicatat jaraknya, tidak diteruskan sebagai jalan
                if ($jenis === 'road') {
                    $antrean->enqueue(['x' => $nx, 'y' => $ny]);
                }
            }
        }

        $this->jarak = $jarak;
        $this->induk = $induk;
    }

    private function muatDenah(): void
    {
        if ($this->grid !== null) {
            return;
        }

        $this->grid = [];
        foreach (MapCell::get(['x', 'y', 'type']) as $sel) {
            $this->grid[$this->kunci($sel->x, $sel->y)] = $sel->type;

            if ($sel->type === 'gate') {
                $this->gerbang = ['x' => $sel->x, 'y' => $sel->y];
            }
        }

        if ($this->gerbang === null) {
            throw new RuntimeException('Denah belum memiliki gerbang. Jalankan: php artisan migrate:fresh --seed');
        }
    }

    private function kunci(int $x, int $y): string
    {
        return "{$x},{$y}";
    }

    private function titik(string $kunci): array
    {
        [$x, $y] = array_map('intval', explode(',', $kunci));

        return ['x' => $x, 'y' => $y];
    }
}