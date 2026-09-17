<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LotesController extends Controller
{
    public function index(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = $request->query('sucursal') ?? $sucursales->first()?->id;
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();
        $busqueda = trim((string) $request->query('buscar', ''));

        $lotes = Lote::with(['productos.sucursales', 'proveedor'])
            ->when($selectedSucursal, function ($query, $sucursal) {
                $query->whereHas('productos.sucursales', function ($subQuery) use ($sucursal) {
                    $subQuery->where('sucursales.id', $sucursal->id);
                });
            })
            ->when($busqueda !== '', function ($query, $busqueda) {
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('folio', 'like', "%{$busqueda}%")
                        ->orWhereHas('proveedor', function ($proveedorQuery) use ($busqueda) {
                            $proveedorQuery->where('nombre_proveedor', 'like', "%{$busqueda}%");
                        })
                        ->orWhereHas('productos', function ($productoQuery) use ($busqueda) {
                            $productoQuery->where('nombre_producto', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->orderBy('fecha_caducidad')
            ->get()
            ->map(function ($lote) use ($selectedSucursal) {
                $producto = $lote->productos->first();
                $sucursal = $producto?->sucursales->first();
                $cantidadTotal = $lote->productos->sum('stock');
                $nombreProducto = $producto?->nombre_producto ?? 'Producto sin nombre';
                $nombreSucursal = $sucursal?->nombre_sucursal ?? $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal';
                $fechaCaducidad = $lote->fecha_caducidad ? Carbon::parse($lote->fecha_caducidad) : null;

                if (! $fechaCaducidad) {
                    $estado = 'Sin fecha';
                    $estadoClass = 'warning';
                } elseif ($fechaCaducidad->isPast()) {
                    $estado = 'Caducado';
                    $estadoClass = 'expired';
                } elseif ($fechaCaducidad->diffInDays(Carbon::now()) <= 30) {
                    $estado = 'Caduca < 30 días';
                    $estadoClass = 'danger';
                } elseif ($fechaCaducidad->diffInDays(Carbon::now()) <= 90) {
                    $estado = 'Caduca < 90 días';
                    $estadoClass = 'warning';
                } else {
                    $estado = 'Vigente';
                    $estadoClass = 'vigente';
                }

                return [
                    'folio' => $lote->folio,
                    'producto' => $nombreProducto,
                    'marca' => $lote->proveedor?->nombre_proveedor ?? 'Sin proveedor',
                    'sucursal' => $nombreSucursal,
                    'cantidad' => $cantidadTotal,
                    'fecha_entrada' => $lote->entregado_en ? Carbon::parse($lote->entregado_en)->format('Y-m-d') : '-',
                    'fecha_caducidad' => $fechaCaducidad ? $fechaCaducidad->format('Y-m-d') : '-',
                    'estado' => $estado,
                    'estado_class' => $estadoClass,
                ];
            })
            ->values();

        $totalLotes = $lotes->count();
        $vigentes = $lotes->filter(fn (array $lote) => $lote['estado_class'] === 'vigente')->count();
        $porCaducar = $lotes->filter(fn (array $lote) => in_array($lote['estado_class'], ['warning', 'danger'], true))->count();
        $caducados = $lotes->filter(fn (array $lote) => $lote['estado_class'] === 'expired')->count();

        return view('pages.lotes.index', compact(
            'lotes',
            'sucursales',
            'selectedSucursal',
            'busqueda',
            'totalLotes',
            'vigentes',
            'porCaducar',
            'caducados',
        ));
    }

    public function create(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $proveedores = Proveedor::orderBy('nombre_proveedor')->get();
        $presentaciones = PresentacionProducto::orderBy('presentacion')->get();
        $selectedSucursalId = $request->query('sucursal') ?? $sucursales->first()?->id;

        return view('pages.lotes.create', compact('sucursales', 'proveedores', 'presentaciones', 'selectedSucursalId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'folio' => ['required', 'string', 'max:50', 'unique:lotes,folio'],
            'id_proveedor' => ['required', 'exists:proveedores,id'],
            'sucursal' => ['required', 'exists:sucursales,id'],
            'entregado_en' => ['required', 'date'],
            'fecha_caducidad' => ['required', 'date', 'after_or_equal:entregado_en'],
            'codigo_barras' => ['required', 'string', 'max:255'],
            'nombre_producto' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:1'],
            'precio' => ['required', 'numeric', 'min:0'],
            'id_presentacion' => ['required', 'exists:presentacion_productos,id'],
            'es_controlado' => ['boolean'],
        ]);

        $lote = Lote::create([
            'folio' => $data['folio'],
            'id_proveedor' => $data['id_proveedor'],
            'entregado_en' => $data['entregado_en'],
            'fecha_caducidad' => $data['fecha_caducidad'],
        ]);

        $producto = Producto::create([
            'codigo_barras' => $data['codigo_barras'],
            'nombre_producto' => $data['nombre_producto'],
            'descripcion' => $data['descripcion'] ?? '',
            'stock' => $data['stock'],
            'precio' => $data['precio'],
            'id_lote' => $lote->id,
            'id_presentacion' => $data['id_presentacion'],
            'es_controlado' => $request->boolean('es_controlado', false),
            'es_activo' => true,
        ]);

        $producto->sucursales()->syncWithoutDetaching([$data['sucursal']]);

        return redirect()->route('lotes.index', ['sucursal' => $data['sucursal']])
            ->with('success', 'Lote registrado correctamente.');
    }
}
