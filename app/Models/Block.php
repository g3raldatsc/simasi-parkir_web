<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    protected $fillable = ['code', 'name'];

    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }
}
