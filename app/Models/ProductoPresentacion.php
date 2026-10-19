<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $id_presentacion
 * @property int $producto
 * @property string $precio_presentacion
 * @property int $unidades
 * @property-read PresentacionProducto $presentacion
 * @property-read Producto $productoRelacion
 */
class ProductoPresentacion extends Model
{
    protected $table = 'presentacion_producto';

    protected $fillable = [
        'id_presentacion',
        'producto',
        'precio_presentacion',
        'unidades',
        'es_principal',
    ];

    protected $casts = [
        'precio_presentacion' => 'decimal:2',
        'unidades' => 'integer',
        'es_principal' => 'boolean',
    ];

    /**
     * @return BelongsTo<PresentacionProducto, $this>
     */
    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(PresentacionProducto::class, 'id_presentacion');
    }

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function productoRelacion(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto');
    }
}
