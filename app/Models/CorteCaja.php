<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $id_sucursal
 * @property int $id_usuario
 * @property string $turno
 * @property string $caja
 * @property string $efectivo_inicial
 * @property string $efectivo_declarado
 * @property string $efectivo_esperado
 * @property string $diferencia
 * @property CarbonImmutable $fecha_inicio
 * @property CarbonImmutable|null $fecha_cierre
 * @property string $estado
 * @property bool $cierre_automatico
 * @property CarbonImmutable $creado_en
 * @property-read Sucursal $sucursal
 * @property-read User $usuario
 * @property-read Collection<int, MovimientoCaja> $movimientos
 * @property-read Collection<int, Venta> $ventas
 */
class CorteCaja extends Model
{
    /**
     * Horario de cierre de cada turno (clave en minúsculas para tolerar los
     * registros históricos guardados como «matutino»/«vespertino»).
     *
     * @var array<string, array{etiqueta: string, cierre: string}>
     */
    public const HORARIOS = [
        'matutino' => ['etiqueta' => 'Mañana', 'cierre' => '15:00'],
        'vespertino' => ['etiqueta' => 'Tarde', 'cierre' => '22:00'],
    ];

    /**
     * Minutos antes del cierre del turno en que se avisa al cajero.
     */
    public const AVISO_FIN_TURNO_MINUTOS = 15;

    /**
     * Minutos de tolerancia después del cierre antes del cierre automático.
     */
    public const TOLERANCIA_CIERRE_MINUTOS = 10;

    public $timestamps = false;

    protected $table = 'cortes_caja';

    protected $fillable = [
        'id_sucursal',
        'id_usuario',
        'turno',
        'caja',
        'efectivo_inicial',
        'efectivo_declarado',
        'efectivo_esperado',
        'diferencia',
        'fecha_inicio',
        'fecha_cierre',
        'estado',
        'cierre_automatico',
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
        'cierre_automatico' => 'boolean',
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

    /**
     * @return HasMany<MovimientoCaja, $this>
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'id_corte_caja');
    }

    /**
     * @return HasMany<Venta, $this>
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'id_corte_caja');
    }

    public function esAbierto(): bool
    {
        return $this->estado === 'abierto';
    }

    /**
     * Clave normalizada del turno para consultar HORARIOS.
     */
    public function claveTurno(): string
    {
        return strtolower(trim((string) $this->turno));
    }

    /**
     * Momento exacto en que termina el turno del corte.
     *
     * Se calcula con la fecha de apertura. Un corte abierto después del
     * cierre de su turno (p. ej. Vespertino a las 22:30) no tiene ventana
     * válida y devuelve null: no se le avisa ni se cierra automáticamente.
     */
    public function finDeTurno(): ?CarbonImmutable
    {
        $horario = self::HORARIOS[$this->claveTurno()] ?? null;

        if ($horario === null || $this->fecha_inicio === null) {
            return null;
        }

        $fin = $this->fecha_inicio->copy()->setTimeFromTimeString($horario['cierre']);

        return $fin->gt($this->fecha_inicio) ? $fin : null;
    }

    /**
     * Minutos que faltan para el cierre del turno (negativos si ya terminó).
     */
    public function minutosParaFinDeTurno(): ?int
    {
        $fin = $this->finDeTurno();

        return $fin === null ? null : (int) now()->diffInMinutes($fin, false);
    }

    /**
     * El turno está por terminar: ya toca avisar al cajero.
     */
    public function estaPorTerminarTurno(int $avisoMinutos = self::AVISO_FIN_TURNO_MINUTOS): bool
    {
        $minutos = $this->minutosParaFinDeTurno();

        return $minutos !== null && $minutos > 0 && $minutos <= $avisoMinutos;
    }

    /**
     * El turno ya terminó; corre la tolerancia para cerrar la caja.
     */
    public function turnoVencido(): bool
    {
        $minutos = $this->minutosParaFinDeTurno();

        return $minutos !== null && $minutos <= 0;
    }

    /**
     * Se agotó la tolerancia: el sistema debe cerrar la caja.
     */
    public function toleranciaVencida(int $toleranciaMinutos = self::TOLERANCIA_CIERRE_MINUTOS): bool
    {
        $minutos = $this->minutosParaFinDeTurno();

        return $minutos !== null && $minutos <= -$toleranciaMinutos;
    }

    /**
     * Efectivo que debería haber en la caja: fondo inicial + ventas en
     * efectivo del corte + ingresos - retiros.
     */
    public function efectivoEsperado(): float
    {
        $inicial = (float) $this->efectivo_inicial;

        $ventasCorte = (float) Venta::where('id_corte_caja', $this->id)->sum('total');

        // Fallback para cortes antiguos sin ventas vinculadas.
        if ($ventasCorte === 0.0) {
            $query = Venta::where('ventas.id_sucursal', $this->id_sucursal)
                ->where('ventas.creado_en', '>=', $this->fecha_inicio);

            if ($this->fecha_cierre) {
                $query->where('ventas.creado_en', '<=', $this->fecha_cierre);
            } else {
                $query->whereNull('ventas.id_corte_caja');
            }

            $ventasCorte = (float) $query->join('pagos', 'pagos.id', '=', 'ventas.id_pago')
                ->where('pagos.metodo', 'Efectivo')
                ->sum('ventas.total');
        }

        $ingresos = (float) MovimientoCaja::where('id_corte_caja', $this->id)->where('tipo', 'ingreso')->sum('monto');
        $retiros = (float) MovimientoCaja::where('id_corte_caja', $this->id)->where('tipo', 'retiro')->sum('monto');

        return round($inicial + $ventasCorte + $ingresos - $retiros, 2);
    }

    /**
     * Cierra el corte sin diferencia, marcándolo como cierre del sistema.
     */
    public function cerrarPorSistema(): bool
    {
        if (! $this->esAbierto()) {
            return false;
        }

        $esperado = $this->efectivoEsperado();

        $this->update([
            'efectivo_declarado' => $esperado,
            'efectivo_esperado' => $esperado,
            'diferencia' => 0,
            'fecha_cierre' => now(),
            'estado' => 'cerrado',
            'cierre_automatico' => true,
        ]);

        return true;
    }
}
