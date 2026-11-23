<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('map_cells', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('x');
            $table->smallInteger('y');
            $table->string('type', 20);
            $table->string('label', 100)->nullable();
            $table->unique(['x', 'y']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_cells');
    }
};
