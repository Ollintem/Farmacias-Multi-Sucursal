<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function preciosPorProducto(): HasMany
    {
        return $this->hasMany(ProductoPresentacion::class, 'id_presentacion');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_presentacion');
    }

    public function scopeActivas($query)
    {
        return $query->where('es_activo', true);
    }
}
