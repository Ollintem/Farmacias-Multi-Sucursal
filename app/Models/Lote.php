<?php

namespace App\Models;

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
        'entregado_en',
        'fecha_caducidad',
        'fecha_de_caducidad',
    ];

    protected $casts = [
        'stock_lote' => 'integer',
        'entregado_en' => 'datetime',
        'fecha_caducidad' => 'datetime',
        'fecha_de_caducidad' => 'datetime',
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

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_lote');
    }
}
