<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Traspaso extends Model
{
    public $timestamps = false;

    protected $table = 'traspasos';

    protected $fillable = [
        'sucursal_a',
        'sucursal_b',
        'pedido_por',
        'recibido_por',
        'estado',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
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
}
