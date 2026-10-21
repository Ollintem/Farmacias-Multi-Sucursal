<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de permisos granulares por módulo y acción.
     */
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_modulo')->constrained('modulos')->cascadeOnDelete();
            $table->string('accion', 20);
            $table->timestamps();

            $table->unique(['id_modulo', 'accion'], 'permisos_modulo_accion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
