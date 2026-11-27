<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('blocks')->restrictOnDelete();
            $table->foreignId('map_cell_id')->constrained('map_cells')->restrictOnDelete();
            $table->smallInteger('number');
            $table->boolean('is_active')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique('map_cell_id');  // satu sel hanya untuk satu slot
            $table->unique(['block_id', 'number']); // nomor slot unik di dalam blok
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
