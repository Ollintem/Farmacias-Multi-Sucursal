<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventario extends Model
{
    protected $table = 'inventario';

    protected $fillable = [
        'id_sucursal',
        'id_lote',
        'stock',
        'stock_individual',
    ];

    protected $casts = [
        'stock' => 'integer',
        'stock_individual' => 'integer',
    ];

    /**
     * Mantiene `stock` (presentaciones completas) y `stock_individual`
     * (unidades) sincronizados cuando solo se escribe uno de los dos campos.
     *
     * `stock_individual` es la fuente de verdad: si cambia solo él se recalcula
     * `stock`; si cambia solo `stock` se derivan las unidades con el factor de
     * conversión. Si ambos vienen definidos se respetan tal cual.
     */
    protected static function booted(): void
    {
        static::saving(function (Inventario $inventario): void {
            $cambiaUnidades = $inventario->isDirty('stock_individual');
            $cambiaPresentaciones = $inventario->isDirty('stock');

            if ($cambiaUnidades && ! $cambiaPresentaciones) {
                $inventario->stock = intdiv((int) $inventario->stock_individual, $inventario->factorConversion());
            } elseif ($cambiaPresentaciones && ! $cambiaUnidades) {
                $inventario->stock_individual = (int) $inventario->stock * $inventario->factorConversion();
            }
        });
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    /**
     * Factor de conversión de la presentación principal del producto: cuántas
     * unidades individuales trae cada presentación. Es 1 para productos
     * unitarios o sin principal.
     */
    public function factorConversion(): int
    {
        return max(1, (int) ($this->lote?->producto?->factorPrincipal() ?? 1));
    }

    /**
     * Fija el total de unidades individuales y recalcula las presentaciones
     * principales completas (las sueltas quedan solo en `stock_individual`).
     */
    public function fijarUnidadesIndividuales(int $unidades): void
    {
        $unidades = max(0, $unidades);

        $this->stock_individual = $unidades;
        $this->stock = intdiv($unidades, $this->factorConversion());
        $this->save();
    }

    /**
     * Fija las presentaciones principales completas y deriva las unidades
     * individuales (sin sueltas).
     */
    public function fijarStockPresentaciones(int $presentaciones): void
    {
        $presentaciones = max(0, $presentaciones);

        $this->stock = $presentaciones;
        $this->stock_individual = $presentaciones * $this->factorConversion();
        $this->save();
    }

    /**
     * Suma o resta unidades individuales al inventario y mantiene ambos campos
     * sincronizados.
     */
    public function ajustarUnidadesIndividuales(int $delta): void
    {
        $this->fijarUnidadesIndividuales((int) $this->stock_individual + $delta);
    }

    /**
     * Filtra las filas de inventario de una sucursal.
     *
     * Entrada: id de la sucursal.
     * Salida: el query con el filtro aplicado.
     */
    public function scopeForSucursal(Builder $query, int $idSucursal): Builder
    {
        return $query->where('inventario.id_sucursal', $idSucursal);
    }

    /**
     * Suma el stock de inventario agrupado por producto.
     *
     * El producto se resuelve vía lote (`lotes.id_producto`), porque la tabla
     * `inventario` solo guarda `id_sucursal`, `id_lote` y `stock`. Suma
     * `stock_individual` porque el sistema trabaja en unidades individuales.
     *
     * Entrada: id de sucursal opcional para acotar la suma.
     * Salida: arreglo `[id_producto => stock_total]`.
     *
     * @return array<int, int>
     */
    public static function stockPorProducto(?int $idSucursal = null): array
    {
        return static::query()
            ->join('lotes', 'lotes.id', '=', 'inventario.id_lote')
            ->whereNotNull('lotes.id_producto')
            ->when($idSucursal !== null, fn (Builder $query) => $query->forSucursal($idSucursal))
            ->groupBy('lotes.id_producto')
            ->selectRaw('lotes.id_producto as id_producto, COALESCE(SUM(inventario.stock_individual), 0) as total')
            ->pluck('total', 'id_producto')
            ->map(fn (mixed $total): int => (int) $total)
            ->all();
    }

    /**
     * Lotes de cada producto en una sucursal.
     *
     * Cuenta los lotes con fila de inventario en la sucursal (`total`) y
     * cuántos de ellos aún conservan existencia (`con_existencia`), que es
     * lo que muestra la pestaña Stock.
     *
     * Entrada: id de la sucursal.
     * Salida: arreglo `[id_producto => ['total' => n, 'con_existencia' => n]]`.
     *
     * @return array<int, array{total: int, con_existencia: int}>
     */
    public static function lotesPorProducto(int $idSucursal): array
    {
        return static::query()
            ->join('lotes', 'lotes.id', '=', 'inventario.id_lote')
            ->forSucursal($idSucursal)
            ->whereNotNull('lotes.id_producto')
            ->groupBy('lotes.id_producto')
            ->selectRaw('lotes.id_producto as id_producto, COUNT(DISTINCT inventario.id_lote) as total, COUNT(DISTINCT CASE WHEN inventario.stock_individual > 0 THEN inventario.id_lote END) as con_existencia')
            ->get()
            ->mapWithKeys(fn (Inventario $fila): array => [(int) $fila->id_producto => [
                'total' => (int) $fila->total,
                'con_existencia' => (int) $fila->con_existencia,
            ]])
            ->all();
    }
}
