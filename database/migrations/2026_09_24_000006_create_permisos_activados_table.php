<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos_activados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_modulo')->constrained('modulos')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
            $table->boolean('puede_ver')->default(true);
            $table->boolean('puede_crear')->default(true);
            $table->boolean('puede_editar')->default(true);
            $table->boolean('puede_borrar')->default(true);
            $table->timestamps();
            $table->unique(['id_modulo', 'id_usuario']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_activados');
    }
};
