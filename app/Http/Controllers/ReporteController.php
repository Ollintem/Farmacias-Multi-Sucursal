<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\User;
use App\Services\VentasReporteService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function __construct(private readonly VentasReporteService $servicio) {}

    /**
     * Pantalla principal del módulo de reportes de ventas.
     */
    public function index(Request $request): View
    {
        $datos = $this->resolverDatos($request);

        return view('pages.reportes.index', $datos);
    }

    /**
     * Vista imprimible del reporte, usada para guardar/descargar el PDF.
     */
    public function pdf(Request $request): View
    {
        $datos = $this->resolverDatos($request);

        return view('pages.reportes.pdf', $datos);
    }

    /**
     * Valida los filtros, obtiene las sucursales permitidas y consulta el
     * servicio de ventas. Pantalla y PDF comparten este mismo flujo.
     *
     * @return array{
     *     reporte: array<string, mixed>,
     *     filtros: array<string, mixed>,
     *     sucursales: Collection<int, Sucursal>,
     *     puedeConsultarVarias: bool
     * }
     */
    private function resolverDatos(Request $request): array
    {
        $usuario = $request->user();

        if (! $usuario instanceof User) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        $hoy = CarbonImmutable::now();
        $tipo = (string) $request->query('tipo', VentasReporteService::TIPO_DIA);

        $fecha = (string) $request->query('fecha', $hoy->toDateString());
        $semana = (string) $request->query('semana', $hoy->format('o-\WW'));
        $mes = (string) $request->query('mes', $hoy->format('Y-m'));

        $sucursales = $this->servicio->sucursalesDisponibles($usuario);
        $puedeConsultarVarias = $this->servicio->puedeConsultarVariasSucursales($usuario);
        $sucursalId = $this->resolverSucursal($request, $usuario, $sucursales, $puedeConsultarVarias);

        $datosValidacion = [
            'tipo' => $tipo,
            'fecha' => $tipo === VentasReporteService::TIPO_DIA ? $fecha : null,
            'semana' => $tipo === VentasReporteService::TIPO_SEMANA ? $semana : null,
            'mes' => $tipo === VentasReporteService::TIPO_MES ? $mes : null,
            'sucursal' => $sucursalId,
        ];

        $reglas = [
            'tipo' => ['required', Rule::in(VentasReporteService::TIPOS)],
            'sucursal' => ['nullable', 'integer'],
        ];

        if ($tipo === VentasReporteService::TIPO_DIA) {
            $reglas['fecha'] = ['required', 'date_format:Y-m-d'];
        } elseif ($tipo === VentasReporteService::TIPO_SEMANA) {
            $reglas['semana'] = ['required', 'regex:/^\d{4}-W(0[1-9]|[1-4]\d|5[0-3])$/'];
        } elseif ($tipo === VentasReporteService::TIPO_MES) {
            $reglas['mes'] = ['required', 'date_format:Y-m'];
        }

        Validator::make($datosValidacion, $reglas)->validate();

        $periodo = match ($tipo) {
            VentasReporteService::TIPO_DIA => $fecha,
            VentasReporteService::TIPO_SEMANA => $semana,
            VentasReporteService::TIPO_MES => $mes,
            default => $hoy->toDateString(),
        };

        $filtros = [
            'tipo' => $tipo,
            'fecha' => $fecha,
            'semana' => $semana,
            'mes' => $mes,
            'sucursal' => $sucursalId,
        ];

        return [
            'reporte' => $this->servicio->generar($tipo, $sucursalId, $periodo),
            'filtros' => $filtros,
            'sucursales' => $sucursales,
            'puedeConsultarVarias' => $puedeConsultarVarias,
        ];
    }

    /**
     * Resuelve la sucursal del reporte respetando los permisos del usuario.
     *
     * Los usuarios con sucursal fija siempre consultan la suya. Quienes
     * pueden ver varias sucursales pueden elegir una o dejar el filtro vacío
     * para obtener el consolidado de todas.
     *
     * @param  Collection<int, Sucursal>  $sucursales
     */
    private function resolverSucursal(
        Request $request,
        User $usuario,
        Collection $sucursales,
        bool $puedeConsultarVarias
    ): ?int {
        if (! $puedeConsultarVarias) {
            return $usuario->id_sucursal !== null
                ? (int) $usuario->id_sucursal
                : $sucursales->first()?->id;
        }

        if ($request->query->has('sucursal')) {
            $valor = trim((string) $request->query('sucursal'));

            if ($valor === '') {
                return null;
            }

            $id = (int) $valor;

            return $sucursales->contains(fn (Sucursal $sucursal): bool => $sucursal->id === $id)
                ? $id
                : null;
        }

        $activa = (int) session('active_sucursal_id', 0);

        return $activa > 0 && $sucursales->contains(fn (Sucursal $sucursal): bool => $sucursal->id === $activa)
            ? $activa
            : null;
    }
}
