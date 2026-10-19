<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_caja
 * @property string $folio
 * @property string $descuento
 * @property string $total
 * @property int $id_pago
 * @property string $estado
 * @property Carbon $creado_en
 * @property-read Caja $caja
 * @property-read Pago $pago
 */
class Venta extends Model
{
    public $timestamps = false;

    protected $table = 'ventas';

    protected $fillable = [
        'id_caja',
        'folio',
        'descuento',
        'total',
        'id_pago',
        'estado',
    ];

    protected $casts = [
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'creado_en' => 'datetime',
    ];

    /**
     * @return BelongsTo<Caja, $this>
     */
    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'id_caja');
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
