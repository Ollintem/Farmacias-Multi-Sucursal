<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pago extends Model
{
    public $timestamps = false;

    protected $table = 'pagos';

    protected $fillable = [
        'monto',
        'metodo',
        'estado',
        'referencia',
    ];

    protected $casts = [
        'monto' => 'double',
        'creado_en' => 'datetime',
    ];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_pago');
    }
}
