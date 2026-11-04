<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_proveedor
 * @property int $id_sucursal
 * @property int $pedido_por
 * @property int|null $recibido_por
 * @property Carbon|null $entregado_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Proveedor $proveedor
 * @property-read Sucursal $sucursal
 * @property-read User $solicitadoPor
 * @property-read User|null $recibidoPor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Lote> $lotes
 */
class Compra extends Model
{
    protected $table = 'compras';

    protected $fillable = [
        'id_proveedor',
        'id_sucursal',
        'pedido_por',
        'recibido_por',
        'entregado_en',
    ];

    protected $casts = [
        'entregado_en' => 'datetime',
    ];

    /**
     * @return BelongsTo<Proveedor, $this>
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    /**
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function solicitadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pedido_por');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }

    /**
     * @return HasMany<Lote, $this>
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_compra');
    }

    /**
     * @return BelongsToMany<Producto, $this>
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'lotes', 'id_compra', 'id_producto')
            ->withPivot('stock_lote', 'fecha_caducidad', 'entregado_en');
    }

    /**
     * Indica si la compra todavía puede recibirse.
     */
    public function estaPendiente(): bool
    {
        return $this->recibido_por === null;
    }

    /**
     * Indica si la compra ya fue recibida.
     */
    public function estaRecibida(): bool
    {
        return $this->recibido_por !== null;
    }
}
