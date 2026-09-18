<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Limpieza del diseño anterior: el catálogo `permisos` ya no existe,
        // los flags viven directamente en `permisos_activados`.
        Schema::dropIfExists('permisos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se recrea el catálogo legacy.
    }
};
