<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $tipo_permiso
 * @property string $descripcion
 */
class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = [
        'tipo_permiso',
        'descripcion',
    ];

    /**
     * @return HasMany<PermisoActivado, $this>
     */
    public function permisosActivados(): HasMany
    {
        return $this->hasMany(PermisoActivado::class, 'id_permiso');
    }
}
