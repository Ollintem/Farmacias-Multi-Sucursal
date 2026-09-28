<x-layouts::app :title="__('Productos')">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Catálogo</p>
                    <h1 class="mt-2 text-3xl font-bold">Productos</h1>
                </div>
            </div>

            @include('pages.inventario.tabs', ['seccion' => 'productos', 'selectedSucursal' => $selectedSucursal])

            @if(session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
                    role="status"
                >
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
                    role="alert"
                >
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-red-600 text-xs font-bold text-white">!</span>
                    {{ session('error') }}
                </div>
            @endif

            <div class="module-card p-5 sm:p-6" x-data="buscadorTabla()" x-effect="filtrar($el)">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</h2>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <form method="GET" action="{{ route('inventario.productos') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <input type="search" name="buscar" value="{{ $busqueda }}" placeholder="Buscar código o nombre" class="theme-input w-full sm:w-64" aria-label="Buscar producto" @input.debounce.200ms="texto = $event.target.value">
                            <button type="submit" class="theme-button theme-button-secondary whitespace-nowrap">Buscar</button>
                        </form>

                        <a href="{{ route('inventario.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-primary whitespace-nowrap">+ Agregar producto nuevo</a>
                    </div>
                </div>

                <div class="module-table mt-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Nombre</th>
                                    <th class="px-4 py-3 font-semibold">Categoría</th>
                                    <th class="px-4 py-3 font-semibold">Presentación</th>
                                    <th class="px-4 py-3 font-semibold">Precio</th>
                                    <th class="px-4 py-3 font-semibold">Controlado</th>
                                    <th class="px-4 py-3 font-semibold">Estado</th>
                                    <th class="px-4 py-3 font-semibold">Acciones</th>
                                </tr>
                            </thead>
                                @foreach($productos as $producto)
                                    @php
                                        $presentaciones = $producto->presentacionesOrdenadas($presentacionCaja?->id);
                                        $presentacionesLista = $presentaciones
                                            ->reject(fn ($presentacion) => $presentacionCaja !== null && $presentacion->id_presentacion === $presentacionCaja->id)
                                            ->values();
                                        $totalDesplegable = $presentacionesLista->count() + ($producto->precio !== null ? 1 : 0);
                                    @endphp
                                    <tbody x-data="{ abierta: false }" data-buscar="{{ $producto->codigo_barras }} {{ $producto->nombre_producto }}">
                                        <tr @class([
                                            'border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5',
                                            'opacity-60' => ! $producto->es_activo,
                                        ])>
                                            <td class="px-4 py-3">
                                                <span class="block font-medium text-slate-800 dark:text-slate-100">{{ $producto->nombre_producto }}</span>
                                                <span class="block text-xs theme-subtle">{{ $producto->codigo_barras }}</span>
                                            </td>

                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                                {{ $producto->categoria?->nombre ?? 'Sin categoría' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                @if($producto->presentacionCaja && $presentacionCaja)
                                                    <span class="block font-medium text-slate-800 dark:text-slate-100">{{ $presentacionCaja->presentacion }}</span>
                                                    <span class="block text-xs theme-subtle">{{ $producto->presentacionCaja->unidades }} uds. por caja</span>
                                                @else
                                                    <span class="block theme-subtle">Sin presentación Caja</span>
                                                @endif

                                                @if($totalDesplegable > 0)
                                                    <button
                                                        type="button"
                                                        @click="abierta = !abierta"
                                                        :aria-expanded="abierta"
                                                        aria-controls="presentaciones-{{ $producto->id }}"
                                                        class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-slate-500 transition hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400"
                                                    >
                                                        <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="abierta && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                        + {{ $totalDesplegable }} {{ $totalDesplegable === 1 ? 'presentación' : 'presentaciones' }}
                                                    </button>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3">
                                                @if($producto->presentacionCaja && $presentacionCaja)
                                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">${{ number_format((float) $producto->presentacionCaja->precio_presentacion, 2) }}</span>
                                                @else
                                                    <span class="theme-subtle">—</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3">
                                                @if($producto->es_controlado)
                                                    <span class="inline-flex items-center rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">Sí</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-slate-500/10 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:text-slate-400">No</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3">
                                                <form action="{{ route('inventario.estado', $producto) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="es_activo" value="{{ $producto->es_activo ? 0 : 1 }}">
                                                    <button
                                                        type="{{ $producto->es_activo ? 'button' : 'submit' }}"
                                                        @class([
                                                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold transition',
                                                            'bg-emerald-500/15 text-emerald-700 hover:bg-emerald-500/25 dark:text-emerald-300' => $producto->es_activo,
                                                            'bg-slate-500/15 text-slate-600 hover:bg-slate-500/25 dark:text-slate-300' => ! $producto->es_activo,
                                                        ])
                                                        @if($producto->es_activo)
                                                            data-modo="desactivar"
                                                            data-ruta="{{ route('inventario.estado', $producto) }}"
                                                            data-nombre="{{ $producto->nombre_producto }}"
                                                            @click="$dispatch('confirmar-accion', { modo: $event.currentTarget.dataset.modo, ruta: $event.currentTarget.dataset.ruta, nombre: $event.currentTarget.dataset.nombre })"
                                                        @endif
                                                    >
                                                        <span @class(['h-1.5 w-1.5 rounded-full', 'bg-emerald-500' => $producto->es_activo, 'bg-slate-400' => ! $producto->es_activo])></span>
                                                        {{ $producto->es_activo ? 'Activo' : 'Inactivo' }}
                                                    </button>
                                                </form>
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <a href="{{ route('inventario.edit', $producto) }}" title="Editar producto" class="text-slate-500 transition hover:text-emerald-600 dark:text-slate-400">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                        <span class="sr-only">Editar producto</span>
                                                    </a>

                                                    <button
                                                        type="button"
                                                        title="Eliminar producto"
                                                        class="text-slate-500 transition hover:text-red-600 dark:text-slate-400"
                                                        data-modo="eliminar"
                                                        data-ruta="{{ route('inventario.destroy', $producto) }}"
                                                        data-nombre="{{ $producto->nombre_producto }}"
                                                        @click="$dispatch('confirmar-accion', { modo: $event.currentTarget.dataset.modo, ruta: $event.currentTarget.dataset.ruta, nombre: $event.currentTarget.dataset.nombre })"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        <span class="sr-only">Eliminar producto</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        @if($totalDesplegable > 0)
                                            <tr>
                                                <td colspan="7" class="p-0">
                                                    <div
                                                        id="presentaciones-{{ $producto->id }}"
                                                        x-show="abierta"
                                                        x-collapse
                                                        class="border-t border-dashed border-slate-200 bg-slate-50/70 px-4 py-3 dark:border-slate-700 dark:bg-slate-900/40"
                                                    >
                                                        <p class="theme-subtle mb-2 text-xs uppercase tracking-[0.2em]">Presentaciones y precios</p>
                                                        <table class="min-w-full text-xs">
                                                            <thead>
                                                                <tr class="theme-subtle text-left">
                                                                    <th class="py-1 pr-4 font-medium">Presentación</th>
                                                                    <th class="py-1 pr-4 font-medium">Unidades</th>
                                                                    <th class="py-1 font-medium">Precio</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if($producto->precio !== null)
                                                                    <tr class="border-t border-slate-200/70 dark:border-slate-700/60">
                                                                        <td class="py-1.5 pr-4 text-slate-700 dark:text-slate-200">Unitario</td>
                                                                        <td class="py-1.5 pr-4 theme-subtle">1 ud.</td>
                                                                        <td class="py-1.5 font-semibold text-slate-800 dark:text-slate-100">${{ number_format($producto->precio, 2) }}</td>
                                                                    </tr>
                                                                @endif

                                                                @foreach($presentacionesLista as $presentacion)
                                                                    <tr class="border-t border-slate-200/70 dark:border-slate-700/60">
                                                                        <td class="py-1.5 pr-4 text-slate-700 dark:text-slate-200">{{ $presentacion->presentacion?->presentacion ?? '—' }}</td>
                                                                        <td class="py-1.5 pr-4 theme-subtle">{{ $presentacion->unidades }} ud.</td>
                                                                        <td class="py-1.5 font-semibold text-slate-800 dark:text-slate-100">${{ number_format((float) $presentacion->precio_presentacion, 2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                @endforeach
                                <tbody data-vacio @if($productos->isNotEmpty()) style="display: none" @endif>
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                            No se encontraron productos para esta sucursal o búsqueda.
                                        </td>
                                    </tr>
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.confirmacion-accion')
    @include('partials.buscador-cliente')
</x-layouts::app>
