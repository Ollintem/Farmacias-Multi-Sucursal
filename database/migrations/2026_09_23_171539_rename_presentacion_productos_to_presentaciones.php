<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renombra la tabla legacy en BDs ya migradas.
     * En migrate:fresh es no-op porque la migración base ya crea `presentaciones`.
     */
    public function up(): void
    {
        if (Schema::hasTable('presentacion_productos') && ! Schema::hasTable('presentaciones')) {
            Schema::rename('presentacion_productos', 'presentaciones');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('presentaciones') && ! Schema::hasTable('presentacion_productos')) {
            Schema::rename('presentaciones', 'presentacion_productos');
        }
    }
};
