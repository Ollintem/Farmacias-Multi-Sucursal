<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionBancaria extends Model
{
    public $timestamps = false;

    protected $table = 'configuracion_bancaria';

    protected $fillable = [
        'banco',
        'clabe',
        'beneficiario',
        'numero_cuenta',
        'correo_contacto',
        'created_at',
        'updated_at',
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
