<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cortes_caja', function (Blueprint $table): void {
            if (! Schema::hasColumn('cortes_caja', 'caja')) {
                $table->string('caja', 30)->default('Caja 1')->after('turno');
            }
        });

        Schema::table('ventas', function (Blueprint $table): void {
            if (! Schema::hasColumn('ventas', 'id_corte_caja')) {
                $table->foreignId('id_corte_caja')->nullable()->after('id_sucursal')->constrained('cortes_caja')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table): void {
            if (Schema::hasColumn('ventas', 'id_corte_caja')) {
                $table->dropForeign(['id_corte_caja']);
                $table->dropColumn('id_corte_caja');
            }
        });

        Schema::table('cortes_caja', function (Blueprint $table): void {
            if (Schema::hasColumn('cortes_caja', 'caja')) {
                $table->dropColumn('caja');
            }
        });
    }
};
