<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Sucursal;
use App\Models\Traspaso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TraspasoController extends Controller
{
    /**
     * Redirige al listado unificado de entradas filtrado por traspasos.
     *
     * Entrada: query string opcional `sucursal`.
     * Salida: redirección a entradas-de-almacen.index con tipo=traspasos.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('entradas-de-almacen.index', array_filter([
            'sucursal' => $request->query('sucursal'),
            'tipo' => 'traspasos',
        ]));
    }

    /**
     * Muestra el formulario de alta de traspasos entre sucursales.
     *
     * Solo el SuperAdmin elige la sucursal origen (el destino es su
     * sucursal activa); los demás usuarios tienen el origen fijo en su
     * sucursal activa y eligen el destino. Los lotes se filtran por el
     * inventario del origen (Lotes → Producto → Inventario → Sucursal
     * origen) y el stock mostrado es `inventario.stock` de esa sucursal,
     * nunca `lotes.stock` ni sumas globales.
     *
     * La sucursal elegible viaja en `?origen=` (SuperAdmin) o `?destino=`
     * (el parámetro `?sucursal` lo reserva el middleware para la sucursal
     * activa). Sin elección no se ofrecen lotes en el SELECT.
     *
     * Entrada: query string opcional `origen` o `destino` según el rol.
     * Salida: resources/views/pages/entradas/create-traspaso.blade.php.
     */
    public function create(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $usuarioActual = $request->user();
        $primeraSucursal = $sucursales->first();
        $activaId = (int) (session('active_sucursal_id')
            ?? ($usuarioActual !== null ? $usuarioActual->id_sucursal : null)
            ?? ($primeraSucursal !== null ? $primeraSucursal->id : null)
            ?? 0);
        $fija = $sucursales->firstWhere('id', $activaId) ?? $sucursales->first();
        $opciones = $sucursales->where('id', '!==', $fija?->id)->values();

        $esSuperAdmin = $usuarioActual?->rol?->tipo_rol === 'SuperAdmin';
        $campoPicker = $esSuperAdmin ? 'sucursal_a' : 'sucursal_b';
        $campoFijo = $esSuperAdmin ? 'sucursal_b' : 'sucursal_a';
        $paramQuery = $esSuperAdmin ? 'origen' : 'destino';

        $elegidoId = (int) old($campoPicker, $request->query($paramQuery, 0));
        $elegidoValido = $elegidoId > 0 && $opciones->contains('id', $elegidoId);

        $origenLotesId = $esSuperAdmin ? ($elegidoValido ? $elegidoId : null) : ($fija !== null ? $fija->id : null);

        $lotesDisponibles = ($elegidoValido && $origenLotesId)
            ? Lote::query()
                ->with(['producto.presentacionesPrecio.presentacion', 'inventarios' => fn ($query) => $query->where('id_sucursal', $origenLotesId)])
                ->whereHas('inventarios', fn ($query) => $query->where('id_sucursal', $origenLotesId)->where('stock_individual', '>', 0))
                ->where(function ($query): void {
                    $query->whereNull('fecha_de_caducidad')
                        ->orWhere('fecha_de_caducidad', '>=', now()->toDateString());
                })
                ->where(function ($query): void {
                    $query->whereNull('fecha_caducidad')
                        ->orWhere('fecha_caducidad', '>=', now()->toDateString());
                })
                ->orderBy('folio')
                ->get()
                ->filter(fn (Lote $lote) => ! $lote->estaCaducado() && (int) $lote->inventarios->sum('stock_individual') > 0)
                ->values()
            : collect();

        $sucursalOrigenLotes = $origenLotesId !== null ? $sucursales->firstWhere('id', $origenLotesId) : null;

        return view('pages.entradas.create-traspaso', [
            'esSuperAdmin' => $esSuperAdmin,
            'fija' => $fija,
            'fijaEsDestino' => $esSuperAdmin,
            'opcionesSucursal' => $opciones,
            'campoPicker' => $campoPicker,
            'campoFijo' => $campoFijo,
            'paramQuery' => $paramQuery,
            'valorPicker' => old($campoPicker, $request->query($paramQuery, '')),
            'mostrarLotes' => $elegidoValido,
            'origenLotesId' => $origenLotesId,
            'nombreOrigenLotes' => $sucursalOrigenLotes !== null ? $sucursalOrigenLotes->nombre_sucursal : 'la sucursal origen elegida',
            'lotesDisponibles' => $lotesDisponibles,
            'sucursalLink' => old($esSuperAdmin ? 'sucursal_a' : 'sucursal_b', $request->query($paramQuery, '')) ?: $activaId,
        ]);
    }

    /**
     * Valida y registra un traspaso entre dos sucursales distintas.
     *
     * Para el SuperAdmin el destino es su sucursal activa y elige el
     * origen; para los demás el origen es su sucursal activa y eligen el
     * destino. El estado siempre nace como pendiente: no se confía en lo
     * enviado desde el frontend.
     *
     * Entrada: sucursal elegible, mensaje opcional y listado de lotes con
     * cantidades para `detalles_traspaso`.
     * Salida: redirección al listado de entradas filtrado por traspasos.
     */
    public function store(Request $request): RedirectResponse
    {
        $usuarioActual = $request->user();
        $activaId = (int) (session('active_sucursal_id') ?? ($usuarioActual !== null ? $usuarioActual->id_sucursal : null) ?? 0);

        if ($activaId <= 0) {
            return back()->with('error', 'No se pudo determinar tu sucursal activa. Vuelve a elegir sucursal activa.')->withInput();
        }

        $esSuperAdmin = $usuarioActual?->rol?->tipo_rol === 'SuperAdmin';

        if ($esSuperAdmin) {
            $destinoId = $activaId;
            $data = $request->validate([
                'sucursal_a' => ['required', 'exists:sucursales,id', Rule::notIn([$destinoId])],
                'mensaje' => ['nullable', 'string', 'max:1000'],
                'lotes' => ['required', 'array', 'min:1'],
                'lotes.*.lote' => ['required', 'exists:lotes,id'],
                'lotes.*.cantidad' => ['required', 'integer', 'min:1', 'max:10000'],
            ], [
                'sucursal_a.not_in' => 'La sucursal origen debe ser diferente a la sucursal destino.',
            ]);
            $origenId = (int) $data['sucursal_a'];
        } else {
            $origenId = $activaId;
            $data = $request->validate([
                'sucursal_b' => ['required', 'exists:sucursales,id', Rule::notIn([$origenId])],
                'mensaje' => ['nullable', 'string', 'max:1000'],
                'lotes' => ['required', 'array', 'min:1'],
                'lotes.*.lote' => ['required', 'exists:lotes,id'],
                'lotes.*.cantidad' => ['required', 'integer', 'min:1', 'max:10000'],
            ], [
                'sucursal_b.not_in' => 'La sucursal destino debe ser diferente a la sucursal origen.',
            ]);
            $destinoId = (int) $data['sucursal_b'];
        }

        /** @var array<int, array{lote: int, cantidad: int}> $lotesSolicitados */
        $lotesSolicitados = $data['lotes'];

        $lotes = Lote::query()
            ->with('producto')
            ->whereIn('id', collect($lotesSolicitados)->pluck('lote'))
            ->get()
            ->keyBy('id');

        $errores = [];

        foreach (array_values($data['lotes']) as $indice => $item) {
            $lote = $lotes->get($item['lote']);

            if ($lote === null) {
                continue;
            }

            if ($lote->estaCaducado()) {
                $errores["lotes.{$indice}.lote"] = 'El lote está caducado y no puede traspasarse.';

                continue;
            }

            $stockOrigen = (int) Inventario::query()
                ->where('id_sucursal', $origenId)
                ->where('id_lote', $lote->id)
                ->sum('stock_individual');

            if ($stockOrigen < (int) $item['cantidad']) {
                $errores["lotes.{$indice}.cantidad"] = "Stock insuficiente en origen. Disponible: {$stockOrigen} uds.";
            }
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }

        $detalles = collect($lotesSolicitados)
            ->map(function (array $item): array {
                return [
                    'id_lote' => (int) $item['lote'],
                    'cantidad' => (int) $item['cantidad'],
                ];
            })
            ->values();

        DB::transaction(function () use ($data, $detalles, $request, $origenId, $destinoId): void {
            $traspaso = Traspaso::create([
                'sucursal_a' => $origenId,
                'sucursal_b' => $destinoId,
                'mensaje' => $data['mensaje'] ?? null,
                'pedido_por' => $request->user()->id,
                'estado' => 'pendiente',
            ]);

            $traspaso->detalles()->createMany($detalles->all());
        });

        return redirect()->route('entradas-de-almacen.index', [
            'sucursal' => $activaId,
            'tipo' => 'traspasos',
        ])->with('success', 'Solicitud de traspaso registrada. La sucursal origen debe enviarla.');
    }

    /**
     * Envía un traspaso pendiente: descuenta el origen y deja la mercancía en tránsito.
     *
     * Flujo: PENDIENTE → ENVIADO. El destino NO incrementa su inventario en
     * este momento. Solo la sucursal origen (o SuperAdmin) puede enviarlo y
     * solo una vez: un segundo envío devuelve error sin tocar el stock.
     *
     * Entrada: id del traspaso pendiente donde la sucursal activa es el origen.
     * Salida: redirección a entradas filtrado por traspasos.
     */
    public function enviar(Request $request, Traspaso $traspaso): RedirectResponse
    {
        if (strtolower((string) $traspaso->estado) !== 'pendiente') {
            return back()->with('error', 'El traspaso ya fue enviado o procesado.');
        }

        if (! $this->puedeEnviar($request, $traspaso)) {
            return back()->with('error', 'Solo la sucursal origen puede enviar este traspaso.');
        }

        try {
            DB::transaction(function () use ($traspaso): void {
                app(LotesController::class)->descontarOrigen($traspaso);

                $traspaso->update(['estado' => 'enviado']);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('entradas-de-almacen.index', [
            'sucursal' => $traspaso->sucursal_a,
            'tipo' => 'traspasos',
        ])->with('success', "Traspaso T-{$traspaso->id} enviado. Mercancía en tránsito.");
    }

    /**
     * Id de la sucursal activa: sesión, sucursal del usuario o 0.
     *
     * Entrada: request con el usuario autenticado.
     * Salida: id de sucursal (0 si no hay ninguna disponible).
     */
    private function sucursalActivaId(Request $request): int
    {
        $usuario = $request->user();
        $idSucursal = $usuario !== null ? $usuario->id_sucursal : null;

        return (int) (session('active_sucursal_id') ?? $idSucursal ?? 0);
    }

    /**
     * Indica si el usuario actual puede enviar el traspaso (es origen o SuperAdmin).
     */
    private function puedeEnviar(Request $request, Traspaso $traspaso): bool
    {
        $usuario = $request->user();

        if ($usuario?->rol?->tipo_rol === 'SuperAdmin') {
            return true;
        }

        $sucursalActiva = $this->sucursalActivaId($request);

        return $sucursalActiva > 0 && $sucursalActiva === (int) $traspaso->sucursal_a;
    }

    /**
     * Recibe un traspaso en tránsito e incrementa el inventario destino.
     *
     * Flujo: ENVIADO → ACEPTADO (recibido). El origen NO vuelve a
     * descontarse: la salida ya ocurrió al enviar. Solo procede una vez.
     *
     * Entrada: id del traspaso en tránsito donde la sucursal activa es el destino.
     * Salida: redirección a alertas con mensaje de resultado.
     */
    public function aceptar(Request $request, Traspaso $traspaso): RedirectResponse
    {
        $estado = strtolower((string) $traspaso->estado);

        if ($estado === 'pendiente') {
            return back()->with('error', 'El traspaso aún no fue enviado. La sucursal origen debe enviarlo primero.');
        }

        if ($estado !== 'enviado') {
            return back()->with('error', 'El traspaso ya fue respondido.');
        }

        if (! $this->puedeResponder($request, $traspaso)) {
            return back()->with('error', 'Solo la sucursal destino puede aceptar este traspaso.');
        }

        try {
            DB::transaction(function () use ($traspaso, $request) {
                app(LotesController::class)->incrementarDestino($traspaso);

                $traspaso->update([
                    'estado' => 'aceptado',
                    'recibido_por' => $request->user()->id,
                    'respondido_en' => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('alertas.index', [
            'sucursal' => $traspaso->sucursal_b,
            'filtro' => 'traspasos',
        ])->with('success', "Traspaso T-{$traspaso->id} aceptado. Inventario actualizado.");
    }

    /**
     * Rechaza un traspaso pendiente o en tránsito con motivo opcional.
     *
     * Si ya se había enviado, la mercancía se devuelve al origen antes de
     * marcarlo rechazado; si sigue pendiente no hay nada que devolver.
     *
     * Entrada: id del traspaso y motivo de rechazo opcional.
     * Salida: redirección a alertas con mensaje de resultado.
     */
    public function rechazar(Request $request, Traspaso $traspaso): RedirectResponse
    {
        $data = $request->validate([
            'motivo_respuesta' => ['nullable', 'string', 'max:1000'],
        ]);

        $estado = strtolower((string) $traspaso->estado);

        if (! in_array($estado, ['pendiente', 'enviado'], true)) {
            return back()->with('error', 'El traspaso ya fue respondido.');
        }

        if (! $this->puedeResponder($request, $traspaso)) {
            return back()->with('error', 'Solo la sucursal destino puede rechazar este traspaso.');
        }

        try {
            DB::transaction(function () use ($traspaso, $data, $request, $estado): void {
                if ($estado === 'enviado') {
                    app(LotesController::class)->restaurarOrigen($traspaso);
                }

                $traspaso->update([
                    'estado' => 'rechazado',
                    'recibido_por' => $request->user()->id,
                    'motivo_respuesta' => $data['motivo_respuesta'] ?? null,
                    'respondido_en' => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('alertas.index', [
            'sucursal' => $traspaso->sucursal_b,
            'filtro' => 'traspasos',
        ])->with('success', "Traspaso T-{$traspaso->id} rechazado.");
    }

    /**
     * Cancela un traspaso pendiente o en tránsito solicitado por la sucursal origen.
     *
     * Si ya se había enviado, la mercancía se devuelve al origen antes de
     * marcarlo cancelado; si sigue pendiente no hay nada que devolver porque
     * la mercancía aún no salió del origen.
     *
     * Entrada: id del traspaso donde la sucursal activa es el origen.
     * Salida: redirección a entradas filtrado por traspasos.
     */
    public function cancelar(Request $request, Traspaso $traspaso): RedirectResponse
    {
        $estado = strtolower((string) $traspaso->estado);

        if (! in_array($estado, ['pendiente', 'enviado'], true)) {
            return back()->with('error', 'El traspaso ya fue respondido y no puede cancelarse.');
        }

        if (! $this->puedeCancelar($request, $traspaso)) {
            return back()->with('error', 'Solo la sucursal origen puede cancelar este traspaso.');
        }

        try {
            DB::transaction(function () use ($traspaso, $estado): void {
                if ($estado === 'enviado') {
                    app(LotesController::class)->restaurarOrigen($traspaso);
                }

                $traspaso->update([
                    'estado' => 'cancelado',
                    'respondido_en' => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('entradas-de-almacen.index', [
            'sucursal' => $traspaso->sucursal_a,
            'tipo' => 'traspasos',
        ])->with('success', "Traspaso T-{$traspaso->id} cancelado.");
    }

    /**
     * Indica si el usuario actual puede cancelar el traspaso (es origen o SuperAdmin).
     */
    private function puedeCancelar(Request $request, Traspaso $traspaso): bool
    {
        $usuario = $request->user();

        if ($usuario?->rol?->tipo_rol === 'SuperAdmin') {
            return true;
        }

        $sucursalActiva = $this->sucursalActivaId($request);

        return $sucursalActiva > 0 && $sucursalActiva === (int) $traspaso->sucursal_a;
    }

    /**
     * Indica si el usuario actual puede responder el traspaso (es destino o SuperAdmin).
     */
    private function puedeResponder(Request $request, Traspaso $traspaso): bool
    {
        $usuario = $request->user();

        if ($usuario?->rol?->tipo_rol === 'SuperAdmin') {
            return true;
        }

        $sucursalActiva = $this->sucursalActivaId($request);

        return $sucursalActiva > 0 && $sucursalActiva === (int) $traspaso->sucursal_b;
    }
}
