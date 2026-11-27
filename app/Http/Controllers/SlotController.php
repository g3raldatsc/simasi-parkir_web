<?php

namespace App\Http\Controllers;

use App\Http\Requests\SlotRequest;
use App\Models\Block;
use App\Models\ParkingTransaction;
use App\Models\Slot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SlotController extends Controller
{
    public function index(Request $request): View
    {
        $blokId = $request->integer('blok');
        $status = $request->query('status');

        $slots = Slot::query()
            ->with(['block', 'mapCell'])
            ->when($this->modulTransaksiTersedia(), fn ($q) => $q->with('activeTransaction'))
            ->when($blokId, fn ($q) => $q->where('block_id', $blokId))
            ->when($status === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when($status === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->orderBy('block_id')
            ->orderBy('number')
            ->paginate(20)
            ->withQueryString();

        $blocks = Block::orderBy('code')->get();

        return view('slots.index', compact('slots', 'blocks', 'blokId', 'status'));
    }

    public function edit(Slot $slot): View
    {
        $slot->load(['block', 'mapCell']);

        if ($this->modulTransaksiTersedia()) {
            $slot->load('activeTransaction');
        }

        return view('slots.edit', compact('slot'));
    }

    public function update(SlotRequest $request, Slot $slot): RedirectResponse
    {
        $data = $request->validated();

        // Aturan bisnis: slot yang sedang terisi tidak boleh dinonaktifkan
        if (! $data['is_active'] && $this->sedangTerisi($slot)) {
            return back()
                ->withInput()
                ->withErrors(['is_active' => 'Slot sedang terisi kendaraan dan tidak dapat dinonaktifkan. Catat kendaraan keluar terlebih dahulu.']);
        }

        $slot->update($data);
        $slot->load('block');

        return redirect()->route('slots.index')
            ->with('success', "Slot {$slot->label} berhasil diperbarui.");
    }

    // SEMENTARA: tabel transaksi milik Anggota 3. Setelah modulnya digabung ke main,
    // metode ini boleh dihapus dan pemanggilnya disederhanakan.
    private function modulTransaksiTersedia(): bool
    {
        return class_exists(ParkingTransaction::class)
            && Schema::hasTable('parking_transactions');
    }

    private function sedangTerisi(Slot $slot): bool
    {
        return $this->modulTransaksiTersedia()
            && $slot->activeTransaction()->exists();
    }
}
