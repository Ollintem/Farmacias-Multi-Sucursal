<?php

namespace App\Models;

use App\Models\Traits\BelongsToSucursal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use BelongsToSucursal;

    protected $table = 'productos';

    protected $fillable = [
        'codigo_barras',
        'nombre_producto',
        'descripcion',
        'stock',
        'precio',
        'id_presentacion',
        'es_controlado',
        'es_activo',
        'entregado_en',
    ];

    protected $casts = [
        'stock' => 'integer',
        'precio' => 'float',
        'id_presentacion' => 'integer',
        'es_controlado' => 'boolean',
        'es_activo' => 'boolean',
        'entregado_en' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(PresentacionProducto::class, 'id_presentacion');
    }

    public function sucursales(): BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class, 'inventario', 'id_producto', 'id_sucursal')
            ->withPivot('stock')
            ->withTimestamps();
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_producto');
    }

    public function presentacionesPrecio(): HasMany
    {
        return $this->hasMany(ProductoPresentacion::class, 'producto');
    }

    public function ventas(): BelongsToMany
    {
        return $this->belongsToMany(Venta::class, 'producto_venta', 'producto', 'venta')
            ->withPivot('cantidad', 'precio_unidad');
    }
}
