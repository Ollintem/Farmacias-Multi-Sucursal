<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Centraliza las consultas de inventario que comparten los módulos del ERP:
 * búsqueda de productos, caducidades próximas y stock bajo por sucursal.
 */
class InventarioService
{
    /**
     * Consulta base de productos, opcionalmente acotada a una sucursal.
     *
     * `soloConInventario` usa las filas reales de `inventario` (pestaña Stock
     * y punto de venta). Cuando es false, la sucursal se resuelve por el pivot
     * `producto_sucursal`, que es el catálogo dado de alta en esa sucursal.
     *
     * `soloActivos` se desactiva solo en el catálogo (pestaña Productos),
     * donde los inactivos deben listarse para poder reactivarlos.
     *
     * @return Builder<Producto>
     */
    public function consultaProductos(?int $idSucursal = null, bool $soloConInventario = true, bool $soloActivos = true): Builder
    {
        return Producto::query()
            ->when($soloActivos, fn (Builder $query) => $query->where('productos.es_activo', true))
            ->when($idSucursal !== null, function (Builder $query) use ($idSucursal, $soloConInventario): void {
                if ($soloConInventario) {
                    $query->whereHas(
                        'inventarios',
                        fn (Builder $inventarioQuery) => $inventarioQuery->where('inventario.id_sucursal', $idSucursal)
                    );

                    return;
                }

                $query->whereHas(
                    'sucursales',
                    fn (Builder $sucursalQuery) => $sucursalQuery->whereKey($idSucursal)
                );
            });
    }

    /**
     * Busca productos por nombre parcial o código de barras.
     *
     * El código se compara exacto por defecto (lectura de escáner); los
     * llamadores que necesitan el comportamiento histórico de búsqueda
     * parcial pueden pasar `$codigoExacto = false`.
     *
     * Se consulta siempre sobre productos, nunca sobre presentaciones, para
     * que un producto con varias presentaciones no se duplique.
     *
     * @return Builder<Producto>
     */
    public function buscarProductos(
        string $termino,
        ?int $idSucursal = null,
        bool $soloConInventario = true,
        bool $codigoExacto = true,
        bool $soloActivos = true
    ): Builder {
        $query = $this->consultaProductos($idSucursal, $soloConInventario, $soloActivos);
        $termino = trim($termino);

        if ($termino === '') {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($termino, $codigoExacto): void {
            $subQuery->where('productos.nombre_producto', 'like', "%{$termino}%");

            if ($codigoExacto) {
                $subQuery->orWhere('productos.codigo_barras', $termino);

                return;
            }

            $subQuery->orWhere('productos.codigo_barras', 'like', "%{$termino}%");
        });
    }

    /**
     * Consulta base de lotes para el listado de caducidades.
     *
     * Mantiene la regla actual del módulo: los lotes sin filas de inventario
     * (sin ubicar) se muestran en cualquier sucursal para no ocultarlos.
     *
     * @return Builder<Lote>
     */
    public function consultaLotes(?int $idSucursal = null, string $busqueda = ''): Builder
    {
        $busqueda = trim($busqueda);

        return Lote::query()
            ->when($idSucursal !== null, function (Builder $query) use ($idSucursal): void {
                $query->where(function (Builder $subQuery) use ($idSucursal): void {
                    $subQuery->whereHas(
                        'inventarios',
                        fn (Builder $inventarioQuery) => $inventarioQuery->where('inventario.id_sucursal', $idSucursal)
                    )->orWhereDoesntHave('inventarios');
                });
            })
            ->when($busqueda !== '', function (Builder $query) use ($busqueda): void {
                $query->where(function (Builder $subQuery) use ($busqueda): void {
                    $subQuery->where('folio', 'like', "%{$busqueda}%")
                        ->orWhereHas(
                            'proveedor',
                            fn (Builder $proveedorQuery) => $proveedorQuery->where('nombre_proveedor', 'like', "%{$busqueda}%")
                        )
                        ->orWhereHas(
                            'compra.proveedor',
                            fn (Builder $proveedorQuery) => $proveedorQuery->where('nombre_proveedor', 'like', "%{$busqueda}%")
                        )
                        ->orWhereHas(
                            'producto',
                            fn (Builder $productoQuery) => $productoQuery->where('nombre_producto', 'like', "%{$busqueda}%")
                        );
                });
            });
    }

