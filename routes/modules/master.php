<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\SlotController;
use Illuminate\Support\Facades\Route;

// Modul Alif: blok dan slot (khusus admin)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('blocks', BlockController::class)->only(['index', 'edit', 'update']);
    Route::resource('slots', SlotController::class)->only(['index', 'edit', 'update']);
});
