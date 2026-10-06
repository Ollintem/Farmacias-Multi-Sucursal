<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Sucursal;
use App\Models\Traspaso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Solo se envían los lotes con existencias en inventario y no
     * caducados, para que el formulario no ofrezca mercancía que no
     * puede moverse.
     *
     * Entrada: query string opcional `sucursal` (destino preseleccionado).
     * Salida: resources/views/pages/entradas/create-traspaso.blade.php.
     */
    public function create(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;

        $lotesDisponibles = Lote::query()
            ->with(['producto', 'inventarios'])
            ->whereHas('inventarios', fn ($query) => $query->where('stock', '>', 0))
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
            ->filter(fn (Lote $lote) => ! $lote->estaCaducado() && (int) $lote->inventarios->sum('stock') > 0)
            ->values();

        return view('pages.entradas.create-traspaso', compact('sucursales', 'selectedSucursalId', 'lotesDisponibles'));
    }

    /**
     * Valida y registra un traspaso entre dos sucursales distintas.
     *
     * Entrada: sucursal origen, sucursal destino, mensaje opcional y
     * listado de lotes con cantidades para `detalles_traspaso`.
     * Salida: redirección al listado de entradas filtrado por traspasos.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sucursal_a' => ['required', 'exists:sucursales,id', 'different:sucursal_b'],
            'sucursal_b' => ['required', 'exists:sucursales,id', 'different:sucursal_a'],
            'mensaje' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'string', 'max:20'],
            'lotes' => ['required', 'array', 'min:1'],
            'lotes.*.lote' => ['required', 'exists:lotes,id'],
            'lotes.*.cantidad' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $lotes = Lote::query()
            ->with('producto')
            ->whereIn('id', collect($data['lotes'])->pluck('lote'))
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
                ->where('id_sucursal', (int) $data['sucursal_a'])
                ->where('id_lote', $lote->id)
                ->sum('stock');

            if ($stockOrigen < (int) $item['cantidad']) {
                $errores["lotes.{$indice}.cantidad"] = "Stock insuficiente en origen. Disponible: {$stockOrigen} uds.";
            }
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }

        $detalles = collect($data['lotes'])
            ->map(function (array $item) {
                return [
                    'id_lote' => (int) $item['lote'],
                    'cantidad' => (int) $item['cantidad'],
                ];
            })
            ->values();

        DB::transaction(function () use ($data, $detalles, $request): void {
            $traspaso = Traspaso::create([
                'sucursal_a' => $data['sucursal_a'],
                'sucursal_b' => $data['sucursal_b'],
                'mensaje' => $data['mensaje'] ?? null,
                'pedido_por' => $request->user()->id,
                'estado' => $data['estado'] ?? 'pendiente',
            ]);

            $traspaso->detalles()->createMany($detalles->all());
        });

        return redirect()->route('entradas-de-almacen.index', [
            'sucursal' => $data['sucursal_b'],
            'tipo' => 'traspasos',
        ])->with('success', 'Solicitud de traspaso enviada a la sucursal destino.');
    }

    /**
     * Acepta un traspaso pendiente y mueve el inventario origen → destino por lote.
     *
     * Cada detalle indica el lote exacto y sus unidades: se descuentan de la
     * fila de inventario del origen y se suman en el destino. El lote viaja
     * intacto (mismo id, caducidad y producto).
     *
     * Entrada: id del traspaso pendiente donde la sucursal activa es el destino.
     * Salida: redirección a alertas con mensaje de resultado.
     */
    public function aceptar(Request $request, Traspaso $traspaso): RedirectResponse
    {
        if (! $traspaso->esPendiente()) {
            return back()->with('error', 'El traspaso ya fue respondido.');
        }

        if (! $this->puedeResponder($request, $traspaso)) {
            return back()->with('error', 'Solo la sucursal destino puede aceptar este traspaso.');
        }

        try {
            DB::transaction(function () use ($traspaso, $request) {
                $this->moverLotes((int) $traspaso->sucursal_a, (int) $traspaso->sucursal_b, $traspaso);

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
     * Rechaza un traspaso pendiente con motivo opcional.
     *
     * Entrada: id del traspaso y motivo de rechazo opcional.
     * Salida: redirección a alertas con mensaje de resultado.
     */
    public function rechazar(Request $request, Traspaso $traspaso): RedirectResponse
    {
        $data = $request->validate([
            'motivo_respuesta' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! $traspaso->esPendiente()) {
            return back()->with('error', 'El traspaso ya fue respondido.');
        }

        if (! $this->puedeResponder($request, $traspaso)) {
            return back()->with('error', 'Solo la sucursal destino puede rechazar este traspaso.');
        }

        $traspaso->update([
            'estado' => 'rechazado',
            'recibido_por' => $request->user()->id,
            'motivo_respuesta' => $data['motivo_respuesta'] ?? null,
            'respondido_en' => now(),
        ]);

        return redirect()->route('alertas.index', [
            'sucursal' => $traspaso->sucursal_b,
            'filtro' => 'traspasos',
        ])->with('success', "Traspaso T-{$traspaso->id} rechazado.");
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

        $sucursalActiva = (int) (session('active_sucursal_id') ?? $usuario?->id_sucursal ?? 0);

        return $sucursalActiva > 0 && $sucursalActiva === (int) $traspaso->sucursal_b;
    }

    /**
     * Mueve los lotes de un traspaso entre sucursales con bloqueo de filas.
     *
     * @throws \RuntimeException Cuando el stock en origen es insuficiente.
     */
    private function moverLotes(int $origenId, int $destinoId, Traspaso $traspaso): void
    {
        foreach ($traspaso->detalles()->lockForUpdate()->get() as $detalle) {
            $fila = Inventario::query()
                ->where('id_sucursal', $origenId)
                ->where('id_lote', $detalle->id_lote)
                ->lockForUpdate()
                ->first();

            $disponible = (int) ($fila?->stock ?? 0);

            if ($disponible < (int) $detalle->cantidad) {
                throw new \RuntimeException("Stock insuficiente en origen para el lote {$detalle->id_lote}. Disponible: {$disponible} uds, solicitado: {$detalle->cantidad} uds.");
            }

            $fila->decrement('stock', (int) $detalle->cantidad);

            Inventario::updateOrCreate(
                ['id_sucursal' => $destinoId, 'id_lote' => $detalle->id_lote],
                ['stock' => 0]
            )->increment('stock', (int) $detalle->cantidad);
        }
    }
}
