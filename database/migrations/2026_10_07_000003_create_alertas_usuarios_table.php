<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estado de lectura/entrega de cada alerta por usuario.
     *
     * La lectura se determina con fecha_leido + estado (PENDIENTE/LEIDA).
     * Se evita el duplicado id_alerta + id_usuario con clave única.
     */
    public function up(): void
    {
        Schema::create('alertas_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_alerta')->constrained('alertas')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
            $table->timestamp('fecha_enviado')->useCurrent();
            $table->timestamp('fecha_leido')->nullable();
            $table->enum('estado', ['PENDIENTE', 'LEIDA'])->default('PENDIENTE');
            $table->timestamps();

            $table->unique(['id_alerta', 'id_usuario'], 'alertas_usuarios_alerta_usuario_unique');
            $table->index('id_alerta');
            $table->index('id_usuario');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_usuarios');
    }
};
