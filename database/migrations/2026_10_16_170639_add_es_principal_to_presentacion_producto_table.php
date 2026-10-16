<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca la presentación principal de cada producto.
     *
     * Sustituye a `productos.id_presentacion`: la principal ya no vive en el
     * producto sino en su fila de presentación. Cada producto debe tener
     * exactamente una principal activa.
     */
    public function up(): void
    {
        Schema::table('presentacion_producto', function (Blueprint $table) {
            $table->boolean('es_principal')->default(false)->after('unidades');
        });

        // Backfill: cada producto con presentaciones recibe una principal.
        // Se prefiere «caja»; si no existe, la primera registrada del producto.
        $productos = DB::table('presentacion_producto')
            ->select('producto')
            ->distinct()
            ->pluck('producto');

        foreach ($productos as $productoId) {
            $filas = DB::table('presentacion_producto')
                ->join('presentaciones', 'presentaciones.id', '=', 'presentacion_producto.id_presentacion')
                ->where('presentacion_producto.producto', $productoId)
                ->orderByRaw("CASE WHEN LOWER(presentaciones.presentacion) = 'caja' THEN 0 ELSE 1 END")
                ->orderBy('presentacion_producto.id')
                ->pluck('presentacion_producto.id');

            if ($filas->isEmpty()) {
                continue;
            }

            DB::table('presentacion_producto')
                ->whereIn('id', $filas)
                ->update(['es_principal' => false]);

            DB::table('presentacion_producto')
                ->where('id', $filas->first())
                ->update(['es_principal' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('presentacion_producto', function (Blueprint $table) {
            $table->dropColumn('es_principal');
        });
    }
};
