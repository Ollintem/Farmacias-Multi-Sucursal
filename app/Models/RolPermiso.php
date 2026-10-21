<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_rol
 * @property int $id_permiso
 * @property bool $activo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Rol $rol
 * @property-read Permiso $permiso
 */
#[Fillable(['id_rol', 'id_permiso', 'activo'])]
class RolPermiso extends Model
{
    protected $table = 'roles_permisos';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Rol, $this>
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    /**
     * @return BelongsTo<Permiso, $this>
     */
    public function permiso(): BelongsTo
    {
        return $this->belongsTo(Permiso::class, 'id_permiso');
    }
}