    /**
     * Lotes con existencia que vencen dentro de la ventana indicada.
     *
     * Incluye los ya caducados (cualquier fecha anterior a hoy), que quedan
     * ordenados antes que los próximos a caducar. La fecha efectiva es
     * `fecha_de_caducidad` con respaldo a `fecha_caducidad`.
     *
     * @return Builder<Lote>
     */
    public function lotesProximosACaducar(
        ?int $idSucursal = null,
        int $dias = 90,
        bool $incluirSinFecha = false
    ): Builder {
        $limite = Carbon::today()->addDays(max(0, $dias))->endOfDay();

        return Lote::query()
            ->whereHas('inventarios', function (Builder $query) use ($idSucursal): void {
                $query->when(
                    $idSucursal !== null,
                    fn (Builder $inventarioQuery) => $inventarioQuery->where('inventario.id_sucursal', $idSucursal)
                )->where('inventario.stock_individual', '>', 0);
            })
            ->where(function (Builder $query) use ($limite, $incluirSinFecha): void {
                if ($incluirSinFecha) {
                    $query->where(function (Builder $sinFecha): void {
                        $sinFecha->whereNull('lotes.fecha_de_caducidad')
                            ->whereNull('lotes.fecha_caducidad');
                    })->orWhereRaw('COALESCE(lotes.fecha_de_caducidad, lotes.fecha_caducidad) <= ?', [$limite]);

                    return;
                }

                $query->whereRaw('COALESCE(lotes.fecha_de_caducidad, lotes.fecha_caducidad) IS NOT NULL')
                    ->whereRaw('COALESCE(lotes.fecha_de_caducidad, lotes.fecha_caducidad) <= ?', [$limite]);
            })
            ->orderByRaw('COALESCE(lotes.fecha_de_caducidad, lotes.fecha_caducidad) ASC');
    }

    /**
     * Productos con lotes caducados o próximos a caducar, listos para mostrar.
     *
     * Cada fila identifica producto, lote, sucursal, stock disponible y fecha
     * de caducidad. Con `$idSucursal` el stock es el de esa sucursal; sin él
     * se suma la existencia del lote en todas las sucursales.
     *
     * @return Collection<int, mixed>
     */
    public function productosProximosACaducar(?int $idSucursal = null, int $dias = 90): Collection
    {
        $hoy = Carbon::today();

        return $this->lotesProximosACaducar($idSucursal, $dias)
            ->with([
                'producto:id,nombre_producto,codigo_barras',
                'presentacion:id,presentacion',
                'inventarios' => function ($query) use ($idSucursal): void {
                    $query->when(
                        $idSucursal !== null,
                        fn ($inventarioQuery) => $inventarioQuery->where('inventario.id_sucursal', $idSucursal)
                    )->with('sucursal:id,nombre_sucursal');
                },
            ])
            ->get()
            ->map(function (Lote $lote) use ($idSucursal, $hoy): array {
                $inventario = $idSucursal !== null
                    ? $lote->inventarios->firstWhere('id_sucursal', $idSucursal)
                    : $lote->inventarios->first();

                $stock = $idSucursal !== null
                    ? (int) ($inventario->stock_individual ?? 0)
                    : (int) $lote->inventarios->sum('stock_individual');

                $fecha = $lote->fecha_de_caducidad ?? $lote->fecha_caducidad;
                $fecha = $fecha !== null ? Carbon::parse($fecha)->startOfDay() : null;

                return [
                    'producto' => $lote->producto,
                    'producto_id' => $lote->id_producto,
                    'lote' => $lote,
                    'lote_id' => $lote->id,
                    'folio' => $lote->folio,
                    'sucursal' => $inventario?->sucursal,
                    'sucursal_id' => $inventario?->id_sucursal,
                    'stock' => $stock,
                    'fecha_caducidad' => $fecha,
                    'estado' => $fecha !== null && $fecha->lt($hoy) ? 'CADUCADO' : 'PRÓXIMO A CADUCAR',
                    'dias_restantes' => $fecha !== null ? (int) $hoy->diffInDays($fecha, false) : null,
                ];
            })
            ->values();
    }

    /**
     * Productos activos cuyo stock es igual o inferior a su mínimo principal.
     *
     * El mínimo vive en `presentacion_producto.stock_minimo` de la
     * presentación principal. Solo cuenta productos con mínimo configurado
     * (> 0), igual que las alertas actuales, y ordena del stock más bajo.
     *
     * @return Collection<int, mixed>
     */
    public function productosConStockBajo(?int $idSucursal = null): Collection
    {
        $sucursales = $idSucursal !== null
            ? Sucursal::query()->whereKey($idSucursal)->get()
            : Sucursal::query()->orderBy('nombre_sucursal')->get();

        return collect($sucursales->all())
            ->flatMap(fn (Sucursal $sucursal): Collection => $this->productosConStockBajoDeSucursal($sucursal))
            ->sortBy('stock')
            ->values();
    }

    /**
     * @return Collection<int, mixed>
     */
    private function productosConStockBajoDeSucursal(Sucursal $sucursal): Collection
    {
        $stockPorProducto = Inventario::stockPorProducto($sucursal->id);

        return Producto::query()
            ->where('productos.es_activo', true)
            ->whereHas(
                'inventarios',
                fn (Builder $query) => $query->where('inventario.id_sucursal', $sucursal->id)
            )
            ->with('presentacionesPrecio.presentacion')
            ->get()
            ->map(function (Producto $producto) use ($stockPorProducto, $sucursal): array {
                $presentacion = $producto->presentacionPrincipalDe();

                return [
                    'producto' => $producto,
                    'presentacion' => $presentacion,
                    'sucursal' => $sucursal,
                    'stock' => (int) ($stockPorProducto[$producto->id] ?? 0),
                    'minimo' => (int) ($presentacion->stock_minimo ?? 0),
                ];
            })
            ->filter(fn (array $fila): bool => $fila['minimo'] > 0 && $fila['stock'] <= $fila['minimo'])
            ->values();
    }
}
