<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permisos predeterminados de cada rol por módulo. Es independiente de
     * `permisos_activados`, que conserva las excepciones individuales.
     */
    public function up(): void
    {
        Schema::create('roles_permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_modulo')->constrained('modulos')->cascadeOnDelete();
            $table->foreignId('id_rol')->constrained('roles')->cascadeOnDelete();
            $table->boolean('puede_ver')->default(true);
            $table->boolean('puede_crear')->default(true);
            $table->boolean('puede_editar')->default(true);
            $table->boolean('puede_borrar')->default(true);
            $table->timestamps();

            $table->unique(['id_rol', 'id_modulo'], 'roles_permisos_rol_modulo_unique');
            $table->index(['id_rol', 'puede_ver'], 'roles_permisos_rol_ver_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_permisos');
    }
};
