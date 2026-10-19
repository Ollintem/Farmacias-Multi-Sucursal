<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $presentacion
 * @property string $descripcion
 * @property bool $es_activo
 */
class PresentacionProducto extends Model
{
    protected $table = 'presentaciones';

    public $timestamps = false;

    protected $fillable = [
        'presentacion',
        'descripcion',
        'es_activo',
    ];

    protected $casts = [
        'es_activo' => 'boolean',
    ];

    /**
     * @return HasMany<ProductoPresentacion, $this>
     */
    public function preciosPorProducto(): HasMany
    {
        return $this->hasMany(ProductoPresentacion::class, 'id_presentacion');
    }

    /**
     * @return HasMany<Producto, $this>
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_presentacion');
    }

    /**
     * @param  Builder<PresentacionProducto>  $query
     * @return Builder<PresentacionProducto>
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('es_activo', true);
    }
}
