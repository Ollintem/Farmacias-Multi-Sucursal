<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $folio
 * @property int $stock_lote
 * @property int|null $id_pedido
 * @property int|null $id_proveedor
 * @property int|null $id_producto
 * @property int|null $id_presentacion
 * @property Carbon $entregado_en
 * @property Carbon|null $fecha_caducidad
 * @property Carbon|null $fecha_de_caducidad
 * @property Carbon|null $anulado_en
 * @property-read Pedido|null $pedido
 * @property-read Proveedor|null $proveedor
 * @property-read Producto|null $producto
 */
class Lote extends Model
{
    protected $table = 'lotes';

    public $timestamps = false;

    protected $fillable = [
        'folio',
        'stock_lote',
        'id_pedido',
        'id_proveedor',
        'id_producto',
        'id_presentacion',
        'entregado_en',
        'fecha_caducidad',
        'fecha_de_caducidad',
        'anulado_en',
    ];

    protected $casts = [
        'stock_lote' => 'integer',
        'entregado_en' => 'datetime',
        'fecha_caducidad' => 'datetime',
        'fecha_de_caducidad' => 'datetime',
        'anulado_en' => 'datetime',
    ];

    /**
     * Alias con el nombre de la nueva especificación.
     * Lee/escribe la misma fecha que fecha_caducidad.
     *
     * @return Attribute<mixed, mixed>
     */
    protected function fechaDeCaducidad(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): mixed => $value ?? ($attributes['fecha_caducidad'] ?? null),
            set: fn (mixed $value): array => [
                'fecha_de_caducidad' => $value,
                'fecha_caducidad' => $value,
            ],
        );
    }

    protected static function booted(): void
    {
        static::saving(function (Lote $lote): void {
            // Si se asignó fecha_caducidad directamente, replica a fecha_de_caducidad.
            // (El setter de fecha_de_caducidad ya escribe ambas columnas).
            if ($lote->isDirty('fecha_caducidad') && ! $lote->isDirty('fecha_de_caducidad')) {
                $lote->setAttribute('fecha_de_caducidad', $lote->getAttribute('fecha_caducidad'));
            }
        });
    }

    /**
     * Pedido de compra que originó el lote (si vino de uno).
     *
     * @return BelongsTo<Pedido, $this>
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    /**
     * Proveedor registrado directamente en el lote. Para lotes previos
     * a la columna sigue resolviéndose vía pedido (`$lote->pedido?->proveedor`).
     *
     * @return BelongsTo<Proveedor, $this>
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    /**
     * Presentación con la que se registró el lote (su caja).
     */
    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(PresentacionProducto::class, 'id_presentacion');
    }

    /**
     * @return HasMany<Inventario, $this>
     */
    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_lote');
    }

    /**
     * @return HasMany<DetalleTraspaso, $this>
     */
    public function detallesTraspaso(): HasMany
    {
        return $this->hasMany(DetalleTraspaso::class, 'id_lote');
    }

    /**
     * Indica si el lote ya caducó (misma regla que el listado de lotes).
     *
     * La fecha efectiva es `fecha_de_caducidad` con respaldo a
     * `fecha_caducidad`; sin fecha conocida no se considera caducado.
     */
    public function estaCaducado(): bool
    {
        // Nota: el accessor fechaDeCaducidad devuelve el valor crudo
        // (string|Carbon|null), por eso se normaliza con Carbon::parse.
        $fecha = $this->fecha_de_caducidad ?? $this->fecha_caducidad;

        if (! $fecha) {
            return false;
        }

        return Carbon::parse($fecha)->isPast();
    }

    /**
     * Stock del lote asignado a una sucursal vía inventario.
     *
     * Entrada: id de la sucursal.
     * Salida: unidades disponibles (0 si no hay fila de inventario).
     */
    public function stockEnSucursal(int $idSucursal): int
    {
        return (int) $this->inventarios->where('id_sucursal', $idSucursal)->sum('stock_individual');
    }

    /**
     * Unidades base que trae cada unidad de la presentación del lote.
     *
     * Sale null cuando el lote se maneja en unidades base: no tiene
     * presentación o su producto ya no registra esa presentación con
     * su equivalencia (datos heredados).
     */
    public function unidadesPresentacion(): ?int
    {
        if ($this->id_presentacion === null) {
            return null;
        }

        $unidades = (int) ($this->producto?->presentacionesPrecio
            ->firstWhere('id_presentacion', $this->id_presentacion)?->unidades ?? 0);

        return $unidades >= 2 ? $unidades : null;
    }

    /**
     * Partes para mostrar una cantidad de unidades base en la unidad del lote.
     *
     * Entrada: cantidad en unidades base.
     *
     * @return array{etiqueta: string, unidad: string, base: ?string}
     *                                                                `unidad` es la medida en la presentación del lote («10 cajas»,
     *                                                                «3 cajas · 5 sueltas»), `base` la equivalencia entre paréntesis
     *                                                                («100 uds.») y `etiqueta` la presentación en minúsculas. Sin
     *                                                                presentación la cantidad ya son unidades: `base` queda null para
     *                                                                no repetir la misma cifra.
     */
    public function partesDeUnidades(int $unidadesBase): array
    {
        $etiqueta = strtolower($this->presentacion?->presentacion ?? 'caja');
        $porCaja = $this->unidadesPresentacion();

        if ($porCaja === null || $unidadesBase <= 0) {
            return [
                'etiqueta' => $etiqueta,
                'unidad' => max(0, $unidadesBase).' uds.',
                'base' => null,
            ];
        }

        $cajas = intdiv($unidadesBase, $porCaja);
        $sueltas = $unidadesBase % $porCaja;
        $partes = [];

        if ($cajas > 0) {
            $partes[] = "{$cajas} ".($cajas === 1 ? $etiqueta : $etiqueta.'s');
        }

        if ($sueltas > 0) {
            $partes[] = "{$sueltas} ".($sueltas === 1 ? 'suelta' : 'sueltas');
        }

        return [
            'etiqueta' => $etiqueta,
            'unidad' => implode(' · ', $partes),
            'base' => $unidadesBase.' uds.',
        ];
    }
}
