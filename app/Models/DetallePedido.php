<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function pedidoRelacion(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido');
    }

    public function productoRelacion(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto');
    }
}
