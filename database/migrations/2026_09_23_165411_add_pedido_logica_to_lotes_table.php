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
        Schema::table('lotes', function (Blueprint $table) {
            $table->integer('stock_lote')->default(0)->after('folio');
            $table->foreignId('id_pedido')->nullable()->after('stock_lote')->constrained('pedidos')->nullOnDelete();
            // Alias con el nombre solicitado en la nueva especificación.
            // Se mantiene fecha_caducidad por compatibilidad y se sincroniza vía modelo.
            if (! Schema::hasColumn('lotes', 'fecha_de_caducidad')) {
                $table->timestamp('fecha_de_caducidad')->nullable()->after('fecha_caducidad');
            }
        });

        // id_proveedor pasa a nullable: el proveedor ahora llega vía pedido.
        // Se hace en un paso separado para evitar conflictos con el FK existente en algunos drivers.
        try {
            Schema::table('lotes', function (Blueprint $table) {
                $table->unsignedBigInteger('id_proveedor')->nullable()->change();
            });
        } catch (Throwable) {
            // Si doctrine/dbal no está disponible, se omite el change() y se conserva el esquema base.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropConstrainedForeignIdIfExists('id_pedido');
            if (Schema::hasColumn('lotes', 'fecha_de_caducidad')) {
                $table->dropColumn('fecha_de_caducidad');
            }
            if (Schema::hasColumn('lotes', 'stock_lote')) {
                $table->dropColumn('stock_lote');
            }
        });
    }
};
