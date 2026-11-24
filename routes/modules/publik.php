<?php

use App\Http\Controllers\ParkingMapController;
use Illuminate\Support\Facades\Route;

// Modul Sofyan & Alif: beranda, pencarian plat, peta, kendaraan, laporan okupansi

Route::view('/', 'home')->name('home');

// Peta Parkir Interaktif & Algoritma BFS
Route::get('/peta', [ParkingMapController::class, 'index'])->name('peta');
Route::get('/peta/path', [ParkingMapController::class, 'path'])->name('peta.path');
Route::get('/peta/nearest', [ParkingMapController::class, 'nearest'])->name('peta.nearest');
Route::get('/peta/status', [ParkingMapController::class, 'status'])->name('peta.status');
