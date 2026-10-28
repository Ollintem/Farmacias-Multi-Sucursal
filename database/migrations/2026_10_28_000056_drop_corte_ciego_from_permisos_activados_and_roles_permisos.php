<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permisos_activados', function (Blueprint $table) {
            $table->dropColumn('corte_ciego');
        });

        Schema::table('roles_permisos', function (Blueprint $table) {
            $table->dropColumn('corte_ciego');
        });
    }

    public function down(): void
    {
        Schema::table('permisos_activados', function (Blueprint $table) {
            $table->boolean('corte_ciego')->default(false);
        });

        Schema::table('roles_permisos', function (Blueprint $table) {
            $table->boolean('corte_ciego')->default(false);
        });
    }
};
