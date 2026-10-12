<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'codigo_barras',
        'nombre_producto',
        'descripcion',
        'precio',
        'vender_por_unidad',
        'id_presentacion',
        'id_categoria',
        'es_controlado',
        'es_activo',
        'entregado_en',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'vender_por_unidad' => 'boolean',
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

    public function inventarios(): HasManyThrough
    {
        return $this->hasManyThrough(Inventario::class, Lote::class, 'id_producto', 'id_lote', 'id', 'id');
    }

    /**
     * Sucursales donde el producto fue dado de alta (catálogo global: la
     * sucursal no vive en `productos` sino en este pivot).
     */
    public function sucursales(): BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class, 'producto_sucursal', 'producto', 'id_sucursal')
            ->withTimestamps();
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

    /**
     * Stock total del producto en todas las sucursales.
     *
     * Suma `inventario.stock` vía lote. Reemplaza la antigua columna
     * `productos.stock` (eliminada): el stock vive en `lotes.stock_lote`
     * (histórico de entrada) e `inventario.stock` (disponible por sucursal).
     */
    public function stockTotal(): int
    {
        return (int) $this->inventarios()->sum('inventario.stock');
    }

    /**
     * Stock disponible del producto en una sucursal.
     */
    public function stockEnSucursal(int $idSucursal): int
    {
        return (int) $this->inventarios()->where('inventario.id_sucursal', $idSucursal)->sum('inventario.stock');
    }

    /**
     * Presentación con la que se registra un lote del producto: su caja.
     *
     * El alta de lotes no deja elegir presentación: el producto aporta la suya
     * principal (la caja) y la cantidad capturada se convierte con sus
     * unidades, igual que en el resto del sistema. Sale null cuando el
     * producto solo se vende por unidad y no tiene presentaciones.
     */
    public function presentacionParaLote(): ?ProductoPresentacion
    {
        $presentaciones = $this->presentacionesPrecio;

        if ($presentaciones->isEmpty()) {
            return null;
        }

        $principal = $this->id_presentacion === null
            ? null
            : $presentaciones->firstWhere('id_presentacion', $this->id_presentacion);

        return $principal
            ?? $presentaciones->first(
                fn (ProductoPresentacion $presentacion): bool => strcasecmp($presentacion->presentacion?->presentacion ?? '', 'Caja') === 0
            )
            ?? $presentaciones->first();
    }

    /**
     * Unidades que trae la caja (presentación principal) del producto.
     *
     * Salida: null cuando el producto no se vende en cajas (solo por unidad).
     */
    public function unidadesPorCaja(): ?int
    {
        if ($this->id_presentacion === null) {
            return null;
        }

        $unidades = (int) ($this->presentacionesPrecio
            ->firstWhere('id_presentacion', $this->id_presentacion)?->unidades ?? 0);

        return $unidades >= 2 ? $unidades : null;
    }

    /**
     * Estado de las cajas de un producto para un stock dado.
     *
     * Deriva cuántas cajas siguen selladas y cuántas unidades quedan sueltas
     * a partir del stock en unidades base; no requiere estado persistido.
     *
     * Entrada: stock disponible en unidades base.
     *
     * @return array{unidades_por_caja: int, selladas: int, sueltas: int}|null
     */
    public function estadoDeCajas(int $stockUnidades): ?array
    {
        $porCaja = $this->unidadesPorCaja();

        if ($porCaja === null) {
            return null;
        }

        $stock = max(0, $stockUnidades);

        return [
            'unidades_por_caja' => $porCaja,
            'selladas' => intdiv($stock, $porCaja),
            'sueltas' => $stock % $porCaja,
        ];
    }

    public function ventas(): BelongsToMany
    {
        return $this->belongsToMany(Venta::class, 'producto_venta', 'producto', 'venta')
            ->withPivot('cantidad', 'precio_unidad');
    }

    public function pedidos(): BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'detalles_pedido', 'producto', 'pedido')
            ->withPivot('cantidad');
    }
}
