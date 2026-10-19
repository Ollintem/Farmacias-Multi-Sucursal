<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $banco
 * @property string $clabe
 * @property string $beneficiario
 * @property string $numero_cuenta
 * @property string $correo_contacto
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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

    public static function obtener(): self
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
