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
        Schema::create('mermas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sucursal')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('id_lote')->constrained('lotes')->cascadeOnDelete();
            $table->unsignedInteger('cantidad');
            $table->string('motivo', 30);
            $table->string('nota', 500)->nullable();
            $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mermas');
    }
};
