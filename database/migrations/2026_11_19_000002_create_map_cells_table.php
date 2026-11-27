<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_cells', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('x');  // kolom, 0 sampai 12
            $table->smallInteger('y');  // baris, 0 sampai 14
            $table->string('type', 20); // road | building | gate | slot
            $table->string('label', 100)->nullable();   // misalnya Gedung, Gudang, Pos

            $table->unique(['x', 'y']); // satu sel per koordinat
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_cells');
    }
};
