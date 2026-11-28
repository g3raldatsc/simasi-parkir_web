<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    // 1-2 huruf, 1-4 angka, 0-3 huruf (untuk plat yang udah dinormalisasi)
    public const PLATE_PATTERN = '/^[A-Z]{1,2}[0-9]{1,4}[A-Z]{0,3}$/';

    protected $fillable = ['plate_number', 'owner_name'];

    // "p 1234 xl" -> "P1234XL": hanya huruf dan angka, semuanya huruf kapital
    public static function normalize(string $plat): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plat));
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ParkingTransaction::class);
    }

    // Transaksi yang belum punya waktu keluar = kendaraan sedang parkir
    public function activeTransaction(): HasOne
    {
        return $this->hasOne(ParkingTransaction::class)->whereNull('exited_at');
    }
}
