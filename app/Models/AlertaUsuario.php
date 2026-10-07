<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaUsuario extends Model
{
    public const ESTADO_PENDIENTE = 'PENDIENTE';

    public const ESTADO_LEIDA = 'LEIDA';

    protected $table = 'alertas_usuarios';

    protected $fillable = [
        'id_alerta',
        'id_usuario',
        'fecha_enviado',
        'fecha_leido',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_enviado' => 'datetime',
            'fecha_leido' => 'datetime',
        ];
    }

    public function alerta(): BelongsTo
    {
        return $this->belongsTo(Alerta::class, 'id_alerta');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function estaLeida(): bool
    {
        return $this->estado === self::ESTADO_LEIDA || $this->fecha_leido !== null;
    }
}
