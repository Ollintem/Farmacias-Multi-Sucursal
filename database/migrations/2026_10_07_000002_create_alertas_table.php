<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alertas generadas por el sistema para cada sucursal.
     *
     * entidad_tipo + entidad_id identifican dinámicamente el origen
     * (p. ej. traspaso, lote, producto, venta) sin FK rígida.
     */
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tipo_alerta')->constrained('tipo_alerta')->cascadeOnDelete();
            $table->foreignId('id_sucursal')->constrained('sucursales')->cascadeOnDelete();
            $table->string('entidad_tipo', 60);
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->enum('estado', ['ACTIVA', 'RESUELTA', 'CANCELADA'])->default('ACTIVA');
            $table->timestamp('fecha_creado')->useCurrent();
            $table->timestamp('fecha_resuelto')->nullable();
            $table->timestamps();

            $table->index('id_tipo_alerta');
            $table->index('id_sucursal');
            $table->index('estado');
            $table->index(['entidad_tipo', 'entidad_id']);
            $table->index('fecha_creado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
