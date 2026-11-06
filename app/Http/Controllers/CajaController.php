<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\CorteCaja;
use App\Models\CorteCiego;
use App\Models\MovimientoCaja;
use App\Models\Sucursal;
use App\Models\TipoAlerta;
use App\Models\User;
use App\Models\Venta;
use App\Support\CierreCajaTurno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CajaController extends Controller
{
    /**
     * Turnos oficiales del sistema: solo Mañana y Tarde.
     *
     * Se persiste como Matutino/Vespertino para compatibilidad histórica.
     */
    public const TURNOS = [
        'Matutino' => 'Mañana',
        'Vespertino' => 'Tarde',
    ];

    /**
     * Panel principal de caja: cortes abiertos y operación diaria (sin historial para aligerar).
     */
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $sucursalId = (int) ($request->query('sucursal') ?? session('active_sucursal_id') ?? $usuario?->id_sucursal ?? 0);
        if ($sucursalId <= 0) {
            $sucursalId = (int) (Sucursal::orderBy('nombre_sucursal')->value('id') ?? 0);
        }

        $sucursal = $sucursalId ? Sucursal::find($sucursalId) : null;
        $sucursales = Sucursal::where('es_activa', true)->orderBy('nombre_sucursal')->get();

        $cortesAbiertos = collect();
        $miCorte = null;
        $esCorteCiego = false;
        $historialCount = 0;

        if ($sucursal) {
            // Cierra de forma perezosa las cajas que ya vencieron su turno
            // (respaldo del comando programado, útil al recargar la página).
            CierreCajaTurno::procesar($sucursal->id);

            $cortesAbiertos = CorteCaja::with(['usuario', 'movimientos.usuario'])
                ->where('id_sucursal', $sucursal->id)
                ->where('estado', 'abierto')
                ->orderByDesc('fecha_inicio')
                ->get()
                ->each(fn (CorteCaja $c) => $c->setAttribute('efectivo_esperado_calculado', $c->efectivoEsperado()));

            $miCorte = $usuario
                ? $cortesAbiertos->firstWhere('id_usuario', $usuario->id)
                : null;

            $historialCount = CorteCaja::where('id_sucursal', $sucursal->id)->where('estado', 'cerrado')->count();
            $esCorteCiego = $usuario ? (bool) CorteCiego::where('id_usuarios', $usuario->id)->value('corte_ciego') : false;
        }

        // Resumen del día para la sucursal
        $resumenHoy = null;
        if ($sucursal) {
            $hoyInicio = now()->startOfDay();
            $ventasHoy = Venta::where('id_sucursal', $sucursal->id)->where('creado_en', '>=', $hoyInicio)->with('pago')->get();
            $totalHoy = (float) $ventasHoy->sum('total');
            $efectivoHoy = (float) $ventasHoy->filter(fn (Venta $v) => strcasecmp($v->pago?->metodo ?? '', 'Efectivo') === 0)->sum('total');
            $tarjetaHoy = (float) $ventasHoy->filter(fn (Venta $v) => strcasecmp($v->pago?->metodo ?? '', 'Tarjeta') === 0)->sum('total');
            $transferenciaHoy = (float) $ventasHoy->filter(fn (Venta $v) => strcasecmp($v->pago?->metodo ?? '', 'Transferencia') === 0)->sum('total');
            $resumenHoy = compact('ventasHoy', 'totalHoy', 'efectivoHoy', 'tarjetaHoy', 'transferenciaHoy');
        }

        return view('pages.caja.index', compact('sucursal', 'sucursales', 'cortesAbiertos', 'miCorte', 'esCorteCiego', 'resumenHoy', 'sucursalId', 'historialCount'));
    }

    /**
     * Ventana dedicada de historial y auditoría (filtros + resumen + trazabilidad completa).
     */
    public function auditoria(Request $request): View
    {
        $usuario = $request->user();
        $sucursalId = (int) ($request->query('sucursal') ?? session('active_sucursal_id') ?? $usuario?->id_sucursal ?? 0);
        if ($sucursalId <= 0) {
            $sucursalId = (int) (Sucursal::orderBy('nombre_sucursal')->value('id') ?? 0);
        }

        $sucursal = $sucursalId ? Sucursal::find($sucursalId) : null;
        $sucursales = Sucursal::where('es_activa', true)->orderBy('nombre_sucursal')->get();

        $historial = collect();
        $audFiltros = [
            'aud_desde' => trim((string) $request->query('aud_desde', '')),
            'aud_hasta' => trim((string) $request->query('aud_hasta', '')),
            'aud_turno' => trim((string) $request->query('aud_turno', '')),
            'aud_caja' => trim((string) $request->query('aud_caja', '')),
            'aud_usuario' => trim((string) $request->query('aud_usuario', '')),
            'aud_estado' => trim((string) $request->query('aud_estado', '')),
        ];
        $auditoriaResumen = null;
        $usuariosFiltro = collect();

        if ($sucursal) {
            $historialQuery = CorteCaja::with(['usuario', 'movimientos.usuario', 'ventas.pago'])
                ->where('id_sucursal', $sucursal->id)
                ->where('estado', 'cerrado');

            if ($audFiltros['aud_desde'] !== '') {
                $historialQuery->whereDate('fecha_cierre', '>=', $audFiltros['aud_desde']);
            }
            if ($audFiltros['aud_hasta'] !== '') {
                $historialQuery->whereDate('fecha_cierre', '<=', $audFiltros['aud_hasta']);
            }
            if (in_array($audFiltros['aud_turno'], array_keys(self::TURNOS), true)) {
                $historialQuery->where('turno', $audFiltros['aud_turno']);
            }
            if ($audFiltros['aud_caja'] !== '') {
                $historialQuery->where('caja', $audFiltros['aud_caja']);
            }
            if ($audFiltros['aud_usuario'] !== '' && ctype_digit($audFiltros['aud_usuario'])) {
                $historialQuery->where('id_usuario', (int) $audFiltros['aud_usuario']);
            }
            if (in_array($audFiltros['aud_estado'], ['cuadrado', 'faltante', 'sobrante'], true)) {
                match ($audFiltros['aud_estado']) {
                    'cuadrado' => $historialQuery->where('diferencia', 0),
                    'faltante' => $historialQuery->where('diferencia', '<', 0),
                    'sobrante' => $historialQuery->where('diferencia', '>', 0),
                    default => null,
                };
            }

            $historial = $historialQuery->orderByDesc('fecha_cierre')->limit(100)->get();

            $totalCierres = $historial->count();
            $cuadrados = $historial->filter(fn (CorteCaja $c) => (float) $c->diferencia === 0.0)->count();
            $faltantes = $historial->filter(fn (CorteCaja $c) => (float) $c->diferencia < 0)->count();
            $sobrantes = $historial->filter(fn (CorteCaja $c) => (float) $c->diferencia > 0)->count();
            $sumDiferencia = round($historial->sum(fn (CorteCaja $c) => (float) $c->diferencia), 2);
            $sumEsperado = round($historial->sum(fn (CorteCaja $c) => (float) $c->efectivo_esperado), 2);
            $sumDeclarado = round($historial->sum(fn (CorteCaja $c) => (float) $c->efectivo_declarado), 2);
            $auditoriaResumen = compact('totalCierres', 'cuadrados', 'faltantes', 'sobrantes', 'sumDiferencia', 'sumEsperado', 'sumDeclarado');

            $usuariosFiltro = User::whereIn('id', $historial->pluck('id_usuario')->unique()->filter())
                ->orWhere('id_sucursal', $sucursal->id)
                ->orderBy('nombre')->limit(50)->get(['id', 'nombre', 'apellido']);
        }

        return view('pages.caja.auditoria', compact('sucursal', 'sucursales', 'historial', 'audFiltros', 'auditoriaResumen', 'usuariosFiltro', 'sucursalId'));
    }

    /**
     * Abre una caja (corte) para la sucursal activa.
     */
    public function abrir(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_sucursal' => ['required', 'integer', 'exists:sucursales,id'],
            'caja' => ['required', 'string', 'max:30'],
            'turno' => ['required', 'string', 'in:Matutino,Vespertino'],
            'efectivo_inicial' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'redirect_to' => ['nullable', 'string', 'in:punto-venta,caja'],
        ]);

        $sucursal = Sucursal::findOrFail($data['id_sucursal']);

        if (! $sucursal->es_activa) {
            return back()->with('error', 'La sucursal seleccionada está inactiva.');
        }

        // Solo una caja abierta por usuario y sucursal: se permite tener cajas abiertas en distintas sucursales.
        $yaTieneAbierta = CorteCaja::where('id_usuario', $request->user()->id)
            ->where('id_sucursal', $sucursal->id)
            ->where('estado', 'abierto')
            ->exists();

        if ($yaTieneAbierta) {
            return back()->with('error', 'Ya tienes una caja abierta en esta sucursal. Cierra tu caja actual antes de abrir otra.');
        }

        // Multi-caja: se permite más de un abierto por sucursal, pero no duplicar misma caja+turno abierta.
        $duplicado = CorteCaja::where('id_sucursal', $sucursal->id)
            ->where('caja', $data['caja'])
            ->where('turno', $data['turno'])
            ->where('estado', 'abierto')
            ->exists();

        if ($duplicado) {
            return back()->with('error', "Ya existe una caja abierta «{$data['caja']}» en turno {$data['turno']} para {$sucursal->nombre_sucursal}. Cierra ese corte antes de abrir otro igual.");
        }

        $corte = CorteCaja::create([
            'id_sucursal' => $sucursal->id,
            'id_usuario' => $request->user()->id,
            'caja' => $data['caja'],
            'turno' => $data['turno'],
            'efectivo_inicial' => $data['efectivo_inicial'],
            'efectivo_declarado' => 0,
            'efectivo_esperado' => $data['efectivo_inicial'],
            'diferencia' => 0,
            'fecha_inicio' => now(),
            'estado' => 'abierto',
        ]);

        // Notificación inmediata de caja activada
        try {
            $tipoCaja = TipoAlerta::where('nombre', 'CIERRE_CAJA')->first();
            if ($tipoCaja) {
                Alerta::create([
                    'id_tipo_alerta' => $tipoCaja->id,
                    'id_sucursal' => $sucursal->id,
                    'entidad_tipo' => 'corte_caja',
                    'entidad_id' => $corte->id,
                    'estado' => Alerta::ESTADO_ACTIVA,
                ]);
            }
        } catch (\Throwable $e) {
            // No bloquear el flujo si falla la notificación
        }

        $mensaje = "Caja «{$data['caja']}» abierta en {$sucursal->nombre_sucursal} — turno {$data['turno']} con $".number_format((float) $data['efectivo_inicial'], 2).' inicial.';

        // Abrir la caja desde el Punto de Venta devuelve al usuario a su venta
        // en curso (el carrito vive en sesión y se conserva) y reabre el cobro.
        if (($data['redirect_to'] ?? null) === 'punto-venta') {
            return redirect()->route('punto-venta.index', ['cobrar' => 1])->with('success', $mensaje);
        }

        return redirect()->route('caja.index', ['sucursal' => $sucursal->id])->with('success', $mensaje);
    }

    /**
     * Registra un movimiento (ingreso/retiro) sobre un corte abierto.
     */
    public function movimiento(Request $request, CorteCaja $corte): RedirectResponse
    {
        if ($corte->estado !== 'abierto') {
            return back()->with('error', 'Solo se pueden registrar movimientos en cajas abiertas.');
        }

        $data = $request->validate([
            'tipo' => ['required', 'in:ingreso,retiro'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        MovimientoCaja::create([
            'id_corte_caja' => $corte->id,
            'id_usuario' => $request->user()->id,
            'tipo' => $data['tipo'],
            'monto' => $data['monto'],
            'motivo' => $data['motivo'] ?? null,
        ]);

        $etiqueta = $data['tipo'] === 'ingreso' ? 'Ingreso' : 'Retiro';

        return back()->with('success', "{$etiqueta} de $".number_format((float) $data['monto'], 2).' registrado en '.$corte->caja.' — '.$corte->turno.'.');
    }

    /**
     * Cierra un corte de caja calculando el esperado real.
     */
    public function cerrar(Request $request, CorteCaja $corte): RedirectResponse
    {
        if ($corte->estado !== 'abierto') {
            return back()->with('error', 'Este corte ya está cerrado.');
        }

        $data = $request->validate([
            'efectivo_declarado' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'redirect_to' => ['nullable', 'string', 'in:punto-venta,caja'],
        ]);

        $esperado = $corte->efectivoEsperado();
        $declarado = (float) $data['efectivo_declarado'];
        $diferencia = round($declarado - $esperado, 2);

        $corte->update([
            'efectivo_declarado' => $declarado,
            'efectivo_esperado' => $esperado,
            'diferencia' => $diferencia,
            'fecha_cierre' => now(),
            'estado' => 'cerrado',
            'cierre_automatico' => false,
        ]);

        $mensaje = $diferencia === 0.0
            ? "Caja «{$corte->caja}» cerrada sin diferencia. Esperado y declarado: $".number_format($esperado, 2).'.'
            : "Caja «{$corte->caja}» cerrada. Esperado $".number_format($esperado, 2).', declarado $'.number_format($declarado, 2).' — diferencia $'.number_format($diferencia, 2).'.';

        // Cerrar la caja desde el Punto de Venta devuelve al usuario a su
        // operación (el carrito vive en sesión y se conserva).
        if (($data['redirect_to'] ?? null) === 'punto-venta') {
            return redirect()->route('punto-venta.index')->with('success', $mensaje);
        }

        return back()->with('success', $mensaje);
    }
}
