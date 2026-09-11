<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre_modulo
 * @property Carbon $creado_en
 * @property Carbon $actualizado_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nombre_modulo'])]
class Modulo extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'modulos';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    /**
     * Asignaciones de permisos sobre este módulo.
     *
     * @return HasMany<PermisoActivado, $this>
     */
    public function permisosActivados(): HasMany
    {
        return $this->hasMany(PermisoActivado::class, 'id_modulo');
    }
}
