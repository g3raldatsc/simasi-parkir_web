<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
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
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

// Hanya untuk admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->except('show');
});
