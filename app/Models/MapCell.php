<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['x', 'y', 'type', 'label'])]
class MapCell extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const TYPE_ENTRANCE = 'entrance';

    public const TYPE_ROAD = 'road';

    public const TYPE_SLOT = 'slot';

    public const TYPE_OBSTACLE = 'obstacle';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'x' => 'integer',
            'y' => 'integer',
        ];
    }

    /**
     * @return HasOne<Slot, $this>
     */
    public function slot(): HasOne
    {
        return $this->hasOne(Slot::class);
    }
}
