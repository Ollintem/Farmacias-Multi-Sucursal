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
    ];

    public function preciosPorProducto(): HasMany
    {
        return $this->hasMany(ProductoPresentacion::class, 'id_presentacion');
    }
}
