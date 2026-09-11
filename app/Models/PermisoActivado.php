<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_permiso
 * @property int $id_usuario
 * @property int $id_modulo
 * @property bool $es_activo
 * @property Carbon $creado_en
 * @property Carbon $actualizado_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['id_permiso', 'id_usuario', 'id_modulo', 'es_activo'])]
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
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Permiso, $this>
     */
    public function permiso(): BelongsTo
    {
        return $this->belongsTo(Permiso::class, 'id_permiso');
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
