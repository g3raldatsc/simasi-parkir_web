<?php

use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

// Modul Sofyan: beranda, pencarian plat, peta, kendaraan, laporan okupansi

// sementara doang ntar bakal diganti beranda ini dengan halaman pencarian plat.
Route::view('/', 'home')->name('home');

// Data kendaraan: dapat diakses petugas dan admin
Route::middleware('auth')->group(function () {
    Route::resource('kendaraan', VehicleController::class)
        ->parameters(['kendaraan' => 'vehicle'])
        ->names('vehicles')
        ->except('show');
});
