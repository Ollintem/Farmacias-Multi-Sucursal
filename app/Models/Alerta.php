<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $id_tipo_alerta
 * @property int $id_sucursal
 * @property string $entidad_tipo
 * @property int|null $entidad_id
 * @property string $estado
 * @property CarbonImmutable|null $fecha_creado
 * @property CarbonImmutable|null $fecha_resuelto
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read TipoAlerta|null $tipoAlerta
 * @property-read Sucursal $sucursal
 */
class Alerta extends Model
{
    public const ESTADO_ACTIVA = 'ACTIVA';

    public const ESTADO_RESUELTA = 'RESUELTA';

    public const ESTADO_CANCELADA = 'CANCELADA';

    protected $table = 'alertas';

    protected $fillable = [
        'id_tipo_alerta',
        'id_sucursal',
        'entidad_tipo',
        'entidad_id',
        'estado',
        'fecha_creado',
        'fecha_resuelto',
    ];

    protected function casts(): array
    {
        return [
            'entidad_id' => 'integer',
            'fecha_creado' => 'datetime',
            'fecha_resuelto' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TipoAlerta, $this>
     */
    public function tipoAlerta(): BelongsTo
    {
        return $this->belongsTo(TipoAlerta::class, 'id_tipo_alerta');
    }

    /**
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    /**
     * @return HasMany<AlertaUsuario, $this>
     */
    public function lecturas(): HasMany
    {
        return $this->hasMany(AlertaUsuario::class, 'id_alerta');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'alertas_usuarios', 'id_alerta', 'id_usuario');
    }
}
