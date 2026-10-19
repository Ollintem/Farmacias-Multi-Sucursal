<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $nombre_proveedor
 * @property string $direccion
 * @property string $unidad_entrega
 * @property string $telefono
 * @property string $correo
 * @property string|null $foto_path
 * @property string|null $rfc
 * @property string|null $nombre_contacto
 * @property bool $es_activo
 * @property Carbon $creado_en
 * @property Carbon $actualizado_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre_proveedor',
        'direccion',
        'unidad_entrega',
        'telefono',
        'correo',
        'foto_path',
        'rfc',
        'nombre_contacto',
        'es_activo',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
        'es_activo' => 'boolean',
    ];

    /**
     * URL pública de la foto de perfil o null si usa el avatar de inicial.
     */
    public function fotoUrl(): ?string
    {
        if (blank($this->foto_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->foto_path);
    }

    /**
     * @return HasMany<Pedido, $this>
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_proveedor');
    }
}
