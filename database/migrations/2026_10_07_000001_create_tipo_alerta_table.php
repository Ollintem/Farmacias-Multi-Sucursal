<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de tipos de alerta del sistema.
     *
     * Cada tipo pertenece a un módulo y define su nivel
     * (INFO, ADVERTENCIA, CRITICA) y si está activo.
     */
    public function up(): void
    {
        Schema::create('tipo_alerta', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();
            $table->foreignId('id_modulo')->constrained('modulos')->cascadeOnDelete();
            $table->enum('nivel', ['INFO', 'ADVERTENCIA', 'CRITICA'])->default('INFO');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('id_modulo');
            $table->index('nivel');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_alerta');
    }
};
