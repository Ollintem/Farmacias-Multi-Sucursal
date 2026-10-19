<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $traspaso
 * @property int $id_lote
 * @property int $cantidad
 * @property-read Traspaso $traspasoRelacion
 * @property-read Lote $lote
 */
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

    /**
     * @return BelongsTo<Traspaso, $this>
     */
    public function traspasoRelacion(): BelongsTo
    {
        return $this->belongsTo(Traspaso::class, 'traspaso');
    }

    /**
     * @return BelongsTo<Lote, $this>
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }
}
