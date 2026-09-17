<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'codigo_barras',
        'nombre_producto',
        'descripcion',
        'stock',
        'precio',
        'id_lote',
        'id_presentacion',
        'es_controlado',
        'es_activo',
    ];

    protected $casts = [
        'es_controlado' => 'boolean',
        'es_activo' => 'boolean',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function sucursales(): BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class, 'producto_sucursal', 'producto', 'sucursal');
    }
}
