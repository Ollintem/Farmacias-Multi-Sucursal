<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_caja', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_corte_caja')->constrained('cortes_caja')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
            $table->enum('tipo', ['ingreso', 'retiro'])->default('ingreso');
            $table->decimal('monto', 12, 2);
            $table->string('motivo', 255)->nullable();
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};
