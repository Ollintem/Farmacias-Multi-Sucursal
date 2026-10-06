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
    ];

    protected $casts = [
        'stock' => 'integer',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'id_lote');
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
     * `inventario` solo guarda `id_sucursal`, `id_lote` y `stock`.
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
            ->selectRaw('lotes.id_producto as id_producto, COALESCE(SUM(inventario.stock), 0) as total')
            ->pluck('total', 'id_producto')
            ->map(fn (mixed $total): int => (int) $total)
            ->all();
    }
}
