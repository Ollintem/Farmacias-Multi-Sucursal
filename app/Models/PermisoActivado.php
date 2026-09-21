<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $id_permiso
 * @property int $id_modulo
 * @property int $id_usuario
 * @property bool $es_activo
 */
#[Fillable(['id_permiso', 'id_modulo', 'id_usuario', 'es_activo'])]
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
            'es_activo' => 'boolean',
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

    /**
     * @return BelongsTo<Permiso, $this>
     */
    public function permiso(): BelongsTo
    {
        return $this->belongsTo(Permiso::class, 'id_permiso');
    }
}
