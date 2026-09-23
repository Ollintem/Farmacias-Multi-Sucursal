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
                @if(auth()->user()?->puedeVerModulo('Lotes y caducidades'))
                    <a href="{{ route('lotes.index', ['sucursal' => $selectedSucursal?->id]) }}" class="module-tab">Lotes y caducidades</a>
                @endif
            </nav>

            @if(session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    {{ session('error') }}
                </div>
            @endif

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
                            <label class="flex items-center gap-2 text-sm font-medium">
                                <span class="theme-subtle">Sucursal:</span>
                                <select name="sucursal" class="branch-select" aria-label="Seleccionar sucursal" onchange="this.form.submit()">
                                    @forelse($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ $selectedSucursal && $selectedSucursal->id === $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre_sucursal }}
                                        </option>
                                    @empty
                                        <option value="">Sin sucursales registradas</option>
                                    @endforelse
                                </select>
                            </label>
                            <input type="search" name="buscar" value="{{ $busqueda }}" placeholder="Buscar código o nombre" class="theme-input w-full sm:w-64" aria-label="Buscar producto">
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
                                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $producto->codigo_barras }}</td>
                                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $producto->nombre_producto }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $producto->descripcion ?: 'Sin descripción' }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $producto->stock }} uds.</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">${{ number_format($producto->precio, 2) }}</td>
                                        <td class="px-4 py-3">
                                            @if($producto->stock <= 15)
                                                <span class="inventory-status inventory-status-low">Bajo</span>
                                            @else
                                                <span class="inventory-status inventory-status-available">Disponible</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
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
