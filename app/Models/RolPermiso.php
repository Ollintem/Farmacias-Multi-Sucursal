<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_modulo
 * @property int $id_rol
 * @property bool $puede_ver
 * @property bool $puede_crear
 * @property bool $puede_editar
 * @property bool $puede_borrar
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Rol $rol
 * @property-read Modulo $modulo
 */
#[Fillable(['id_rol', 'id_modulo', 'puede_ver', 'puede_crear', 'puede_editar', 'puede_borrar'])]
class RolPermiso extends Model
{
    protected $table = 'roles_permisos';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'puede_ver' => 'boolean',
            'puede_crear' => 'boolean',
            'puede_editar' => 'boolean',
            'puede_borrar' => 'boolean',
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
     * @return BelongsTo<Modulo, $this>
     */
    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'id_modulo');
    }
}
