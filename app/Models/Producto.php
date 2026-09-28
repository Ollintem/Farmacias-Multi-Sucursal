<?php

namespace App\Models;

use App\Models\Traits\BelongsToSucursal;
use Illuminate\Database\Eloquent\Collection;
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
        'id_categoria',
        'es_controlado',
        'es_activo',
        'entregado_en',
    ];

    protected $casts = [
        'stock' => 'integer',
        'precio' => 'float',
        'id_presentacion' => 'integer',
        'id_categoria' => 'integer',
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

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
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

    /**
     * Presentaciones del producto para listados: la principal (Caja) va primero y el resto por nombre.
     */
    public function presentacionesOrdenadas(?int $idPresentacionPrincipal = null): Collection
    {
        $ordenadas = $this->presentacionesPrecio
            ->sortBy(fn (ProductoPresentacion $presentacion): string => mb_strtolower($presentacion->presentacion?->presentacion ?? ''))
            ->values();

        if ($idPresentacionPrincipal === null) {
            return $ordenadas;
        }

        $indice = $ordenadas->search(
            fn (ProductoPresentacion $presentacion): bool => $presentacion->id_presentacion === $idPresentacionPrincipal
        );

        if ($indice === false) {
            return $ordenadas;
        }

        $principal = $ordenadas->splice((int) $indice, 1)->first();

        return $ordenadas->prepend($principal)->values();
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_producto');
    }

    public function ventas(): BelongsToMany
    {
        return $this->belongsToMany(Venta::class, 'producto_venta', 'producto', 'venta')
            ->withPivot('cantidad', 'precio_unidad');
    }
}
