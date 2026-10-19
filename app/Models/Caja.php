<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_sucursal
 * @property Carbon $creado_en
 * @property Carbon $actualizado_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sucursal $sucursal
 */
class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'id_sucursal',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    /**
     * @return HasMany<Venta, $this>
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_caja');
    }

    /**
     * @return HasMany<CorteCaja, $this>
     */
    public function cortes(): HasMany
    {
        return $this->hasMany(CorteCaja::class, 'id_caja');
    }
}
