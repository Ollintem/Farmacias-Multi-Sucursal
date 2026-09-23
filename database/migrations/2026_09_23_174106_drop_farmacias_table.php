<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina la tabla legacy `farmacias` en BDs ya migradas.
     * En migrate:fresh es no-op porque su migración de creación fue eliminada.
     */
    public function up(): void
    {
        Schema::dropIfExists('farmacias');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se restaura: la tabla quedó fuera del esquema.
    }
};
