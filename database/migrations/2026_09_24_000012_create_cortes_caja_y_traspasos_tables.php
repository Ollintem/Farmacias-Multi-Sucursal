<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_caja')->constrained('cajas')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
            $table->string('turno', 20);
            $table->double('efectivo_inicial');
            $table->double('efectivo_declarado');
            $table->double('efectivo_esperado');
            $table->double('diferencia');
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_cierre')->nullable();
            $table->string('estado', 20);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('traspasos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_a')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('sucursal_b')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('pedido_por')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('recibido_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('estado', 20);
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traspasos');
        Schema::dropIfExists('cortes_caja');
    }
};
