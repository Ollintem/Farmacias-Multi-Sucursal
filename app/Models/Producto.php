<?php

namespace App\Models;

use App\Models\Traits\BelongsToSucursal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    use BelongsToSucursal;

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

    public function presentacion(): BelongsTo
    {
        return $this->belongsTo(PresentacionProducto::class, 'id_presentacion');
    }

    public function sucursales(): BelongsToMany
    {
        return $this->belongsToMany(Sucursal::class, 'producto_sucursal', 'producto', 'sucursal');
    }
}
