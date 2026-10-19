<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $id_alerta
 * @property int $id_usuario
 * @property CarbonImmutable|null $fecha_enviado
 * @property CarbonImmutable|null $fecha_leido
 * @property string $estado
 * @property-read Alerta $alerta
 * @property-read User $usuario
 */
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

    /**
     * @return BelongsTo<Alerta, $this>
     */
    public function alerta(): BelongsTo
    {
        return $this->belongsTo(Alerta::class, 'id_alerta');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function estaLeida(): bool
    {
        return $this->estado === self::ESTADO_LEIDA || $this->fecha_leido !== null;
    }
}
