<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre_sucursal
 * @property string $direccion
 * @property string $hora_apertura
 * @property string $hora_cierre
 * @property string|null $telefono
 * @property string|null $correo_contacto
 * @property string|null $responsable
 * @property bool $es_activa
 * @property Carbon|null $fecha_creacion
 * @property Carbon|null $fecha_actualizacion
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nombre_sucursal', 'direccion', 'telefono', 'correo_contacto', 'responsable', 'hora_apertura', 'hora_cierre', 'es_activa'])]
class Sucursal extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'sucursales';

    protected function casts(): array
    {
        return [
            'es_activa' => 'boolean',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_sucursal');
    }
}
