<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sucursal')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('id_producto')->constrained('productos')->cascadeOnDelete();
            $table->integer('stock')->default(0);
            $table->timestamps();
            $table->unique(['id_sucursal', 'id_producto']);
        });

        Schema::create('presentacion_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_presentacion')->constrained('presentaciones')->cascadeOnDelete();
            $table->foreignId('producto')->constrained('productos')->cascadeOnDelete();
            $table->double('precio_presentacion');
            $table->timestamps();
            $table->unique(['id_presentacion', 'producto'], 'presentacion_producto_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentacion_producto');
        Schema::dropIfExists('inventario');
    }
};
