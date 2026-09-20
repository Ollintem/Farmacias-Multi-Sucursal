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
        Schema::table('sucursales', function (Blueprint $table) {
            $table->string('telefono', 30)->nullable()->after('direccion');
            $table->string('correo_contacto', 150)->nullable()->after('telefono');
            $table->string('responsable', 150)->nullable()->after('correo_contacto');
            $table->boolean('es_activa')->default(true)->after('hora_cierre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            $table->dropColumn([
                'telefono',
                'correo_contacto',
                'responsable',
                'es_activa',
            ]);
        });
    }
};
