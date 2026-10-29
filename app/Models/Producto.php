<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $codigo_barras
 * @property string $nombre_producto
 * @property string $descripcion
 * @property string|null $precio
 * @property int|null $id_presentacion
 * @property int|null $id_categoria
 * @property bool $es_controlado
 * @property bool $es_activo
 * @property Carbon|null $entregado_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $stock_sucursal
 * @property ProductoPresentacion|null $presentacionCaja
 * @property bool $puedeDesactivarse
 * @property bool $puedeEliminarse
 * @property-read PresentacionProducto|null $presentacion
 * @property-read Categoria|null $categoria
 */
class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'codigo_barras',
        'nombre_producto',
        'descripcion',
        'id_categoria',
        'es_controlado',
        'es_activo',
        'entregado_en',
    ];

    protected $casts = [
        'id_categoria' => 'integer',
        'es_controlado' => 'boolean',
        'es_activo' => 'boolean',
        'entregado_en' => 'datetime',
    ];

    /**
     * Presentación principal del producto (la marcada con `es_principal`).
     */
    public function presentacionPrincipal(): HasOne
    {
        return $this->hasOne(ProductoPresentacion::class, 'producto')->where('es_principal', true);
    }

    /**
     * @return BelongsTo<Categoria, $this>
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    /**
     * @return HasManyThrough<Inventario, Lote, $this>
     */
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

    /**
     * @return HasMany<ProductoPresentacion, $this>
     */
    public function presentacionesPrecio(): HasMany
    {
        return $this->hasMany(ProductoPresentacion::class, 'producto');
    }

    /**
     * Presentación principal del producto dentro de las presentaciones cargadas.
     *
     * Prefiere la marcada con `es_principal`; si ninguna lo está (datos
     * heredados) cae a «caja» y, en último caso, a la primera.
     */
    public function presentacionPrincipalDe(?Collection $presentaciones = null): ?ProductoPresentacion
    {
        $presentaciones ??= $this->presentacionesPrecio;

        if ($presentaciones->isEmpty()) {
            return null;
        }

        return $presentaciones->firstWhere('es_principal', true)
            ?? $presentaciones->first(
                fn (ProductoPresentacion $presentacion): bool => strcasecmp($presentacion->presentacion?->presentacion ?? '', 'Caja') === 0
            )
            ?? $presentaciones->first();
    }

    /**
     * Presentaciones del producto para listados: la principal va primero y el
     * resto por nombre.
     */
    public function presentacionesOrdenadas(): Collection
    {
        return $this->presentacionesPrecio
            ->sortBy([
                fn (ProductoPresentacion $presentacion): int => $presentacion->es_principal ? 0 : 1,
                fn (ProductoPresentacion $presentacion): string => mb_strtolower($presentacion->presentacion?->presentacion ?? ''),
            ])
            ->values();
    }

    /**
     * @return HasMany<Lote, $this>
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_producto');
    }

    /**
     * Stock total del producto en todas las sucursales.
     *
     * Suma `inventario.stock_individual` (unidades individuales) vía lote. El
     * stock vive en `lotes.stock_lote` (histórico de entrada) e `inventario`
     * (disponible por sucursal).
     */
    public function stockTotal(): int
    {
        return (int) $this->inventarios()->sum('inventario.stock_individual');
    }

    /**
     * Stock disponible del producto en una sucursal, en unidades individuales.
     */
    public function stockEnSucursal(int $idSucursal): int
    {
        return (int) $this->inventarios()->where('inventario.id_sucursal', $idSucursal)->sum('inventario.stock_individual');
    }

    /**
     * Umbral de alerta de stock del producto en unidades base.
     *
     * Usa el stock_minimo configurado en la presentación principal; cuando
     * no existe, cae al umbral histórico de 15 unidades.
     */
    public function stockMinimoAlerta(): int
    {
        $minimo = (int) ($this->presentacionPrincipalDe()?->stock_minimo ?? 0);

        return $minimo > 0 ? $minimo : 15;
    }

    /**
     * Presentación con la que se registra un lote del producto: su principal.
     *
     * El alta de lotes no deja elegir presentación: el producto aporta la suya
     * principal y la cantidad capturada se convierte con sus unidades, igual
     * que en el resto del sistema. Sale null cuando el producto no tiene
     * presentaciones registradas.
     */
    public function presentacionParaLote(): ?ProductoPresentacion
    {
        return $this->presentacionPrincipalDe();
    }

    /**
     * Unidades individuales que trae la presentación principal del producto.
     *
     * Salida: null cuando la principal es la unidad (factor 1) o no existe, es
     * decir, cuando el producto no maneja cajas abiertas.
     */
    public function unidadesPorCaja(): ?int
    {
        $unidades = (int) ($this->presentacionPrincipalDe()?->unidades ?? 0);

        return $unidades >= 2 ? $unidades : null;
    }

    /**
     * Factor de conversión de la presentación principal (unidades por
     * presentación). Es 1 cuando la principal es la unidad o no hay principal.
     */
    public function factorPrincipal(): int
    {
        return max(1, (int) ($this->presentacionPrincipalDe()?->unidades ?? 1));
    }

    /**
     * Precio de venta por unidad individual (presentación «unidad»).
     *
     * Salida: null si el producto no tiene una presentación unitaria.
     */
    public function precioUnidad(): ?float
    {
        $unidad = $this->presentacionesPrecio->first(
            fn (ProductoPresentacion $presentacion): bool => strcasecmp($presentacion->presentacion?->presentacion ?? '', 'Unidad') === 0
        );

        return $unidad?->precio_presentacion !== null ? (float) $unidad->precio_presentacion : null;
    }

    /**
     * Estado de las cajas de un producto para un stock dado.
     *
     * Deriva cuántas cajas siguen selladas y cuántas unidades quedan sueltas
     * a partir del stock en unidades individuales; no requiere estado
     * persistido.
     *
     * Entrada: stock disponible en unidades individuales.
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

    /**
     * @return BelongsToMany<Venta, $this>
     */
    public function ventas(): BelongsToMany
    {
        return $this->belongsToMany(Venta::class, 'producto_venta', 'producto', 'venta')
            ->withPivot('cantidad', 'precio_unidad');
    }

    /**
     * @return BelongsToMany<Pedido, $this>
     */
    public function pedidos(): BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'detalles_pedido', 'producto', 'pedido')
            ->withPivot('cantidad');
    }
}
