<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permisos predeterminados de cada rol. Es independiente de
     * `permisos_activados`, que conserva las excepciones individuales.
     */
    public function up(): void
    {
        Schema::create('roles_permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_rol')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('id_permiso')->constrained('permisos')->cascadeOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['id_rol', 'id_permiso'], 'roles_permisos_unique');
            $table->index(['id_rol', 'activo'], 'roles_permisos_rol_activo_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_permisos');
    }
};
