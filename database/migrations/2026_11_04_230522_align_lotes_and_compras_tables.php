<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alinea una base existente con el paso de pedidos a compras:
 * - copia las compras históricas (tabla `pedidos`) a `compras`,
 * - renombra la relación de `lotes.id_pedido` a `lotes.id_compra`.
 *
 * Las tablas antiguas se conservan para poder revertir la migración.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('compras') && Schema::hasTable('pedidos')) {
            DB::statement(
                'INSERT IGNORE INTO compras (id, id_proveedor, id_sucursal, pedido_por, recibido_por, entregado_en, created_at, updated_at)
                 SELECT id, id_proveedor, id_sucursal, pedido_por, recibido_por, entregado_en, created_at, updated_at FROM pedidos'
            );
        }

        if (! Schema::hasTable('lotes') || ! Schema::hasColumn('lotes', 'id_pedido')) {
            return;
        }

        if (! Schema::hasColumn('lotes', 'id_compra')) {
            Schema::table('lotes', function (Blueprint $table) {
                $table->foreignId('id_compra')->nullable()->after('stock_lote');
            });
        }

        DB::statement('UPDATE lotes SET id_compra = id_pedido WHERE id_compra IS NULL');

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropForeign(['id_pedido']);
            $table->dropColumn('id_pedido');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->foreign('id_compra')->references('id')->on('compras')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lotes') || ! Schema::hasColumn('lotes', 'id_compra')) {
            return;
        }

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropForeign(['id_compra']);
        });

        if (! Schema::hasColumn('lotes', 'id_pedido')) {
            Schema::table('lotes', function (Blueprint $table) {
                $table->foreignId('id_pedido')->nullable()->after('stock_lote');
            });
        }

        DB::statement('UPDATE lotes SET id_pedido = id_compra WHERE id_pedido IS NULL');

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropColumn('id_compra');
        });

        if (Schema::hasTable('pedidos')) {
            Schema::table('lotes', function (Blueprint $table) {
                $table->foreign('id_pedido')->references('id')->on('pedidos')->nullOnDelete();
            });
        }
    }
};
