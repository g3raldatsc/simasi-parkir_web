<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Models\ParkingTransaction;
use App\Models\Vehicle;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $q    = trim((string) $request->query('q'));
        $plat = Vehicle::normalize($q);   // pencarian plat tidak peka spasi dan huruf besar-kecil

        $vehicles = Vehicle::query()
            ->when($this->modulTransaksiTersedia(),
                fn ($query) => $query->with('activeTransaction.slot.block'))
            ->when($q !== '', function ($query) use ($q, $plat) {
                $query->where(function ($w) use ($q, $plat) {
                    $w->where('owner_name', 'like', "%{$q}%");
                    if ($plat !== '') {
                        $w->orWhere('plate_number', 'like', "%{$plat}%");
                    }
                });
            })
            ->orderBy('plate_number')
            ->paginate(10)
            ->withQueryString();

        return view('vehicles.index', compact('vehicles', 'q'));
    }

    public function create(): View
    {
        return view('vehicles.create', ['vehicle' => new Vehicle()]);
    }

    public function store(VehicleRequest $request): RedirectResponse
    {
        $vehicle = Vehicle::create($request->validated());

        return redirect()->route('vehicles.index')
            ->with('success', "Kendaraan {$vehicle->plate_number} berhasil ditambahkan.");
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(VehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        return redirect()->route('vehicles.index')
            ->with('success', "Data kendaraan {$vehicle->plate_number} berhasil diperbarui.");
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        try {
            $vehicle->delete();
        } catch (QueryException $e) {
            // SQLSTATE 23000 = pelanggaran foreign key (kendaraan sudah punya transaksi)
            if ((string) $e->getCode() === '23000') {
                return back()->with('error',
                    "Kendaraan {$vehicle->plate_number} tidak dapat dihapus karena sudah memiliki riwayat parkir.");
            }
            throw $e;
        }

        return redirect()->route('vehicles.index')
            ->with('success', "Kendaraan {$vehicle->plate_number} berhasil dihapus.");
    }

    // SEMENTARA: tabel transaksi milik Anggota 3. Setelah modulnya digabung ke main,
    // metode ini boleh dihapus dan pemanggilnya disederhanakan.
    private function modulTransaksiTersedia(): bool
    {
        return class_exists(ParkingTransaction::class)
            && Schema::hasTable('parking_transactions');
    }
}
