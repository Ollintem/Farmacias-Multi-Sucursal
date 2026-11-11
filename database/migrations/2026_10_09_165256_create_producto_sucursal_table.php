<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('id_sucursal')->constrained('sucursales')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['producto', 'id_sucursal'], 'producto_sucursal_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_sucursal');
    }
};
