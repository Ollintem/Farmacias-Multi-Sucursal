<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function tipoAlerta(): BelongsTo
    {
        return $this->belongsTo(TipoAlerta::class, 'id_tipo_alerta');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(AlertaUsuario::class, 'id_alerta');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'alertas_usuarios', 'id_alerta', 'id_usuario');
    }
}
