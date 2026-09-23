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
        Schema::create('presentacion_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_presentacion')->constrained('presentaciones')->cascadeOnDelete();
            // La especificación nombra la columna FK como `producto`.
            $table->foreignId('producto')->constrained('productos')->cascadeOnDelete();
            $table->double('precio_presentacion');
            $table->timestamps();

            $table->unique(['id_presentacion', 'producto'], 'presentacion_producto_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentacion_producto');
    }
};
