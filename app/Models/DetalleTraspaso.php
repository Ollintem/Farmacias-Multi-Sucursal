<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleTraspaso extends Model
{
    public $timestamps = false;

    protected $table = 'detalles_traspaso';

    protected $fillable = [
        'traspaso',
        'id_lote',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'integer',
    ];

    public function traspasoRelacion(): BelongsTo
    {
        return $this->belongsTo(Traspaso::class, 'traspaso');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }
}
