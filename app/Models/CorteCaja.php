<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $id_sucursal
 * @property int $id_usuario
 * @property string $turno
 * @property string $efectivo_inicial
 * @property string $efectivo_declarado
 * @property string $efectivo_esperado
 * @property string $diferencia
 * @property Carbon $fecha_inicio
 * @property Carbon|null $fecha_cierre
 * @property string $estado
 * @property Carbon $creado_en
 * @property-read Sucursal $sucursal
 * @property-read User $usuario
 */
class CorteCaja extends Model
{
    public $timestamps = false;

    protected $table = 'cortes_caja';

    protected $fillable = [
        'id_sucursal',
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
        'id_sucursal' => 'integer',
        'id_usuario' => 'integer',
        'efectivo_inicial' => 'decimal:2',
        'efectivo_declarado' => 'decimal:2',
        'efectivo_esperado' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_cierre' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
