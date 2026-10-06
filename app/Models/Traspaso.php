<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Traspaso extends Model
{
    public $timestamps = false;

    protected $table = 'traspasos';

    protected $fillable = [
        'sucursal_a',
        'sucursal_b',
        'mensaje',
        'motivo_respuesta',
        'respondido_en',
        'pedido_por',
        'recibido_por',
        'estado',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
        'respondido_en' => 'datetime',
    ];

    public function sucursalOrigen(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_a');
    }

    public function sucursalDestino(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_b');
    }

    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pedido_por');
    }

    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleTraspaso::class, 'traspaso');
    }

    public function lotes(): HasManyThrough
    {
        return $this->hasManyThrough(Lote::class, DetalleTraspaso::class, 'traspaso', 'id', 'id', 'id_lote');
    }

    public function esPendiente(): bool
    {
        return in_array(strtolower((string) $this->estado), ['pendiente', 'enviado'], true);
    }
}
