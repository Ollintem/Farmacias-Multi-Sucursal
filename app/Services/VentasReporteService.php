<?php

namespace App\Services;

use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza las consultas de ventas usadas por el módulo de reportes.
 *
 * Todas las fechas se resuelven aquí: el controlador y las vistas solo
 * consumen el arreglo devuelto por generar(), de modo que la pantalla y el
 * PDF siempre trabajan con exactamente los mismos datos.
 */
class VentasReporteService
{
    public const TIPO_DIA = 'dia';

    public const TIPO_SEMANA = 'semana';

    public const TIPO_MES = 'mes';

    /** @var array<int, string> */
    public const TIPOS = [self::TIPO_DIA, self::TIPO_SEMANA, self::TIPO_MES];

    /** @var array<int, string> */
    private const MESES = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre',
    ];

    /**
     * Sucursales que el usuario puede consultar.
     *
     * Un usuario con sucursal asignada solo puede ver la suya. El SuperAdmin
     * y los usuarios sin sucursal fija pueden consultar todas y, por lo tanto,
     * también consolidar el reporte sin seleccionar una sucursal.
     *
     * @return SupportCollection<int, Sucursal>
     */
    public function sucursalesDisponibles(User $usuario): SupportCollection
    {
        $consulta = Sucursal::query()
            ->where('es_activa', true)
            ->orderBy('nombre_sucursal');

        if (! $this->puedeConsultarVariasSucursales($usuario)) {
            $consulta->whereKey($usuario->id_sucursal);
        }

        return $consulta->get();
    }

    /**
     * Indica si el usuario puede consultar más de una sucursal.
     */
    public function puedeConsultarVariasSucursales(User $usuario): bool
    {
        return $usuario->esSuperAdmin() || $usuario->id_sucursal === null;
    }

    /**
     * Reporte de ventas de un día completo.
     *
     * @return array<string, mixed>
     */
    public function ventasPorDia(string $fecha, ?int $idSucursal = null): array
    {
        return $this->generar(self::TIPO_DIA, $idSucursal, $fecha);
    }

    /**
     * Reporte de ventas de una semana ISO (lunes a domingo).
     *
     * @return array<string, mixed>
     */
    public function ventasPorSemana(string $semana, ?int $idSucursal = null): array
    {
        return $this->generar(self::TIPO_SEMANA, $idSucursal, $semana);
    }

    /**
     * Reporte de ventas de un mes calendario.
     *
     * @return array<string, mixed>
     */
    public function ventasPorMes(string $mes, ?int $idSucursal = null): array
    {
        return $this->generar(self::TIPO_MES, $idSucursal, $mes);
    }

    /**
     * Construye el reporte solicitado con sus ventas y totales.
     *
     * @return array{
     *     tipo: string,
     *     periodo: string,
     *     inicio: CarbonImmutable,
     *     fin: CarbonImmutable,
     *     etiqueta: string,
     *     sucursal: Sucursal|null,
     *     ventas: Collection<int, Venta>,
     *     totales: array<string, mixed>
     * }
     */
    public function generar(string $tipo, ?int $idSucursal, string $periodo): array
    {
        $rango = $this->resolverRango($tipo, $periodo);

        $ventas = $this->consultaVentas($idSucursal)
            ->whereBetween('ventas.creado_en', [$rango['inicio'], $rango['fin']])
            ->orderBy('ventas.creado_en')
            ->orderBy('ventas.id')
            ->get();

        return [
            'tipo' => $tipo,
            'periodo' => $periodo,
            'inicio' => $rango['inicio'],
            'fin' => $rango['fin'],
            'etiqueta' => $rango['etiqueta'],
            'sucursal' => $idSucursal !== null ? Sucursal::find($idSucursal) : null,
            'ventas' => $ventas,
            'totales' => $this->calcularTotales($ventas),
        ];
    }

    /**
     * Consulta base de ventas con sus relaciones para pantalla y PDF.
     *
     * @return Builder<Venta>
     */
    private function consultaVentas(?int $idSucursal): Builder
    {
        return Venta::query()
            ->with([
                'sucursal:id,nombre_sucursal',
                'pago:id,metodo,estado,referencia',
                'corteCaja:id,caja,turno,id_usuario',
                'detalles.productoRelacion:id,nombre_producto,codigo_barras',
            ])
            ->when(
                $idSucursal !== null,
                fn (Builder $consulta) => $consulta->where('ventas.id_sucursal', $idSucursal)
            )
            ->where(function (Builder $consulta): void {
                $consulta->whereNull('ventas.estado')
                    ->orWhereRaw('LOWER(ventas.estado) NOT LIKE ?', ['cancel%']);
            });
    }

    /**
     * Resuelve el rango real de fechas y su etiqueta legible.
     *
     * La semana se maneja conforme al estándar ISO (lunes a domingo). Para
     * la semana y el mes actuales el fin se recorta al día transcurrido.
     *
     * @return array{inicio: CarbonImmutable, fin: CarbonImmutable, etiqueta: string}
     */
    private function resolverRango(string $tipo, string $periodo): array
    {
        $hoy = CarbonImmutable::now()->startOfDay();

        return match ($tipo) {
            self::TIPO_DIA => $this->rangoDia($periodo, $hoy),
            self::TIPO_SEMANA => $this->rangoSemana($periodo, $hoy),
            self::TIPO_MES => $this->rangoMes($periodo, $hoy),
            default => throw ValidationException::withMessages([
                'tipo' => 'El tipo de periodo solicitado no es válido.',
            ]),
        };
    }

    /**
     * @return array{inicio: CarbonImmutable, fin: CarbonImmutable, etiqueta: string}
     */
    private function rangoDia(string $periodo, CarbonImmutable $hoy): array
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodo) !== 1) {
            throw ValidationException::withMessages([
                'fecha' => 'La fecha del reporte no es válida.',
            ]);
        }

        $fecha = CarbonImmutable::createFromFormat('!Y-m-d', $periodo);
        $errores = CarbonImmutable::getLastErrors();

        if (
            ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0))
            || $fecha->format('Y-m-d') !== $periodo
        ) {
            throw ValidationException::withMessages([
                'fecha' => 'La fecha del reporte no es válida.',
            ]);
        }

        if ($fecha->greaterThan($hoy)) {
            throw ValidationException::withMessages([
                'fecha' => 'No se pueden consultar ventas de fechas futuras.',
            ]);
        }

        return [
            'inicio' => $fecha->startOfDay(),
            'fin' => $fecha->endOfDay(),
            'etiqueta' => 'Día '.$fecha->format('d/m/Y'),
        ];
    }

    /**
     * @return array{inicio: CarbonImmutable, fin: CarbonImmutable, etiqueta: string}
     */
    private function rangoSemana(string $periodo, CarbonImmutable $hoy): array
    {
        if (preg_match('/^(\d{4})-W(\d{2})$/', $periodo, $partes) !== 1) {
            throw ValidationException::withMessages([
                'semana' => 'La semana del reporte no es válida.',
            ]);
        }

        $anio = (int) $partes[1];
        $semana = (int) $partes[2];
        $inicio = $hoy->setISODate($anio, $semana, 1)->startOfDay();

        if ($inicio->format('o') !== (string) $anio || (int) $inicio->format('W') !== $semana) {
            throw ValidationException::withMessages([
                'semana' => 'La semana del reporte no es válida.',
            ]);
        }

        $inicioSemanaActual = $hoy->startOfWeek()->startOfDay();

        if ($inicio->greaterThan($inicioSemanaActual)) {
            throw ValidationException::withMessages([
                'semana' => 'No se pueden consultar ventas de semanas futuras.',
            ]);
        }

        $fin = $inicio->equalTo($inicioSemanaActual)
            ? $hoy->endOfDay()
            : $inicio->addDays(6)->endOfDay();

        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'etiqueta' => 'Semana del '.$inicio->format('d/m/Y').' al '.$fin->format('d/m/Y'),
        ];
    }

    /**
     * @return array{inicio: CarbonImmutable, fin: CarbonImmutable, etiqueta: string}
     */
    private function rangoMes(string $periodo, CarbonImmutable $hoy): array
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $periodo, $partes) !== 1) {
            throw ValidationException::withMessages([
                'mes' => 'El mes del reporte no es válido.',
            ]);
        }

        $anio = (int) $partes[1];
        $mes = (int) $partes[2];

        if ($mes < 1 || $mes > 12) {
            throw ValidationException::withMessages([
                'mes' => 'El mes del reporte no es válido.',
            ]);
        }

        $inicio = CarbonImmutable::create($anio, $mes, 1)->startOfDay();
        $inicioMesActual = $hoy->startOfMonth();

        if ($inicio->greaterThan($inicioMesActual)) {
            throw ValidationException::withMessages([
                'mes' => 'No se pueden consultar ventas de meses futuros.',
            ]);
        }

        $fin = $inicio->equalTo($inicioMesActual)
            ? $hoy->endOfDay()
            : $inicio->endOfMonth()->endOfDay();

        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'etiqueta' => ucfirst(self::MESES[$mes]).' de '.$anio,
        ];
    }

    /**
     * Totales del reporte: montos, unidades y desglose por método de pago.
     *
     * @param  Collection<int, Venta>  $ventas
     * @return array<string, mixed>
     */
    private function calcularTotales(Collection $ventas): array
    {
        $total = round((float) $ventas->sum(fn (Venta $venta): float => (float) $venta->total), 2);
        $descuento = round((float) $ventas->sum(fn (Venta $venta): float => (float) $venta->descuento), 2);
        $unidades = (int) $ventas->sum(
            fn (Venta $venta): int => (int) $venta->detalles->sum('cantidad')
        );

        $porMetodo = $ventas
            ->groupBy(fn (Venta $venta): string => (string) $venta->pago->metodo)
            ->map(fn (Collection $grupo, string $metodo): array => [
                'metodo' => $metodo,
                'cantidad' => $grupo->count(),
                'total' => round((float) $grupo->sum(fn (Venta $venta): float => (float) $venta->total), 2),
            ])
            ->values()
            ->all();

        return [
            'cantidad' => $ventas->count(),
            'unidades' => $unidades,
            'subtotal' => round($total + $descuento, 2),
            'descuento' => $descuento,
            'total' => $total,
            'por_metodo' => $porMetodo,
        ];
    }
}
