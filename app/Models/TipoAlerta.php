<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoAlerta extends Model
{
    public const NIVEL_INFO = 'INFO';

    public const NIVEL_ADVERTENCIA = 'ADVERTENCIA';

    public const NIVEL_CRITICA = 'CRITICA';

    protected $table = 'tipo_alerta';

    protected $fillable = [
        'nombre',
        'id_modulo',
        'nivel',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'id_modulo');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'id_tipo_alerta');
    }
}
