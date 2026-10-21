<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_modulo
 * @property string $accion
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Modulo $modulo
 */
#[Fillable(['id_modulo', 'accion'])]
class Permiso extends Model
{
    protected $table = 'permisos';

    /**
     * @return BelongsTo<Modulo, $this>
     */
    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'id_modulo');
    }

    /**
     * @return HasMany<RolPermiso, $this>
     */
    public function rolesPermisos(): HasMany
    {
        return $this->hasMany(RolPermiso::class, 'id_permiso');
    }
}
