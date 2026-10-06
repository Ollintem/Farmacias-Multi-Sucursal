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
        if (! Schema::hasColumn('lotes', 'id_proveedor')) {
            Schema::table('lotes', function (Blueprint $table) {
                $table->foreignId('id_proveedor')->nullable()->after('id_pedido')->constrained('proveedores')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('lotes', 'anulado_en')) {
            Schema::table('lotes', function (Blueprint $table) {
                $table->timestamp('anulado_en')->nullable()->after('fecha_de_caducidad');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('lotes', 'anulado_en')) {
            Schema::table('lotes', function (Blueprint $table) {
                $table->dropColumn('anulado_en');
            });
        }

        if (Schema::hasColumn('lotes', 'id_proveedor')) {
            Schema::table('lotes', function (Blueprint $table) {
                $table->dropForeign(['id_proveedor']);
                $table->dropColumn('id_proveedor');
            });
        }
    }
};
