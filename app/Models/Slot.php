<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Slot extends Model
{
    protected $fillable = ['block_id', 'map_cell_id', 'number', 'is_active', 'notes'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function mapCell(): BelongsTo
    {
        return $this->belongsTo(MapCell::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ParkingTransaction::class);
    }

    // Transaksi yang belum punya waktu keluar = slot sedang terisi
    public function activeTransaction(): HasOne
    {
        return $this->hasOne(ParkingTransaction::class)->whereNull('exited_at');
    }

    // Slot yang aktif dan tidak sedang terisi
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->whereDoesntHave('activeTransaction');
    }

    // Label tampilan, misalnya "A-5" (relasi block harus sudah dimuat)
    protected function label(): Attribute
    {
        return Attribute::get(fn () => $this->block->code . '-' . $this->number);
    }
}
