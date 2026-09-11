<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $tipo_permiso
 * @property string $descripcion
 */
#[Fillable(['tipo_permiso', 'descripcion'])]
class Permiso extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'permisos';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Asignaciones de este permiso a usuarios por módulo.
     *
     * @return HasMany<PermisoActivado, $this>
     */
    public function permisosActivados(): HasMany
    {
        return $this->hasMany(PermisoActivado::class, 'id_permiso');
    }
}
