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
        Schema::table('productos', function (Blueprint $table) {
            if (! Schema::hasColumn('productos', 'entregado_en')) {
                $table->timestamp('entregado_en')->nullable()->after('es_controlado');
            }
            // La especificación pide codigo_barras varchar(20): se conserva la columna
            // existente y solo se ajusta el largo cuando el driver lo permite.
        });

        try {
            Schema::table('productos', function (Blueprint $table) {
                $table->string('codigo_barras', 20)->change();
            });
        } catch (Throwable) {
            // Sin doctrine/dbal no se puede hacer change(); se conserva el tipo base.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'entregado_en')) {
                $table->dropColumn('entregado_en');
            }
        });
    }
};
