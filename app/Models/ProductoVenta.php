<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'precio_unidad' => 'float',
    ];

    public function ventaRelacion(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta');
    }

    public function productoRelacion(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto');
    }
}
