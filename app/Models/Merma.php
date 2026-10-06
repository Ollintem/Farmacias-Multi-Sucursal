<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Merma extends Model
{
    public $timestamps = false;

    protected $table = 'mermas';

    protected $fillable = [
        'id_sucursal',
        'id_lote',
        'cantidad',
        'motivo',
        'nota',
        'id_usuario',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'creado_en' => 'datetime',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
