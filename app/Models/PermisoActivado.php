<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $id_modulo
 * @property int $id_usuario
 * @property bool $puede_ver
 * @property bool $puede_crear
 * @property bool $puede_editar
 * @property bool $puede_borrar
 */
#[Fillable(['id_modulo', 'id_usuario', 'puede_ver', 'puede_crear', 'puede_editar', 'puede_borrar'])]
class PermisoActivado extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'permisos_activados';

    /**
     * Get the attributes that should be cast.
     *
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
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /**
     * @return BelongsTo<Modulo, $this>
     */
    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'id_modulo');
    }
}
