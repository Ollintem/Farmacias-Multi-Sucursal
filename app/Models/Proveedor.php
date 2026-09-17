<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    public $timestamps = false;

    protected $fillable = [
        'nombre_proveedor',
        'direccion',
        'unidad_entrega',
        'telefono',
        'correo',
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_proveedor');
    }
}
