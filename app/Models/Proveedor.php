<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre_proveedor',
        'direccion',
        'unidad_entrega',
        'telefono',
        'correo',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_proveedor');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_proveedor');
    }
}
