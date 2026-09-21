<x-layouts::app :title="__('Inventario')">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Catálogo</p>
                    <h1 class="mt-2 text-3xl font-bold">Inventario farmacéutico</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('inventario.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-primary">+ Agregar producto nuevo</a>
                </div>
            </div>

            <nav class="module-tabs" aria-label="Secciones de inventario">
                <a href="{{ route('inventario.index', ['sucursal' => $selectedSucursal?->id]) }}" class="module-tab module-tab-active">Productos y stock</a>
                @if(auth()->user()?->puedeVerModulo('Lotes y caducidades') ?? false)
                    <a href="{{ route('lotes.index', ['sucursal' => $selectedSucursal?->id]) }}" class="module-tab">Lotes y caducidades</a>
                @endif
            </nav>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Productos</span>
                        <span class="stat-pill positive">{{ $productos->count() }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $productos->count() }}</p>
                    <p class="mt-1 text-xs theme-subtle">Disponibles en la vista</p>
                </div>

                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Stock crítico</span>
                        <span class="stat-pill warning">{{ $stockCritico }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $stockCritico }}</p>
                    <p class="mt-1 text-xs theme-subtle">Productos con stock bajo</p>
                </div>

                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Unidades</span>
                        <span class="stat-pill info">Total</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $stockTotal }}</p>
                    <p class="mt-1 text-xs theme-subtle">Existencias actuales</p>
                </div>

                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Valor total</span>
                        <span class="stat-pill positive">MXN</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">${{ number_format($valorTotal, 2) }}</p>
                    <p class="mt-1 text-xs theme-subtle">Inventario visible</p>
                </div>
            </div>

            <div class="module-card p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</h2>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <form method="GET" action="{{ route('inventario.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <input type="search" name="buscar" value="{{ old('buscar', $busqueda) }}" placeholder="Buscar código o nombre" class="theme-input w-full sm:w-64" aria-label="Buscar producto">
                            <button type="submit" class="theme-button theme-button-secondary whitespace-nowrap">Buscar</button>
                        </form>
                    </div>
                </div>

                <div class="module-table mt-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Código</th>
                                    <th class="px-4 py-3 font-semibold">Producto</th>
                                    <th class="px-4 py-3 font-semibold">Descripción</th>
                                    <th class="px-4 py-3 font-semibold">Stock</th>
                                    <th class="px-4 py-3 font-semibold">Precio</th>
                                    <th class="px-4 py-3 font-semibold">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productos as $producto)
                                    <tr class="border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5">
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $producto->codigo_barras }}</td>
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $producto->nombre_producto }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $producto->descripcion ?: 'Sin descripción' }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $producto->stock }} uds.</td>
                                        <td class="px-4 py-3 text-slate-700">${{ number_format($producto->precio, 2) }}</td>
                                        <td class="px-4 py-3">
                                            @if($producto->stock <= 15)
                                                <span class="inventory-status inventory-status-low">Bajo</span>
                                            @endif
                                            @if($producto->stock > 15)
                                                <span class="inventory-status inventory-status-available">Disponible</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                            No se encontraron productos para esta sucursal o búsqueda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
