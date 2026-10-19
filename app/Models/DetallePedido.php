<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $pedido
 * @property int $producto
 * @property int $cantidad
 * @property-read Pedido $pedidoRelacion
 * @property-read Producto $productoRelacion
 */
class DetallePedido extends Model
{
    public $timestamps = false;

    protected $table = 'detalles_pedido';

    protected $fillable = [
        'pedido',
        'producto',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    /**
     * @return BelongsTo<Pedido, $this>
     */
    public function pedidoRelacion(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido');
    }

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function productoRelacion(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto');
    }
}
