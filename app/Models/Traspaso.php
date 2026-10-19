<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $sucursal_a
 * @property int $sucursal_b
 * @property string|null $mensaje
 * @property string|null $motivo_respuesta
 * @property Carbon|null $respondido_en
 * @property int $pedido_por
 * @property int|null $recibido_por
 * @property string $estado
 * @property Carbon $creado_en
 * @property-read Sucursal $sucursalOrigen
 * @property-read Sucursal $sucursalDestino
 * @property-read User $solicitadoPor
 */
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

    /**
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursalOrigen(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_a');
    }

    /**
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursalDestino(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_b');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pedido_por');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }

    /**
     * @return HasMany<DetalleTraspaso, $this>
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleTraspaso::class, 'traspaso');
    }

    /**
     * @return HasManyThrough<Lote, DetalleTraspaso, $this>
     */
    public function lotes(): HasManyThrough
    {
        return $this->hasManyThrough(Lote::class, DetalleTraspaso::class, 'traspaso', 'id', 'id', 'id_lote');
    }

    public function esPendiente(): bool
    {
        return in_array(strtolower((string) $this->estado), ['pendiente', 'enviado'], true);
    }
}
