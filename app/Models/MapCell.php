<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MapCell extends Model
{
    public $timestamps = false;   // tabel ini tidak punya created_at dan updated_at

    protected $fillable = ['x', 'y', 'type', 'label'];

    public function slot(): HasOne
    {
        return $this->hasOne(Slot::class);
    }
}
