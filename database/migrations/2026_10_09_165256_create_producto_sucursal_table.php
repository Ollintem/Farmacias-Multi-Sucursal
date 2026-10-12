<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alta de un producto en una sucursal: el catálogo sigue siendo global y
     * la tabla `inventario` no se usa para esto (solo registra existencias
     * por lote). Es el pivot que dice en qué sucursales se dio de alta cada
     * producto, para que la pestaña Productos sea independiente por sucursal.
     */
    public function up(): void
    {
        Schema::create('producto_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('id_sucursal')->constrained('sucursales')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['producto', 'id_sucursal'], 'producto_sucursal_unique');
        });

        $this->backfill();
    }

    /**
     * Poblado compatible con los datos existentes, sin borrar nada:
     *
     * 1. Cada producto se asocia a las sucursales donde ya tiene inventario
     *    (la evidencia histórica de que operaba ahí).
     * 2. Los productos sin inventario en ninguna sucursal se asocian a todas,
     *    para que sigan siendo visibles y editables como antes.
     */
    private function backfill(): void
    {
        $ahora = now();

        $conInventario = DB::table('productos')
            ->join('lotes', 'lotes.id_producto', '=', 'productos.id')
            ->join('inventario', 'inventario.id_lote', '=', 'lotes.id')
            ->distinct()
            ->select('productos.id as producto', 'inventario.id_sucursal as id_sucursal')
            ->get()
            ->map(fn ($fila) => [
                'producto' => $fila->producto,
                'id_sucursal' => $fila->id_sucursal,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ])
            ->all();

        DB::table('producto_sucursal')->insertOrIgnore($conInventario);

        $asociados = DB::table('producto_sucursal')->distinct()->pluck('producto');
        $sucursales = DB::table('sucursales')->pluck('id');
        $pendientes = DB::table('productos')->whereNotIn('id', $asociados)->pluck('id');

        $sinEvidencia = [];

        foreach ($pendientes as $productoId) {
            foreach ($sucursales as $sucursalId) {
                $sinEvidencia[] = [
                    'producto' => $productoId,
                    'id_sucursal' => $sucursalId,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
        }

        DB::table('producto_sucursal')->insertOrIgnore($sinEvidencia);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_sucursal');
    }
};
