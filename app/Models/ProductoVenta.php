<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $venta
 * @property int $producto
 * @property int $cantidad
 * @property string $precio_unidad
 * @property-read Venta $ventaRelacion
 * @property-read Producto $productoRelacion
 */
class ProductoVenta extends Model
{
    public $timestamps = false;

    protected $table = 'producto_venta';

    protected $fillable = [
        'venta',
        'producto',
        'cantidad',
        'precio_unidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unidad' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<Venta, $this>
     */
    public function ventaRelacion(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta');
    }

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function productoRelacion(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto');
    }
}
