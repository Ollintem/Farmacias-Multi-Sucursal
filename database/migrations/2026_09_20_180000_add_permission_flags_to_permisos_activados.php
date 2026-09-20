<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permisos_activados')) {
            return;
        }

        Schema::table('permisos_activados', function (Blueprint $table): void {
            if (! Schema::hasColumn('permisos_activados', 'puede_ver')) {
                $table->boolean('puede_ver')->default(true)->after('id_usuario');
            }

            if (! Schema::hasColumn('permisos_activados', 'puede_crear')) {
                $table->boolean('puede_crear')->default(true)->after('puede_ver');
            }

            if (! Schema::hasColumn('permisos_activados', 'puede_editar')) {
                $table->boolean('puede_editar')->default(true)->after('puede_crear');
            }

            if (! Schema::hasColumn('permisos_activados', 'puede_borrar')) {
                $table->boolean('puede_borrar')->default(true)->after('puede_editar');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('permisos_activados')) {
            return;
        }

        Schema::table('permisos_activados', function (Blueprint $table): void {
            foreach (['puede_borrar', 'puede_editar', 'puede_crear', 'puede_ver'] as $column) {
                if (Schema::hasColumn('permisos_activados', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
