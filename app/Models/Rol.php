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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['tipo_rol', 'descripcion'])]
class Rol extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'roles';

    /**
     * Usuarios que tienen este rol.
     *
     * @return HasMany<User, $this>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_rol');
    }
}
