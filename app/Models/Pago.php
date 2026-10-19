<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $monto
 * @property string $metodo
 * @property string $estado
 * @property string $referencia
 * @property Carbon $creado_en
 */
class Pago extends Model
{
    public $timestamps = false;

    protected $table = 'pagos';

    protected $fillable = [
        'monto',
        'metodo',
        'estado',
        'referencia',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'creado_en' => 'datetime',
    ];

    /**
     * @return HasMany<Venta, $this>
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_pago');
    }
}
