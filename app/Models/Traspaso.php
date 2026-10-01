<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Traspaso extends Model
{
    public $timestamps = false;

    protected $table = 'traspasos';

    protected $fillable = [
        'sucursal_a',
        'sucursal_b',
        'id_producto',
        'cantidad',
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
        'cantidad' => 'integer',
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

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function esPendiente(): bool
    {
        return in_array(strtolower((string) $this->estado), ['pendiente', 'enviado'], true);
    }
}
