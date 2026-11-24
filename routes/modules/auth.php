<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Modul Gerald dan Gilang: login, logout, dashboard, pengguna

// Hanya untuk tamu (yang sudah login dialihkan ke dashboard)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')    // maksimal 5 percobaan per menit
        ->name('login.store');
});

// Hanya untuk yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Sementara doang: dashboard sederhana, diganti DashboardController di Tahap 5
    Route::view('/dashboard', 'dashboard')->name('dashboard');
});

// sementara juga: halaman uji, dihapus pada akhir Tahap 5.
Route::view('/uji/panel', 'uji.panel');
Route::get('/uji/flash', function () {
    return redirect('/uji/panel')
        ->with('success', 'Pesan sukses tampil otomatis dan hilang sendiri.')
        ->with('error', 'Ini contoh pesan kesalahan.');
});
Route::get('/uji/admin', fn () => 'Halaman khusus admin berhasil dibuka.')
    ->middleware(['auth', 'role:admin']);
