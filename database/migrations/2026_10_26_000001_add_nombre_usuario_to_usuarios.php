<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('usuarios', 'nombre_usuario')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->string('nombre_usuario')->unique()->after('apellido');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuarios', 'nombre_usuario')) {
            Schema::table('usuarios', function (Blueprint $table) {
                $table->dropUnique(['nombre_usuario']);
                $table->dropColumn('nombre_usuario');
            });
        }
    }
};
