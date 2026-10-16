<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el stock en unidades individuales al inventario.
     *
     * `inventario.stock` pasa a contar presentaciones principales completas y
     * `inventario.stock_individual` el total de unidades individuales (incluye
     * las sueltas de cajas abiertas). El backfill conserva las existencias
     * actuales: lo que hoy es `stock` (unidades base) se copia a
     * `stock_individual` y `stock` se recalcula dividiendo entre el factor de
     * conversión de la presentación principal del producto.
     */
    public function up(): void
    {
        Schema::table('inventario', function (Blueprint $table) {
            $table->integer('stock_individual')->default(0)->after('stock');
        });

        $factores = DB::table('presentacion_producto')
            ->where('es_principal', true)
            ->orderBy('id')
            ->pluck('unidades', 'producto');

        $filas = DB::table('inventario')
            ->join('lotes', 'lotes.id', '=', 'inventario.id_lote')
            ->select('inventario.id', 'inventario.stock', 'lotes.id_producto')
            ->get();

        foreach ($filas as $fila) {
            $unidades = (int) ($factores[$fila->id_producto] ?? 1);
            $factor = $unidades >= 1 ? $unidades : 1;
            $unidadesIndividuales = (int) $fila->stock;

            DB::table('inventario')
                ->where('id', $fila->id)
                ->update([
                    'stock_individual' => $unidadesIndividuales,
                    'stock' => intdiv($unidadesIndividuales, $factor),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('inventario', function (Blueprint $table) {
            $table->dropColumn('stock_individual');
        });
    }
};
