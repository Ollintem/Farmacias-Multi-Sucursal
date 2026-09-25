<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionBancaria extends Model
{
    protected $table = 'configuracion_bancaria';

    protected $fillable = [
        'banco',
        'clabe',
        'beneficiario',
        'numero_cuenta',
        'correo_contacto',
    ];

    public static function obtener(): static
    {
        return static::firstOrCreate([], [
            'banco' => '',
            'clabe' => '',
            'beneficiario' => '',
            'numero_cuenta' => '',
            'correo_contacto' => '',
        ]);
    }
}
