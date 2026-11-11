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
            $table->foreignId('id_sucursal')->constrained('sucursales')->restrictOnDelete();
            $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
            $table->string('turno', 20);
            $table->string('caja', 30)->default('Caja 1');
            $table->decimal('efectivo_inicial', 12, 2);
            $table->decimal('efectivo_declarado', 12, 2);
            $table->decimal('efectivo_esperado', 12, 2);
            $table->decimal('diferencia', 12, 2);
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_cierre')->nullable();
            $table->string('estado', 20);
            $table->boolean('cierre_automatico')->default(false);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign('id_corte_caja')->references('id')->on('cortes_caja')->nullOnDelete();
        });

        Schema::create('traspasos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_a')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('sucursal_b')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('pedido_por')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('recibido_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('estado', 20);
            $table->text('mensaje')->nullable();
            $table->text('motivo_respuesta')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('respondido_en')->nullable();
        });
    }

    public function down(): void
    {

        Schema::dropIfExists('traspasos');
        Schema::dropIfExists('cortes_caja');
    }
};
