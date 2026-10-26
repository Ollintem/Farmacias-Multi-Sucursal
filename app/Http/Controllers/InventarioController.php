<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\PresentacionProducto;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
     * Muestra la pestaña Stock: los productos que tienen inventario real en
     * la sucursal seleccionada, con su existencia en unidades base.
     *
     * El stock por sucursal vive en `inventario` y llega al producto vía lote,
     * por eso se agrega con Inventario::stockPorProducto() en lugar de usar el
     * stock global de `productos`. Solo se listan los productos con filas de
     * inventario en la sucursal: el catálogo puede contener productos sin
     * existencias y esos no pertenecen a esta pestaña.
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

        $stockPorProducto = $selectedSucursal
            ? Inventario::stockPorProducto((int) $selectedSucursal->id)
            : [];

        $lotesPorProducto = $selectedSucursal
            ? Inventario::lotesPorProducto((int) $selectedSucursal->id)
            : [];

        $productos = Producto::query()
            ->where('productos.es_activo', true)
            ->with('presentacionesPrecio.presentacion')
            ->when($selectedSucursal !== null, fn (Builder $query) => $query->whereHas(
                'inventarios',
                fn (Builder $inventarioQuery) => $inventarioQuery->where('inventario.id_sucursal', $selectedSucursal->id)
            ))
            ->when($busqueda !== '', fn (Builder $query) => $this->aplicarBusquedaProducto($query, $busqueda))
            ->orderBy('productos.nombre_producto')
            ->get()
            ->each(function (Producto $producto) use ($stockPorProducto, $lotesPorProducto) {
                $stock = (int) ($stockPorProducto[$producto->id] ?? 0);
                $lotes = $lotesPorProducto[$producto->id] ?? ['total' => 0, 'con_existencia' => 0];
                $cajas = $producto->estadoDeCajas($stock);

                $producto->setAttribute('stock_sucursal', $stock);
                $producto->setAttribute('lotes_sucursal', $lotes['con_existencia']);
                $producto->setAttribute('cajas_cerradas', $cajas['selladas'] ?? null);
                $producto->setAttribute('caja_abierta', $cajas !== null && $cajas['sueltas'] > 0
                    ? $cajas['sueltas'].' uds.'
                    : null);
                $producto->setAttribute('unidades_por_caja', $cajas['unidades_por_caja'] ?? null);
            });

        $stockTotal = (int) $productos->sum('stock_sucursal');
        $stockCritico = $productos
            ->filter(fn (Producto $producto) => (int) $producto->stock_sucursal <= 15)
            ->count();
        $valorTotal = $productos->sum(function (Producto $producto): float {
            $precio = $producto->precioUnidad()
                ?? (float) ($producto->presentacionPrincipalDe()?->precio_presentacion ?? 0);

            return (float) $producto->stock_sucursal * $precio;
        });

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
     * Filtro de búsqueda compartido por las pestañas Stock y Productos.
     *
     * Busca por código de barras o nombre (los campos que promete el
     * placeholder del formulario y que el filtrado en tiempo real usa en
     * `data-buscar`), con columnas calificadas para evitar ambigüedades.
     *
     * Entrada: query Eloquent de productos y texto ya recortado (no vacío).
     * Salida: ninguna, modifica el query recibido.
     *
     * @param  Builder<Producto>  $query
     */
    private function aplicarBusquedaProducto(Builder $query, string $busqueda): void
    {
        $query->where(function (Builder $subQuery) use ($busqueda) {
            $subQuery->where('productos.codigo_barras', 'like', "%{$busqueda}%")
                ->orWhere('productos.nombre_producto', 'like', "%{$busqueda}%");
        });
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
     *
     * @param  Collection<int, PresentacionProducto>|null  $presentaciones
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
        $data = $request->validate($this->reglasProducto());

        $presentaciones = array_values($data['presentaciones']);

        try {
            $producto = DB::transaction(function () use ($request, $data, $presentaciones): Producto {
                $producto = Producto::create([
                    'codigo_barras' => $data['codigo_barras'],
                    'nombre_producto' => $data['nombre_producto'],
                    'id_categoria' => $data['id_categoria'],
                    'descripcion' => $data['descripcion'] ?? '',
                    'es_controlado' => $request->boolean('es_controlado', false),
                    'es_activo' => true,
                ]);

                $this->sincronizarPresentaciones($producto, $presentaciones);

                // El alta es en la sucursal activa: se asocia al pivot sin crear
                // filas de `inventario` (esas nacen con el primer lote).
                $sucursalId = $this->sucursalAlta($request);

                if ($sucursalId !== null) {
                    $producto->sucursales()->syncWithoutDetaching([$sucursalId]);
                }

                return $producto;
            });
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['presentaciones' => 'No se pudo guardar el producto: '.$e->getMessage()]);
        }

        return redirect()->route('inventario.productos', ['sucursal' => session('active_sucursal_id')])
            ->with('success', 'Producto agregado correctamente al inventario.');
    }

    /**
     * Sucursal donde se da de alta el producto: la activa del sidebar, con el
     * query string como respaldo y la primera sucursal como último recurso.
     *
     * Entrada: petición del alta de producto.
     * Salida: id de la sucursal vigente o null si no hay ninguna válida.
     */
    private function sucursalAlta(Request $request): ?int
    {
        $candidata = $request->session()->get('active_sucursal_id')
            ?? $request->query('sucursal')
            ?? Sucursal::query()->orderBy('id')->value('id');

        if (! is_numeric($candidata)) {
            return null;
        }

        $sucursalId = (int) $candidata;

        return Sucursal::query()->whereKey($sucursalId)->exists() ? $sucursalId : null;
    }

    /**
     * Catálogo de la pestaña "Productos": nombre, categoría, presentación por
     * caja, controlado, estado y acciones.
     *
     * Solo se listan los productos dados de alta en la sucursal activa (pivot
     * `producto_sucursal`): cada sucursal tiene su propia tabla Productos.
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

        $productos = Producto::query()
            ->with(['categoria', 'presentacionesPrecio.presentacion'])
            ->withCount(['lotes', 'ventas'])
            ->when($selectedSucursal !== null, fn (Builder $query) => $query->whereHas(
                'sucursales',
                fn (Builder $sucursalQuery) => $sucursalQuery->whereKey($selectedSucursal->id)
            ))
            ->when($busqueda !== '', fn (Builder $query) => $this->aplicarBusquedaProducto($query, $busqueda))
            ->orderBy('productos.nombre_producto')
            ->get();

        // El stock global vive en `inventario` (por lote): se agrega en una
        // sola consulta para saber qué productos pueden darse de baja o
        // eliminarse. La existencia por sucursal se muestra en la pestaña Stock.
        $stockInventario = Inventario::stockPorProducto();

        $productos = $productos
            ->map(function (Producto $producto) use ($stockInventario) {
                $conStock = (int) ($stockInventario[$producto->id] ?? 0) > 0;
                $conLotes = (int) $producto->lotes_count > 0;
                $conVentas = (int) $producto->ventas_count > 0;

                $producto->presentacionPrincipal = $producto->presentacionPrincipalDe();
                $producto->precioUnidad = $producto->precioUnidad();
                $producto->puedeDesactivarse = ! $conStock && ! $conLotes;
                $producto->puedeEliminarse = $producto->puedeDesactivarse && ! $conVentas;

                return $producto;
            });

        return view('pages.inventario.productos', compact(
            'sucursales',
            'selectedSucursal',
            'productos',
            'busqueda',
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
        $data = $request->validate($this->reglasProducto());

        $presentaciones = array_values($data['presentaciones']);

        try {
            DB::transaction(function () use ($request, $producto, $data, $presentaciones): void {
                $producto->update([
                    'codigo_barras' => $data['codigo_barras'],
                    'nombre_producto' => $data['nombre_producto'],
                    'id_categoria' => $data['id_categoria'],
                    'descripcion' => $data['descripcion'] ?? '',
                    'es_controlado' => $request->boolean('es_controlado', false),
                ]);

                $this->sincronizarPresentaciones($producto, $presentaciones);
            });
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['presentaciones' => 'No se pudo actualizar el producto: '.$e->getMessage()]);
        }

        return redirect()->route('inventario.productos', ['sucursal' => session('active_sucursal_id')])
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Guarda las presentaciones del producto y garantiza una única principal.
     *
     * La principal no se toma del formulario: en el alta es «caja», en la
     * edición se conserva la actual y, al quitar las presentaciones, «unidad»
     * queda como principal. Así una petición manipulada no puede cambiar la
     * presentación principal. La presentación «unidad» siempre vale 1. Las
     * presentaciones que ya no vienen en el formulario se eliminan.
     *
     * Entrada: producto y filas validadas del formulario.
     * Salida: ninguna (escribe `presentacion_producto`).
     */
    private function sincronizarPresentaciones(Producto $producto, array $presentaciones): void
    {
        $catalogo = PresentacionProducto::query()
            ->whereIn('id', array_column($presentaciones, 'id_presentacion'))
            ->get()
            ->keyBy('id');

        $indicePrincipal = $this->indicePresentacionPrincipal($presentaciones, $catalogo, $producto);
        $ids = [];

        foreach ($presentaciones as $indice => $fila) {
            $idPresentacion = (int) $fila['id_presentacion'];
            $esUnidad = mb_strtolower((string) ($catalogo[$idPresentacion]->presentacion ?? '')) === 'unidad';
            $unidades = $esUnidad ? 1 : max(1, (int) $fila['unidades']);

            $producto->presentacionesPrecio()->updateOrCreate(
                ['id_presentacion' => $idPresentacion],
                [
                    'unidades' => $unidades,
                    'precio_presentacion' => $fila['precio'],
                    'stock_minimo' => $indice === $indicePrincipal
                        ? max(0, (int) ($fila['stock_minimo'] ?? 0))
                        : 0,
                    'es_principal' => $indice === $indicePrincipal,
                ]
            );

            $ids[] = $idPresentacion;
        }

        $producto->presentacionesPrecio()
            ->whereNotIn('id_presentacion', $ids)
            ->delete();
    }

    /**
     * Índice de la fila que será la presentación principal.
     *
     * No confía en `es_principal` del formulario. En una edición conserva la
     * principal actual si sigue presente; en un alta nueva usa «caja». Al
     * quitar presentaciones (solo se envía «unidad») esa pasa a ser la
     * principal. En último caso se usa la primera fila.
     *
     * Entrada: filas validadas, catálogo indexado por id y producto en edición.
     * Salida: índice de la fila principal.
     */
    private function indicePresentacionPrincipal(array $presentaciones, Collection $catalogo, ?Producto $producto = null): int
    {
        $indiceCaja = null;
        $indiceUnidad = null;

        foreach ($presentaciones as $indice => $fila) {
            $nombre = mb_strtolower((string) ($catalogo[(int) $fila['id_presentacion']]->presentacion ?? ''));

            if ($nombre === 'caja' && $indiceCaja === null) {
                $indiceCaja = $indice;
            }

            if ($nombre === 'unidad' && $indiceUnidad === null) {
                $indiceUnidad = $indice;
            }
        }

        if ($producto !== null) {
            $idPrincipalActual = $producto->presentacionesPrecio()
                ->where('es_principal', true)
                ->value('id_presentacion');

            if ($idPrincipalActual !== null) {
                foreach ($presentaciones as $indice => $fila) {
                    if ((int) $fila['id_presentacion'] === (int) $idPrincipalActual) {
                        return $indice;
                    }
                }
            }
        }

        if ($indiceCaja !== null) {
            return $indiceCaja;
        }

        if ($indiceUnidad !== null && count($presentaciones) === 1) {
            return $indiceUnidad;
        }

        return 0;
    }

    /**
     * Activa o desactiva un producto.
     *
     * Desactivar es la alternativa al borrado: el producto desaparece de
     * Punto de Venta y de Productos y stock conservando lotes, inventario
     * y ventas; se reactiva desde el mismo botón.
     *
     * Entrada: producto y nuevo estado (`es_activo`).
     * Salida: misma página con mensaje de éxito.
     */
    public function cambiarEstado(Request $request, Producto $producto): RedirectResponse
    {
        $activo = $request->boolean('es_activo');

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
            return back()->with('error', "No se puede eliminar «{$producto->nombre_producto}»: tiene lotes registrados. Desactívalo para ocultarlo del catálogo sin perder el historial.");
        }

        if ($this->tieneStock($producto)) {
            return back()->with('error', "No se puede eliminar «{$producto->nombre_producto}»: tiene stock registrado. Desactívalo para ocultarlo del catálogo.");
        }

        $producto->delete();

        return back()->with('success', "«{$producto->nombre_producto}» se eliminó del catálogo.");
    }

    /**
     * Reglas compartidas entre el alta y la edición de productos.
     *
     * Todo producto necesita al menos una presentación, por lo que las
     * presentaciones son obligatorias. La principal se calcula en el servidor
     * («caja» o, al quitar presentaciones, «unidad»), no se confía en
     * `es_principal`; la «unidad» se admite con 1 unidad, el resto exige al
     * menos 2.
     *
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
            'es_controlado' => ['boolean'],
            'presentaciones' => ['required', 'array', 'min:1'],
            'presentaciones.*.id_presentacion' => [
                'required',
                'distinct',
                Rule::exists('presentaciones', 'id')->where(fn ($query) => $query->where('es_activo', true)),
            ],
            'presentaciones.*.unidades' => ['required', 'integer', 'min:1'],
            'presentaciones.*.precio' => ['required', 'numeric', 'min:0'],
            'presentaciones.*.stock_minimo' => ['nullable', 'integer', 'min:0'],
            'presentaciones.*.es_principal' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Determina si el producto tiene unidades disponibles.
     *
     * Entrada: producto.
     * Salida: true si hay stock en el inventario de alguna sucursal
     * (el inventario se vincula al producto vía lote).
     */
    private function tieneStock(Producto $producto): bool
    {
        return (int) $producto->inventarios()->sum('inventario.stock_individual') > 0;
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
