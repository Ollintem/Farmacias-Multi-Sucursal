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
 * @property string $estado
 * @property Carbon|null $entregado_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Proveedor $proveedor
 * @property-read Sucursal $sucursal
 * @property-read User $solicitadoPor
 */
class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'id_proveedor',
        'id_sucursal',
        'pedido_por',
        'recibido_por',
        'estado',
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
        return $this->hasMany(Lote::class, 'id_pedido');
    }

    /**
     * @return BelongsToMany<Producto, $this>
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'detalles_pedido', 'pedido', 'producto')
            ->withPivot('cantidad');
    }

    /**
     * @return HasMany<DetallePedido, $this>
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido');
    }
}
