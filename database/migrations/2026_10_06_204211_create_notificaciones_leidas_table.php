<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guarda en BD qué avisos ya leyó cada usuario por sucursal.
     *
     * Antes vivía en sesión y se perdía al cerrar sesión o cambiar
     * de navegador; en BD persiste para el usuario y sus compañeros.
     */
    public function up(): void
    {
        Schema::create('notificaciones_leidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('id_sucursal')->constrained('sucursales')->cascadeOnDelete();
            $table->string('aviso_id', 100);
            $table->timestamp('leida_en')->useCurrent();
            $table->unique(['id_usuario', 'id_sucursal', 'aviso_id'], 'noti_leidas_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_leidas');
    }
};
