<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Traspaso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $productos = Producto::where('es_activo', true)->orderBy('nombre_producto')->get(['id', 'nombre_producto', 'stock']);

        return view('pages.entradas.create-traspaso', compact('sucursales', 'selectedSucursalId', 'productos'));
    }

    /**
     * Valida y registra un traspaso entre dos sucursales distintas.
     *
     * Entrada: sucursal origen, sucursal destino, producto, cantidad y mensaje.
     * Salida: redirección al listado de entradas filtrado por traspasos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'sucursal_a' => ['required', 'exists:sucursales,id', 'different:sucursal_b'],
            'sucursal_b' => ['required', 'exists:sucursales,id', 'different:sucursal_a'],
            'id_producto' => ['required', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:10000'],
            'mensaje' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', 'string', 'max:20'],
        ]);

        $stockOrigen = (int) Inventario::forSucursal((int) $data['sucursal_a'])
            ->join('lotes', 'lotes.id', '=', 'inventario.id_lote')
            ->where('lotes.id_producto', (int) $data['id_producto'])
            ->sum('inventario.stock');

        if ($stockOrigen < (int) $data['cantidad']) {
            return back()->withInput()->with('error', "Stock insuficiente en origen. Disponible: {$stockOrigen} uds.");
        }

        Traspaso::create([
            'sucursal_a' => $data['sucursal_a'],
            'sucursal_b' => $data['sucursal_b'],
            'id_producto' => $data['id_producto'],
            'cantidad' => $data['cantidad'],
            'mensaje' => $data['mensaje'] ?? null,
            'pedido_por' => $request->user()->id,
            'estado' => $data['estado'] ?? 'pendiente',
        ]);

        return redirect()->route('alertas.index', [
            'sucursal' => $data['sucursal_b'],
            'seccion' => 'traspasos',
        ])->with('success', 'Solicitud de traspaso enviada a la sucursal destino.');
    }

    /**
     * Acepta un traspaso pendiente y mueve el inventario origen → destino (FIFO por caducidad).
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

        $cantidad = max(1, (int) $traspaso->cantidad);

        try {
            DB::transaction(function () use ($traspaso, $cantidad, $request) {
                if ($traspaso->id_producto) {
                    $this->moverInventario((int) $traspaso->sucursal_a, (int) $traspaso->sucursal_b, (int) $traspaso->id_producto, $cantidad);
                }

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
            'seccion' => 'traspasos',
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
            'seccion' => 'traspasos',
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
     * Mueve N unidades de un producto entre sucursales consumiendo lotes FIFO por caducidad.
     *
     * @throws \RuntimeException Cuando el stock en origen es insuficiente.
     */
    private function moverInventario(int $origenId, int $destinoId, int $productoId, int $cantidad): void
    {
        $filas = Inventario::query()
            ->join('lotes', 'lotes.id', '=', 'inventario.id_lote')
            ->where('inventario.id_sucursal', $origenId)
            ->where('lotes.id_producto', $productoId)
            ->where('inventario.stock', '>', 0)
            ->orderByRaw('COALESCE(lotes.fecha_de_caducidad, lotes.fecha_caducidad) ASC')
            ->select('inventario.*')
            ->lockForUpdate()
            ->get();

        $disponible = (int) $filas->sum('stock');

        if ($disponible < $cantidad) {
            throw new \RuntimeException("Stock insuficiente en origen. Disponible: {$disponible} uds, solicitado: {$cantidad} uds.");
        }

        $restante = $cantidad;

        foreach ($filas as $fila) {
            if ($restante <= 0) {
                break;
            }

            $tomar = min((int) $fila->stock, $restante);
            $fila->decrement('stock', $tomar);

            Inventario::updateOrCreate(
                ['id_sucursal' => $destinoId, 'id_lote' => $fila->id_lote],
                ['stock' => 0]
            )->increment('stock', $tomar);

            $restante -= $tomar;
        }
    }
}
