<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    /**
     * Proveedor registrado directamente en el lote. Para lotes previos
     * a la columna sigue resolviéndose vía pedido (`$lote->pedido?->proveedor`).
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_lote');
    }

    /**
     * Indica si el lote ya caducó (misma regla que el listado de lotes).
     *
     * La fecha efectiva es `fecha_de_caducidad` con respaldo a
     * `fecha_caducidad`; sin fecha conocida no se considera caducado.
     */
    public function estaCaducado(): bool
    {
        $fecha = $this->fecha_de_caducidad ?? $this->fecha_caducidad;

        if ($fecha === null || $fecha === '') {
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
        return (int) $this->inventarios->where('id_sucursal', $idSucursal)->sum('stock');
    }
}
