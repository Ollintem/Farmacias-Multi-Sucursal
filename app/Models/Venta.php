<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'descuento' => 'double',
        'total' => 'double',
        'creado_en' => 'datetime',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago');
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_venta', 'venta', 'producto')
            ->withPivot('cantidad', 'precio_unidad');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ProductoVenta::class, 'venta');
    }
}
