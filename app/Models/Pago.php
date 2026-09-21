<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
