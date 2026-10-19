<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_sucursal
 * @property int $id_lote
 * @property int $cantidad
 * @property string $motivo
 * @property string|null $nota
 * @property int $id_usuario
 * @property Carbon $creado_en
 * @property-read Sucursal $sucursal
 * @property-read Lote $lote
 * @property-read User $usuario
 */
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

    /**
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    /**
     * @return BelongsTo<Lote, $this>
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
