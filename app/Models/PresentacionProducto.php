<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresentacionProducto extends Model
{
    protected $table = 'presentacion_productos';

    public $timestamps = false;

    protected $fillable = [
        'presentacion',
        'descripcion',
    ];
}
