<?php

namespace App\Http\Controllers;

use App\Models\ParkingTransaction;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $pengguna = null;
        if (auth()->user()->isAdmin()) {
            $pengguna = [
                'total'   => User::count(),
                'aktif'   => User::where('is_active', true)->count(),
                'admin'   => User::where('role', User::ROLE_ADMIN)->count(),
                'petugas' => User::where('role', User::ROLE_PETUGAS)->count(),
            ];
        }

        // Ringkasan parkir hanya dihitung bila modul parkir sudah ada.
        // Pemeriksaan ini sementara: boleh disederhanakan setelah modul
        // Anggota 2 dan 3 digabung ke main.
        $parkir = null;
        if (class_exists(Slot::class) && class_exists(ParkingTransaction::class)
            && Schema::hasTable('slots') && Schema::hasTable('parking_transactions')) {

            $total  = Slot::where('is_active', true)->count();
            $terisi = ParkingTransaction::whereNull('exited_at')->count();

            $parkir = [
                'total'          => $total,
                'terisi'         => $terisi,
                'kosong'         => max($total - $terisi, 0),
                'masuk_hari_ini' => ParkingTransaction::whereDate('entered_at', today())->count(),
                'persen'         => $total > 0 ? (int) round($terisi / $total * 100) : 0,
            ];
        }

        return view('dashboard', compact('pengguna', 'parkir'));
    }
}
