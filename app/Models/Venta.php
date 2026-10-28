<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_sucursal
 * @property string $folio
 * @property string $descuento
 * @property string $total
 * @property int $id_pago
 * @property string $estado
 * @property Carbon $creado_en
 * @property-read Sucursal $sucursal
 * @property-read Pago $pago
 */
class Venta extends Model
{
    public $timestamps = false;

    protected $table = 'ventas';

    protected $fillable = [
        'id_sucursal',
        'folio',
        'descuento',
        'total',
        'id_pago',
        'estado',
    ];

    protected $casts = [
        'id_sucursal' => 'integer',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'creado_en' => 'datetime',
    ];

    /**
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    /**
     * @return BelongsTo<Pago, $this>
     */
    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago');
    }

    /**
     * @return BelongsToMany<Producto, $this>
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_venta', 'venta', 'producto')
            ->withPivot('cantidad', 'precio_unidad');
    }

    /**
     * @return HasMany<ProductoVenta, $this>
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(ProductoVenta::class, 'venta');
    }
}
