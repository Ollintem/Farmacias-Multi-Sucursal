<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InventarioController extends Controller
{
    /**
     * Redirige la raíz del módulo a la pestaña Productos, que es la pestaña por defecto.
     *
     * Entrada: query string opcional `sucursal`.
     * Salida: redirección a inventario.productos.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('inventario.productos', array_filter([
            'sucursal' => $request->query('sucursal'),
        ]));
    }

    /**
     * Muestra la pestaña Stock filtrada por sucursal y texto de búsqueda.
     *
     * Entrada: query string `sucursal` y `buscar`.
     * Salida: resources/views/pages/inventario/index.blade.php.
     */
    public function stock(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();

        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();

        $busqueda = trim((string) $request->query('buscar', ''));

        $productos = Producto::query()
            ->with('sucursales')
            ->activeSucursal()
            ->where('es_activo', true)
            ->when($busqueda !== '', function ($query, $busqueda) {
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('codigo_barras', 'like', "%{$busqueda}%")
                        ->orWhere('id', $busqueda)
                        ->orWhere('nombre_producto', 'like', "%{$busqueda}%");
                });
            })
            ->orderBy('nombre_producto')
            ->get();

        $stockTotal = (int) $productos->sum('stock');
        $stockCritico = $productos->filter(fn (Producto $producto) => $producto->stock <= 15)->count();
        $valorTotal = $productos->sum(fn (Producto $producto) => (float) $producto->stock * (float) $producto->precio);

        return view('pages.inventario.index', compact(
            'sucursales',
            'selectedSucursal',
            'productos',
            'stockTotal',
            'stockCritico',
            'valorTotal',
            'busqueda',
        ));
    }

    /**
     * Carga los catálogos necesarios para registrar un producto.
     *
     * Entrada: sesión con la sucursal activa.
     * Salida: resources/views/pages/inventario/create.blade.php.
     */
    public function create(): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $sucursales->first()?->id;
        $presentaciones = PresentacionProducto::orderBy('presentacion')->get();
        $categorias = Categoria::orderBy('nombre')->get();
        $presentacionCaja = $this->presentacionCaja($presentaciones);

        return view('pages.inventario.create', compact(
            'sucursales',
            'selectedSucursalId',
            'presentaciones',
            'categorias',
            'presentacionCaja',
        ));
    }

    /**
     * Presentación "caja": fila principal fija del formulario de producto.
     *
     * Entrada: catálogo de presentaciones (opcional).
     * Salida: el registro de caja o null si no existe en el catálogo.
     */
    private function presentacionCaja(?Collection $presentaciones = null): ?PresentacionProducto
    {
        $presentaciones ??= PresentacionProducto::all();

        return $presentaciones->first(
            fn (PresentacionProducto $presentacion) => strtolower($presentacion->presentacion) === 'caja'
        );
    }

    /**
     * Valida, crea el producto con sus presentaciones y lo vincula a la sucursal activa.
     *
     * Entrada: datos del formulario de alta de inventario.
     * Salida: redirección a inventario.productos con mensaje de resultado.
     */
    public function store(Request $request): RedirectResponse
    {
        $presentacionCaja = $this->presentacionCaja();

        if (! $presentacionCaja) {
            return back()
                ->withInput()
                ->withErrors(['presentaciones' => 'No existe la presentación «Caja» en el catálogo. Créala antes de registrar productos.']);
        }

        $data = $request->validate($this->reglasProducto());

        // La primera presentación siempre es Caja: es la que alimenta el
        // control de caja abierta/cerrada, por lo que se fija del lado servidor.
        $data['presentaciones'][0]['id_presentacion'] = $presentacionCaja->id;

        $producto = Producto::create([
            'codigo_barras' => $data['codigo_barras'],
            'nombre_producto' => $data['nombre_producto'],
            'id_categoria' => $data['id_categoria'],
            'descripcion' => $data['descripcion'] ?? '',
            'stock' => 0,
            'precio' => $request->boolean('vender_por_unidad') ? $data['precio'] : null,
            'id_presentacion' => $data['presentaciones'][0]['id_presentacion'],
            'es_controlado' => $request->boolean('es_controlado', false),
            'es_activo' => true,
        ]);

        foreach ($data['presentaciones'] as $presentacion) {
            $producto->presentacionesPrecio()->create([
                'id_presentacion' => $presentacion['id_presentacion'],
                'unidades' => $presentacion['unidades'],
                'precio_presentacion' => $presentacion['precio'],
            ]);
        }

        return redirect()->route('inventario.productos', ['sucursal' => session('active_sucursal_id')])
            ->with('success', 'Producto agregado correctamente al inventario.');
    }

    /**
     * Catálogo de la pestaña "Productos": nombre, categoría, presentación por
     * caja, controlado, estado y acciones.
     *
     * Entrada: query string `sucursal` y `buscar`.
     * Salida: resources/views/pages/inventario/productos.blade.php.
     */
    public function productos(Request $request): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $request->query('sucursal') ?? $sucursales->first()?->id;
        $selectedSucursal = $sucursales->firstWhere('id', $selectedSucursalId) ?? $sucursales->first();
        $busqueda = trim((string) $request->query('buscar', ''));
        $presentacionCaja = $this->presentacionCaja();

        $productos = Producto::query()
            ->with(['categoria', 'presentacionesPrecio.presentacion'])
            ->withSum('inventarios', 'stock')
            ->withCount(['lotes', 'ventas'])
            ->when($busqueda !== '', function ($query, $busqueda) {
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('codigo_barras', 'like', "%{$busqueda}%")
                        ->orWhere('nombre_producto', 'like', "%{$busqueda}%");
                });
            })
            ->orderBy('nombre_producto')
            ->get()
            ->map(function (Producto $producto) use ($presentacionCaja) {
                $conStock = (int) $producto->stock > 0 || (int) $producto->sum_stock > 0;
                $conLotes = (int) $producto->lotes_count > 0;
                $conVentas = (int) $producto->ventas_count > 0;

                $producto->presentacionCaja = $presentacionCaja
                    ? $producto->presentacionesPrecio->firstWhere('id_presentacion', $presentacionCaja->id)
                    : null;
                $producto->puedeDesactivarse = ! $conStock && ! $conLotes;
                $producto->puedeEliminarse = $producto->puedeDesactivarse && ! $conVentas;

                return $producto;
            });

        return view('pages.inventario.productos', compact(
            'selectedSucursal',
            'productos',
            'busqueda',
            'presentacionCaja',
        ));
    }

    /**
     * Carga el formulario de producto en modo edición.
     *
     * Entrada: producto a editar.
     * Salida: resources/views/pages/inventario/create.blade.php con los datos precargados.
     */
    public function edit(Producto $producto): View
    {
        $sucursales = Sucursal::orderBy('nombre_sucursal')->get();
        $selectedSucursalId = session('active_sucursal_id') ?? $sucursales->first()?->id;
        $presentaciones = PresentacionProducto::orderBy('presentacion')->get();
        $categorias = Categoria::orderBy('nombre')->get();
        $presentacionCaja = $this->presentacionCaja($presentaciones);

        return view('pages.inventario.create', compact(
            'sucursales',
            'selectedSucursalId',
            'presentaciones',
            'categorias',
            'presentacionCaja',
            'producto',
        ));
    }

    /**
     * Actualiza los datos del producto y sincroniza sus presentaciones.
     *
     * Entrada: producto y datos del formulario de edición.
     * Salida: redirección al catálogo con mensaje de resultado.
     */
    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $presentacionCaja = $this->presentacionCaja();

        if (! $presentacionCaja) {
            return back()
                ->withInput()
                ->withErrors(['presentaciones' => 'No existe la presentación «Caja» en el catálogo. Créala antes de editar productos.']);
        }

        $data = $request->validate($this->reglasProducto());

        $data['presentaciones'][0]['id_presentacion'] = $presentacionCaja->id;

        $producto->update([
            'codigo_barras' => $data['codigo_barras'],
            'nombre_producto' => $data['nombre_producto'],
            'id_categoria' => $data['id_categoria'],
            'descripcion' => $data['descripcion'] ?? '',
            'precio' => $request->boolean('vender_por_unidad') ? $data['precio'] : null,
            'id_presentacion' => $data['presentaciones'][0]['id_presentacion'],
            'es_controlado' => $request->boolean('es_controlado', false),
        ]);

        foreach ($data['presentaciones'] as $presentacion) {
            $producto->presentacionesPrecio()->updateOrCreate(
                ['id_presentacion' => $presentacion['id_presentacion']],
                ['unidades' => $presentacion['unidades'], 'precio_presentacion' => $presentacion['precio']]
            );
        }

        $producto->presentacionesPrecio()
            ->whereNotIn('id_presentacion', array_column($data['presentaciones'], 'id_presentacion'))
            ->delete();

        return redirect()->route('inventario.productos', ['sucursal' => session('active_sucursal_id')])
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Activa o desactiva un producto.
     *
     * Entrada: producto y nuevo estado (`es_activo`).
     * Salida: misma página con mensaje de éxito o de bloqueo.
     */
    public function cambiarEstado(Request $request, Producto $producto): RedirectResponse
    {
        $activo = $request->boolean('es_activo');

        if (! $activo && $this->tieneStock($producto)) {
            return back()->with('error', "No se puede desactivar «{$producto->nombre_producto}»: tiene stock registrado.");
        }

        if (! $activo && $this->tieneLotes($producto)) {
            return back()->with('error', "No se puede desactivar «{$producto->nombre_producto}»: tiene lotes registrados.");
        }

        $producto->update(['es_activo' => $activo]);

        return back()->with(
            'success',
            $activo
                ? "«{$producto->nombre_producto}» está activo de nuevo."
                : "«{$producto->nombre_producto}» quedó inactivo: ya no aparece en Punto de Venta ni en Productos y stock."
        );
    }

    /**
     * Elimina un producto del catálogo sin tocar su historial.
     *
     * Entrada: producto a eliminar.
     * Salida: misma página con mensaje de éxito o de bloqueo.
     */
    public function destroy(Producto $producto): RedirectResponse
    {
        if ($this->tieneVentas($producto)) {
            return back()->with('error', "No se puede eliminar «{$producto->nombre_producto}»: tiene ventas registradas. El historial de ventas nunca se elimina.");
        }

        if ($this->tieneLotes($producto)) {
            return back()->with('error', "No se puede eliminar «{$producto->nombre_producto}»: tiene lotes registrados.");
        }

        if ($this->tieneStock($producto)) {
            return back()->with('error', "No se puede eliminar «{$producto->nombre_producto}»: tiene stock registrado.");
        }

        $producto->delete();

        return back()->with('success', "«{$producto->nombre_producto}» se eliminó del catálogo.");
    }

    /**
     * Reglas compartidas entre el alta y la edición de productos.
     *
     * Entrada: ninguna.
     * Salida: arreglo de reglas de validación.
     *
     * @return array<string, array<int, mixed>>
     */
    private function reglasProducto(): array
    {
        return [
            'codigo_barras' => ['required', 'string', 'max:255'],
            'nombre_producto' => ['required', 'string', 'max:120'],
            'id_categoria' => ['required', 'exists:categorias,id'],
            'descripcion' => ['nullable', 'string'],
            'vender_por_unidad' => ['nullable', 'boolean'],
            'precio' => ['required_if:vender_por_unidad,1', 'nullable', 'numeric', 'min:0'],
            'es_controlado' => ['boolean'],
            'presentaciones' => ['required', 'array', 'min:1'],
            'presentaciones.*.id_presentacion' => ['required', 'distinct', 'exists:presentaciones,id'],
            'presentaciones.*.unidades' => ['required', 'integer', 'min:2'],
            'presentaciones.*.precio' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Determina si el producto tiene unidades disponibles.
     *
     * Entrada: producto.
     * Salida: true si hay stock global o en alguna sucursal.
     */
    private function tieneStock(Producto $producto): bool
    {
        return (int) $producto->stock > 0
            || (int) Inventario::where('id_producto', $producto->id)->sum('stock') > 0;
    }

    /**
     * Determina si el producto tiene lotes registrados.
     *
     * Entrada: producto.
     * Salida: true si existe al menos un lote.
     */
    private function tieneLotes(Producto $producto): bool
    {
        return Lote::where('id_producto', $producto->id)->exists();
    }

    /**
     * Determina si el producto participa en ventas previas.
     *
     * Entrada: producto.
     * Salida: true si hay historial de ventas.
     */
    private function tieneVentas(Producto $producto): bool
    {
        return $producto->ventas()->exists();
    }
}
