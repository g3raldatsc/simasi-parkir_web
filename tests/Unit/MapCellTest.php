<?php

namespace Tests\Unit;

use App\Models\MapCell;
use PHPUnit\Framework\TestCase;

class MapCellTest extends TestCase
{
    public function test_map_cell_type_constants(): void
    {
        $this->assertSame('entrance', MapCell::TYPE_ENTRANCE);
        $this->assertSame('road', MapCell::TYPE_ROAD);
        $this->assertSame('slot', MapCell::TYPE_SLOT);
        $this->assertSame('obstacle', MapCell::TYPE_OBSTACLE);
    }
}
