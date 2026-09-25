<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorteCaja extends Model
{
    public $timestamps = false;

    protected $table = 'cortes_caja';

    protected $fillable = [
        'id_caja',
        'id_usuario',
        'turno',
        'efectivo_inicial',
        'efectivo_declarado',
        'efectivo_esperado',
        'diferencia',
        'fecha_inicio',
        'fecha_cierre',
        'estado',
    ];

    protected $casts = [
        'efectivo_inicial' => 'double',
        'efectivo_declarado' => 'double',
        'efectivo_esperado' => 'double',
        'diferencia' => 'double',
        'fecha_inicio' => 'datetime',
        'fecha_cierre' => 'datetime',
        'creado_en' => 'datetime',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
