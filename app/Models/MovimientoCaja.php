<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_corte_caja
 * @property int $id_usuario
 * @property string $tipo
 * @property string $monto
 * @property string|null $motivo
 * @property Carbon $creado_en
 * @property-read CorteCaja $corte
 * @property-read User $usuario
 */
class MovimientoCaja extends Model
{
    public $timestamps = false;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'id_corte_caja',
        'id_usuario',
        'tipo',
        'monto',
        'motivo',
    ];

    protected $casts = [
        'id_corte_caja' => 'integer',
        'id_usuario' => 'integer',
        'monto' => 'decimal:2',
        'creado_en' => 'datetime',
    ];

    /**
     * @return BelongsTo<CorteCaja, $this>
     */
    public function corte(): BelongsTo
    {
        return $this->belongsTo(CorteCaja::class, 'id_corte_caja');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
