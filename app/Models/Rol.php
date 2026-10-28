<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $tipo_rol
 * @property string|null $descripcion
 * @property bool $corte_ciego
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['tipo_rol', 'descripcion', 'corte_ciego'])]
class Rol extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'roles';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'corte_ciego' => 'boolean',
        ];
    }

    /**
     * Usuarios que tienen este rol.
     *
     * @return HasMany<User, $this>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_rol');
    }

    /**
     * Configuración de permisos predeterminados del rol.
     *
     * @return HasMany<RolPermiso, $this>
     */
    public function rolesPermisos(): HasMany
    {
        return $this->hasMany(RolPermiso::class, 'id_rol');
    }
}
