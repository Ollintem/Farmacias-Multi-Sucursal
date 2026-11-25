<x-layouts::app :title="__('Productos')">
    <div class="module-page pos-page noti-page relative isolate !overflow-hidden scrollbar-hide">
        {{-- Fondo agua POS — idéntico a Punto de venta (livewire/punto-de-venta) --}}
        <div class="pos-water" aria-hidden="true">
            <div class="pos-water-gradient"></div>
            <div class="pos-water-highlight"></div>
            <div class="pos-water-layer pos-water-layer--back">
                <div class="pos-wave-track pos-wave-track--slow">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,60 C150,85 350,35 600,60 C850,85 1050,35 1200,60 L1200,120 L0,120 Z" fill="rgba(6,182,212,0.20)"/></svg>
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,60 C150,85 350,35 600,60 C850,85 1050,35 1200,60 L1200,120 L0,120 Z" fill="rgba(6,182,212,0.20)"/></svg>
                </div>
            </div>
            <div class="pos-water-layer pos-water-layer--mid">
                <div class="pos-wave-track pos-wave-track--mid">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,55 C180,90 380,30 600,58 C820,86 1000,28 1200,55 L1200,120 L0,120 Z" fill="rgba(14,147,132,0.18)"/></svg>
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,55 C180,90 380,30 600,58 C820,86 1000,28 1200,55 L1200,120 L0,120 Z" fill="rgba(14,147,132,0.18)"/></svg>
                </div>
            </div>
            <div class="pos-water-layer">
                <div class="pos-wave-track pos-wave-track--fast">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,65 C160,95 340,25 600,65 C860,105 1020,20 1200,65 L1200,120 L0,120 Z" fill="rgba(34,211,238,0.24)"/></svg>
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,65 C160,95 340,25 600,65 C860,105 1020,20 1200,65 L1200,120 L0,120 Z" fill="rgba(34,211,238,0.24)"/></svg>
                </div>
            </div>
        </div>
        <div class="module-page-inner noti-full noti-formal relative z-10">
            <div class="lg:hidden">
                @include('pages.inventario.components.tabs', ['seccion' => 'productos', 'selectedSucursal' => $selectedSucursal])
            </div>

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

            <section class="module-card pos-glass-highlight overflow-hidden !bg-white/70 backdrop-blur-xl supports-[backdrop-filter]:!bg-white/60 !border-white/50 shadow-xl shadow-teal-900/5 dark:!bg-slate-800/60 dark:!border-white/10 flex flex-col flex-1 min-h-[520px] lg:min-h-[640px]" aria-label="Catálogo de productos" x-data="buscadorTabla()" x-effect="filtrar($el)">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Catálogo de productos</h2>
                        <p class="theme-subtle text-sm">{{ $productos->count() }} producto(s) · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    </div>
                </header>

                <div class="flex flex-col gap-3 border-b border-white/50 bg-white/50 backdrop-blur-sm supports-[backdrop-filter]:bg-white/40 px-4 py-3 dark:border-white/10 dark:bg-slate-800/40 sm:flex-row sm:items-center sm:justify-between lg:hidden">
                    <form method="GET" action="{{ route('inventario.productos') }}" class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2">
                            <input id="inventario-buscar" type="search" name="buscar" value="{{ $busqueda }}" placeholder="Buscar código o nombre…" class="theme-input w-full sm:w-72" aria-label="Buscar producto" @input.debounce.200ms="texto = $event.target.value">
                            <button type="submit" class="theme-button theme-button-secondary shrink-0">Buscar</button>
                        </div>
                    </form>
                </div>

                <div class="module-table">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[920px] text-left">
                            <thead class="bg-white/60 backdrop-blur-sm supports-[backdrop-filter]:bg-white/50 dark:bg-slate-800/40">
                                <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                    <th class="px-5 py-3.5 w-[28%]">Producto</th>
                                    <th class="px-4 py-3.5 w-[14%]">Categoría</th>
                                    <th class="px-4 py-3.5 w-[20%]">Presentación</th>
                                    <th class="px-4 py-3.5 w-[12%] text-right">Precio</th>
                                    <th class="px-4 py-3.5 w-[9%] text-center">Controlado</th>
                                    <th class="px-4 py-3.5 w-[9%] text-center">Estado</th>
                                    <th class="px-4 py-3.5 w-[8%] text-right">Acciones</th>
                                </tr>
                            </thead>
                                @foreach($productos as $producto)
                                        @php
                                            $principal = $producto->presentacionPrincipal;
                                            $presentacionesLista = $producto->presentacionesPrecio
                                                ->reject(fn ($presentacion) => $principal !== null && $presentacion->id === $principal->id)
                                                ->sortBy(fn ($presentacion) => mb_strtolower($presentacion->presentacion?->presentacion ?? ''))
                                                ->values();
                                            $totalDesplegable = $presentacionesLista->count();
                                            $mostrarDesplegable = $totalDesplegable > 0;
                                        @endphp
                                    <tbody x-data="{ abierta: false }" data-buscar="{{ $producto->codigo_barras }} {{ $producto->nombre_producto }}" class="pos-card-animate group/tbody divide-y divide-slate-100 dark:divide-slate-800" style="animation-delay: {{ $loop->index * 35 }}ms">
                                        <tr @class([
                                            'transition hover:bg-white/60 backdrop-blur-sm dark:hover:bg-slate-700/30',
                                            'opacity-60' => ! $producto->es_activo,
                                        ])>
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-3">
                                                    <span class="hidden h-9 w-9 shrink-0 place-items-center rounded-xl bg-slate-100 text-xs font-bold text-slate-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700 sm:grid">{{ mb_strtoupper(mb_substr($producto->nombre_producto, 0, 2)) }}</span>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-bold leading-tight text-slate-800 dark:text-slate-100">{{ $producto->nombre_producto }}</p>
                                                        <p class="flex items-center gap-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                                            <svg class="h-3 w-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                            {{ $producto->codigo_barras }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-4 py-4">
                                                <span class="stat-pill info !px-2.5 !py-0.5 !text-[11px] !normal-case !font-bold">{{ $producto->categoria?->nombre ?? 'Sin categoría' }}</span>
                                            </td>

                                            <td class="px-4 py-4">
                                                @if($principal)
                                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $principal->presentacion?->presentacion ?? 'Presentación' }}</p>
                                                    <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">
                                                        {{ $principal->unidades >= 2 ? $principal->unidades.' uds. por presentación' : 'Se vende por unidad' }}
                                                    </p>
                                                @else
                                                    <span class="text-sm font-medium text-slate-400">Sin presentación</span>
                                                @endif

                                                @if($mostrarDesplegable)
                                                    <button
                                                        type="button"
                                                        @click="abierta = !abierta"
                                                        :aria-expanded="abierta.toString()"
                                                        aria-controls="presentaciones-{{ $producto->id }}"
                                                        class="mt-2 inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-600 shadow-sm transition hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0c7569] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-emerald-500/40"
                                                    >
                                                        <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="abierta && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                        + {{ $totalDesplegable }} {{ $totalDesplegable === 1 ? 'presentación' : 'presentaciones' }}
                                                    </button>
                                                @endif
                                            </td>

                                            <td class="px-4 py-4 text-right">
                                                @if($principal)
                                                    <span class="text-sm font-extrabold text-emerald-700 dark:text-emerald-300">${{ number_format((float) $principal->precio_presentacion, 2) }}</span>
                                                @else
                                                    <span class="text-xs font-medium text-slate-400">—</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-4 text-center">
                                                @if($producto->es_controlado)
                                                    <span class="stat-pill warning !px-2.5 !py-1 !text-[11px] !font-bold">Sí · receta</span>
                                                @else
                                                    <span class="stat-pill info !px-2.5 !py-1 !text-[11px] !font-semibold">No</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-4 text-center">
                                                <form action="{{ route('inventario.estado', $producto) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="es_activo" value="{{ $producto->es_activo ? 0 : 1 }}">
                                                    <button
                                                        type="{{ $producto->es_activo ? 'button' : 'submit' }}"
                                                        @class([
                                                            'inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[11px] font-bold transition',
                                                            'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300' => $producto->es_activo,
                                                            'border-slate-200 bg-slate-100 text-slate-600 hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300' => ! $producto->es_activo,
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

                                            <td class="px-4 py-4 text-right">
                                                <div class="inline-flex items-center gap-1">
                                                    <a href="{{ route('inventario.edit', $producto) }}" title="Editar producto" class="grid h-7 w-7 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0c7569] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                        <span class="sr-only">Editar producto</span>
                                                    </a>

                                                    <button
                                                        type="button"
                                                        title="Eliminar producto"
                                                        class="grid h-7 w-7 place-items-center rounded-lg border border-red-200 bg-red-50 text-red-600 shadow-sm transition hover:bg-red-100 hover:text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"
                                                        data-modo="eliminar"
                                                        data-ruta="{{ route('inventario.destroy', $producto) }}"
                                                        data-nombre="{{ $producto->nombre_producto }}"
                                                        @click="$dispatch('confirmar-accion', { modo: $event.currentTarget.dataset.modo, ruta: $event.currentTarget.dataset.ruta, nombre: $event.currentTarget.dataset.nombre })"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        <span class="sr-only">Eliminar producto</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        @if($mostrarDesplegable)
                                            <tr>
                                                <td colspan="7" class="p-0">
                                                    <div
                                                        id="presentaciones-{{ $producto->id }}"
                                                        x-show="abierta"
                                                        x-collapse
                                                        x-transition
                                                        x-cloak
                                                        class="border-t border-slate-200 bg-slate-50/50 px-5 py-4 dark:border-slate-700 dark:bg-slate-900/30"
                                                    >
                                                        <p class="mb-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Presentaciones y precios</p>
                                                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                                                            <table class="min-w-full text-xs">
                                                                <thead>
                                                                    <tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                                                                        <th class="px-4 py-2.5">Presentación</th>
                                                                        <th class="px-4 py-2.5 text-right">Unidades</th>
                                                                        <th class="px-4 py-2.5 text-right">Precio</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                                                    @foreach($presentacionesLista as $presentacion)
                                                                        <tr>
                                                                            <td class="px-4 py-2.5 font-semibold text-slate-700 dark:text-slate-200">{{ $presentacion->presentacion?->presentacion ?? '—' }}</td>
                                                                            <td class="px-4 py-2.5 text-right font-medium text-slate-600 dark:text-slate-300">{{ $presentacion->unidades }} ud.</td>
                                                                            <td class="px-4 py-2.5 text-right font-extrabold text-slate-800 dark:text-slate-100">${{ number_format((float) $presentacion->precio_presentacion, 2) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                @endforeach
                                <tbody data-vacio @if($productos->isNotEmpty()) style="display: none" @endif>
                                    <tr>
                                        <td colspan="7" class="px-4 py-14 text-center">
                                            <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                                                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m0 10V11m0 10l-8-4V7m8 4L4 7"/></svg>
                                                </span>
                                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">No se encontraron productos</p>
                                                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">Prueba con otro código o nombre, o registra un nuevo producto para esta sucursal.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        (() => {
            const sync = () => {
                const topbar = document.getElementById('topbar-inventario-buscar');
                const card = document.getElementById('inventario-buscar');
                if (!topbar || !card) return;
                if (topbar.dataset.bound === '1') return;
                topbar.dataset.bound = '1';
                topbar.addEventListener('input', () => {
                    card.value = topbar.value;
                    card.dispatchEvent(new Event('input', { bubbles: true }));
                });
                // Sincroniza valor inicial desde query
                if (card.value && !topbar.value) topbar.value = card.value;
            };
            document.addEventListener('DOMContentLoaded', sync);
            document.addEventListener('livewire:navigated', sync);
            if (document.readyState !== 'loading') sync();
        })();
    </script>

    @include('partials.confirmacion-accion')
    @include('partials.buscador-cliente')
</x-layouts::app>
