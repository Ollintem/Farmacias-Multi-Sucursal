<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property bool $es_activa
 * @property Carbon|null $fecha_creacion
 * @property Carbon|null $fecha_actualizacion
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nombre_sucursal', 'direccion', 'telefono', 'correo_contacto', 'hora_apertura', 'hora_cierre', 'es_activa'])]
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

    /**
     * @return HasMany<Pedido, $this>
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_sucursal');
    }

    /**
     * @return HasMany<Inventario, $this>
     */
    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'id_sucursal');
    }

    /**
     * Productos dados de alta en esta sucursal (pivot `producto_sucursal`).
     *
     * @return BelongsToMany<Producto, $this>
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_sucursal', 'id_sucursal', 'producto')
            ->withTimestamps();
    }

    /**
     * @return HasMany<CorteCaja, $this>
     */
    public function cortesCaja(): HasMany
    {
        return $this->hasMany(CorteCaja::class, 'id_sucursal');
    }

    /**
     * @return HasMany<Venta, $this>
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_sucursal');
    }

    /**
     * @return HasMany<Traspaso, $this>
     */
    public function traspasosOrigen(): HasMany
    {
        return $this->hasMany(Traspaso::class, 'sucursal_a');
    }

    /**
     * @return HasMany<Traspaso, $this>
     */
    public function traspasosDestino(): HasMany
    {
        return $this->hasMany(Traspaso::class, 'sucursal_b');
    }

    /**
     * @return HasMany<Alerta, $this>
     */
    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'id_sucursal');
    }
}
