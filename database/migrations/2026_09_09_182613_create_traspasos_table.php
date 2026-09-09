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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traspasos');
    }
};
