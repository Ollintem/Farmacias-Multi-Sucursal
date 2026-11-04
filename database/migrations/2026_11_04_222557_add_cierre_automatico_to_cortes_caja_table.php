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
        Schema::table('cortes_caja', function (Blueprint $table) {
            // Marca los cortes que cerró el sistema al vencer la tolerancia
            // del turno, para distinguirlos de un cierre manual en auditoría.
            $table->boolean('cierre_automatico')->default(false)->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cortes_caja', function (Blueprint $table) {
            $table->dropColumn('cierre_automatico');
        });
    }
};
