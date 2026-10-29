<div
    class="module-page pos-page noti-page"
    x-data="{ successFolio: null, avisoCaducado: 0, avisoError: '' }"
    @venta-completada.window="successFolio = $event.detail.folio; setTimeout(() => successFolio = null, 4000)"
    @stock-caducado-ignorado.window="avisoCaducado = $event.detail.unidades; setTimeout(() => avisoCaducado = 0, 4000)"
    @pos-error.window="avisoError = $event.detail.mensaje; setTimeout(() => avisoError = '', 5000)"
>
    {{-- Toast de éxito --}}
    <template x-if="successFolio">
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed right-4 top-4 z-[100] rounded-2xl border border-emerald-300 bg-emerald-50 px-5 py-3 shadow-xl dark:border-emerald-700 dark:bg-emerald-900/90"
        >
            <div class="flex items-center gap-2">
                <span class="grid h-7 w-7 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">Venta registrada: <span x-text="successFolio"></span></span>
            </div>
        </div>
    </template>

    {{-- Toast de lotes caducados ignorados --}}
    <template x-if="avisoCaducado > 0">
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed right-4 top-20 z-[100] rounded-2xl border border-amber-300 bg-amber-50 px-5 py-3 shadow-xl dark:border-amber-700 dark:bg-amber-900/90"
        >
            <div class="flex items-center gap-2">
                <span class="grid h-7 w-7 place-items-center rounded-full bg-amber-500 text-xs font-bold text-white">!</span>
                <span class="text-sm font-semibold text-amber-800 dark:text-amber-200">
                    Se vendió con lotes vigentes; se ignoraron <span x-text="avisoCaducado"></span> uds. de lote(s) caducado(s).
                </span>
            </div>
        </div>
    </template>

    {{-- Toast de error al agregar al carrito (sin stock o cantidad máxima) --}}
    <template x-if="avisoError">
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed right-4 top-36 z-[100] rounded-2xl border border-red-300 bg-red-50 px-5 py-3 shadow-xl dark:border-red-700 dark:bg-red-900/90"
        >
            <div class="flex items-center gap-2">
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-red-600 text-xs font-bold text-white">✕</span>
                <span class="text-sm font-semibold text-red-800 dark:text-red-200" x-text="avisoError"></span>
            </div>
        </div>
    </template>

    <div class="module-page-inner noti-full noti-formal">
    {{-- Encabezado --}}
    <header class="mb-4 flex flex-wrap items-start justify-between gap-4" aria-label="Encabezado de punto de venta">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Venta rápida · {{ (\App\Models\Sucursal::find(session('active_sucursal_id') ?? auth()->user()?->id_sucursal))?->nombre_sucursal ?? 'Sin sucursal' }}</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Punto de venta</h1>
            <p class="theme-subtle mt-1 text-sm hidden sm:block">Catálogo por sucursal · {{ $productos->count() }} producto(s) disponible(s)</p>
        </div>
        <span class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 sm:inline-flex">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            {{ $productos->count() }} en catálogo
        </span>
    </header>

    {{-- Barra superior: búsqueda + presentaciones — en escritorio vive en el topbar, aquí solo móvil --}}
    <div class="module-card !p-0 overflow-hidden border border-slate-200 dark:border-zinc-700 shadow-sm lg:hidden">
        <div class="flex flex-col">
            {{-- Fila búsqueda --}}
            <div class="flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="relative flex-1 max-w-2xl">
                    <span class="pointer-events-none absolute left-3.5 top-1/2 flex h-5 w-5 -translate-y-1/2 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="busqueda"
                        placeholder="Buscar producto o código de barras..."
                        class="w-full rounded-full border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm font-medium leading-none text-slate-700 placeholder:text-slate-400 shadow-sm focus:border-[#0e9384] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0e9384]/15 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500"
                        aria-label="Buscar producto"
                    >
                </div>
                <span class="hidden shrink-0 items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 ring-1 ring-slate-200 dark:bg-zinc-700 dark:text-zinc-300 dark:ring-zinc-600 lg:inline-flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Filtra al instante
                </span>
            </div>
            {{-- Fila presentaciones --}}
            <div class="flex items-center gap-3 bg-slate-50/50 px-4 py-3 dark:bg-zinc-800/50">
                <span class="hidden shrink-0 items-center gap-1.5 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400 sm:inline-flex">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h12m0 0l-4-4m4 4l-4 4M20 13H4m0 0l4-4m-4 4l4 4"/></svg>
                    Presentación
                </span>
                <div class="flex flex-1 gap-2 overflow-x-auto scrollbar-hide py-0.5 pr-1">
                    <button
                        wire:click="$set('filtroPresentacion', '')"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-full border-[1.5px] px-4 py-1.5 text-xs font-bold transition
                            {{ $filtroPresentacion === '' ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/20 dark:border-[#6ee7b7] dark:from-[#34d399] dark:to-[#0e9384] dark:text-[#052e2b]' : 'border-slate-200 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-[#0c9f9c] dark:hover:bg-emerald-500/15 dark:hover:text-emerald-100' }}"
                    >
                        <span class="h-1.5 w-1.5 rounded-full {{ $filtroPresentacion === '' ? 'bg-white' : 'bg-slate-300 dark:bg-zinc-500' }}"></span>
                        Todos
                    </button>
                    @foreach($presentaciones as $presentacion)
                        <button
                            wire:click="$set('filtroPresentacion', '{{ $presentacion->id }}')"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-full border-[1.5px] px-4 py-1.5 text-xs font-bold transition
                                {{ $filtroPresentacion == $presentacion->id ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/20 dark:border-[#6ee7b7] dark:from-[#34d399] dark:to-[#0e9384] dark:text-[#052e2b]' : 'border-slate-200 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-[#0c9f9c] dark:hover:bg-emerald-500/15 dark:hover:text-emerald-100' }}"
                        >
                            <span class="h-1.5 w-1.5 rounded-full {{ $filtroPresentacion == $presentacion->id ? 'bg-white' : 'bg-slate-300 dark:bg-zinc-500' }}"></span>
                            {{ ucfirst($presentacion->presentacion) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Área de productos + carrito --}}
    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem] items-start">
        {{-- Grid de productos --}}
        <div class="module-card min-w-0 !p-0 overflow-hidden flex flex-col max-h-[68vh] shadow-sm">
            <div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-slate-50/60 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/60">
                <h2 class="text-sm font-bold tracking-tight text-slate-700 dark:text-white">Catálogo ordenado</h2>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm ring-1 ring-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $productos->count() }} {{ Str::plural('producto', $productos->count()) }}
                </span>
            </div>
            <div class="flex-1 overflow-y-auto overscroll-contain scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-zinc-700">
            @if($productos->isEmpty())
                <div class="flex flex-col items-center justify-center px-4 py-16 text-center">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-2xl text-slate-400 dark:bg-zinc-700 dark:text-zinc-500">Rx</span>
                    <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-zinc-300">No se encontraron productos</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-zinc-500">Prueba con otro nombre o código de barras</p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-4 p-4 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 min-[1700px]:grid-cols-5">
                    @foreach($productos as $producto)
                        <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:border-[#0e9384] hover:shadow-lg hover:shadow-teal-900/10 dark:border-zinc-700 dark:bg-zinc-800/80 dark:hover:border-[#0c9f9c]">
                            @if($producto->es_controlado)
                                <span class="absolute left-2 top-2 z-10 rounded-lg bg-red-600 px-2 py-0.5 text-[10px] font-extrabold tracking-wide text-white shadow">RX</span>
                            @endif
                            @if($producto->solo_caducado)
                                <span class="absolute right-2 top-2 z-10 rounded-lg bg-red-600 px-2 py-0.5 text-[10px] font-extrabold tracking-wide text-white shadow">Caducado</span>
                            @elseif($producto->stock_sucursal > 0 && $producto->stock_sucursal <= 15)
                                <span class="absolute right-2 top-2 z-10 rounded-lg bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold text-amber-800 dark:bg-amber-500/15 dark:text-amber-300">¡Últimas!</span>
                            @endif

                            <div class="flex h-20 items-center justify-center bg-slate-50 dark:bg-zinc-800/60 border-b border-slate-100 dark:border-zinc-700/60">
                                <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-sm font-black text-[#0c7569] shadow-sm ring-1 ring-slate-200 dark:bg-zinc-700 dark:text-emerald-300 dark:ring-zinc-600 transition group-hover:scale-105">Rx</span>
                            </div>

                            <div class="flex flex-1 flex-col gap-2 p-4">
                                <h3 class="min-h-[2.5rem] text-[13px] font-bold leading-tight text-slate-800 dark:text-white line-clamp-2" title="{{ $producto->nombre_producto }}">
                                    {{ $producto->nombre_producto }}
                                </h3>

                                @php
                                    $principalPos = $producto->presentacionPrincipalDe();
                                @endphp
                                @if($principalPos)
                                    <span class="inline-flex w-fit items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200 dark:bg-zinc-700 dark:text-zinc-300 dark:ring-zinc-600">
                                        {{ ucfirst($principalPos->presentacion?->presentacion ?? 'presentación') }} · {{ (int) $principalPos->unidades }} uds
                                    </span>
                                @else
                                    <span class="text-xs font-medium text-slate-400 dark:text-zinc-500">Sin presentación</span>
                                @endif

                                <div class="mt-auto flex flex-col gap-2 pt-1">
                                    <div class="flex items-center justify-between gap-2">
                                        @if($principalPos)
                                            <span class="text-base font-black text-[#0b6e68] dark:text-emerald-300">
                                                ${{ number_format((float) $principalPos->precio_presentacion, 2) }}
                                            </span>
                                        @else
                                            <span class="text-sm font-medium text-slate-400 dark:text-zinc-500">Sin precio</span>
                                        @endif

                                        <button
                                            wire:click="agregarAlCarrito({{ $producto->id }})"
                                            @disabled($producto->stock_sucursal <= 0)
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#0e9384] text-white shadow-sm transition hover:bg-[#0c7569] hover:shadow-md active:scale-95 disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none"
                                            @if($producto->solo_caducado)
                                                title="Sin existencias vigentes — solo lotes caducados"
                                                aria-label="No se puede agregar {{ $producto->nombre_producto }}: solo tiene lotes caducados"
                                            @elseif($producto->stock_sucursal <= 0)
                                                title="Sin existencias"
                                                aria-label="No se puede agregar {{ $producto->nombre_producto }}: sin existencias"
                                            @else
                                                title="Agregar al carrito"
                                                aria-label="Agregar {{ $producto->nombre_producto }} al carrito"
                                            @endif
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full px-2 py-1 text-[11px] font-semibold ring-1 {{ $producto->stock_sucursal <= 0 ? 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/20' : ($producto->stock_sucursal <= 15 ? 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20') }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $producto->stock_sucursal <= 0 ? 'bg-red-500' : ($producto->stock_sucursal <= 15 ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                                        {{ $producto->stock_sucursal }} disp.
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            </div>
        </div>

        {{-- Panel derecho: Carrito (desktop) --}}
        <div class="module-card hidden min-w-0 flex-col overflow-hidden !p-0 lg:flex">
            {{-- Header del carrito --}}
            <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-teal-700/5 to-transparent px-5 py-4 dark:border-zinc-700">
                <div>
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white">Carrito</h2>
                    <p class="text-xs font-medium text-slate-500 dark:text-zinc-400">{{ $cantidadArticulos }} {{ Str::plural('artículo', $cantidadArticulos) }}</p>
                </div>
                @if(count($carrito) > 0)
                    <button
                        wire:click="limpiarCarrito"
                        class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-bold text-red-600 transition hover:bg-red-100 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300"
                        title="Vaciar carrito"
                    >
                        Vaciar
                    </button>
                @endif
            </div>

            {{-- Lista de productos en carrito --}}
            <div class="flex-1 overflow-y-auto px-5 py-3">
                @if(empty($carrito))
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <svg class="mb-3 h-14 w-14 text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">Carrito vacío</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-zinc-500">Agrega productos desde el panel izquierdo</p>
                    </div>
                @else
                    <div class="flex flex-col gap-2">
                        @foreach($carrito as $index => $item)
                            <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-sm font-semibold text-slate-800 dark:text-white line-clamp-1" title="{{ $item['nombre'] }}">
                                            {{ $item['nombre'] }}
                                        </h4>
                                        @if($item['presentacion'])
                                            <p class="text-xs text-slate-500 dark:text-zinc-400">
                                                {{ ucfirst($item['presentacion']) }}
                                                @if(($item['tipo_venta'] ?? 'unidad') === 'presentacion')
                                                    &middot; {{ $item['unidades'] ?? 1 }} und
                                                @endif
                                            </p>
                                        @endif
                                        <p class="mt-1 text-sm font-bold text-[#0c9f9c]">${{ number_format($item['precio'], 2) }}</p>
                                    </div>
                                    <button
                                        wire:click="eliminarDelCarrito({{ $index }})"
                                        class="shrink-0 rounded p-1 text-slate-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20"
                                        title="Eliminar"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-1">
                                        <button
                                            wire:click="actualizarCantidad({{ $index }}, {{ $item['cantidad'] - 1 }})"
                                            class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-100 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600"
                                            @disabled($item['cantidad'] <= 1)
                                        >
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                        </button>
                                        <span class="w-8 text-center text-sm font-bold text-slate-800 dark:text-white">{{ $item['cantidad'] }}</span>
                                        <button
                                            wire:click="actualizarCantidad({{ $index }}, {{ $item['cantidad'] + 1 }})"
                                            class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-100 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600"
                                            @disabled($item['cantidad'] >= (int) floor($item['stock'] / (($item['tipo_venta'] ?? 'unidad') === 'presentacion' ? max(1, (int) ($item['unidades'] ?? 1)) : 1)))
                                        >
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </div>
                                    <span class="text-sm font-bold text-slate-800 dark:text-white">
                                        ${{ number_format($item['precio'] * $item['cantidad'], 2) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Resumen --}}
            <div class="shrink-0 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-800/70">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-500 dark:text-zinc-400">Subtotal</span>
                        <span class="font-bold text-slate-700 dark:text-zinc-200">${{ number_format($subTotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-500 dark:text-zinc-400">Descuento</span>
                        <span class="font-bold text-slate-700 dark:text-zinc-200">$0.00</span>
                    </div>
                    <div class="rounded-xl bg-gradient-to-r from-teal-700/10 to-emerald-500/10 px-4 py-3 dark:from-teal-400/10 dark:to-emerald-400/10">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-extrabold text-slate-800 dark:text-white">Total</span>
                            <span class="text-2xl font-black text-[#0b6e68] dark:text-emerald-300">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <button
                    wire:click="abrirCobro"
                    @disabled(empty($carrito))
                    class="theme-button theme-button-primary mt-4 w-full"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Cobrar ${{ number_format($total, 2) }}
                </button>
            </div>
        </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const topbarSearch = document.getElementById('topbar-pos-search');
            const livewireSearch = document.querySelector('[wire\\:model\\.live\\.debounce\\.300ms="busqueda"]');
            const topbarPills = document.querySelectorAll('[data-topbar-presentacion]');
            const getLivewire = () => {
                const input = document.querySelector('[wire\\:model\\.live\\.debounce\\.300ms="busqueda"]');
                const el = input ? input.closest('[wire\\:id]') : document.querySelector('[wire\\:id]');
                if (!el) return null;
                const id = el.getAttribute('wire:id') || el.getAttribute('wire\\:id');
                return window.Livewire ? (window.Livewire.find ? window.Livewire.find(id) : null) : null;
            };
            if (topbarSearch && livewireSearch) {
                topbarSearch.addEventListener('input', (e) => {
                    livewireSearch.value = e.target.value;
                    livewireSearch.dispatchEvent(new Event('input', {bubbles:true}));
                });
                document.addEventListener('livewire:update', () => {
                    if (document.activeElement !== topbarSearch) topbarSearch.value = livewireSearch.value;
                });
            }
            topbarPills.forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-topbar-presentacion');
                    const comp = getLivewire();
                    if (comp) comp.set('filtroPresentacion', id);
                    topbarPills.forEach(b => {
                        b.classList.remove('border-[#0a5f56]','bg-[#0e9384]','text-white','shadow-sm');
                        b.classList.add('border-slate-200','bg-white','text-slate-600');
                        const dot = b.querySelector('span');
                        if (dot) { dot.classList.remove('bg-white'); dot.classList.add('bg-slate-300','dark:bg-zinc-500'); }
                    });
                    btn.classList.remove('border-slate-200','bg-white','text-slate-600');
                    btn.classList.add('border-[#0a5f56]','bg-[#0e9384]','text-white','shadow-sm');
                    const dot = btn.querySelector('span');
                    if (dot) { dot.classList.remove('bg-slate-300','dark:bg-zinc-500'); dot.classList.add('bg-white'); }
                });
            });
        });
    </script>

    {{-- Botón flotante carrito (móvil) --}}
    <div class="fixed bottom-5 right-5 z-40 sm:bottom-6 sm:right-6 lg:hidden">
        <button
            wire:click="$set('mostrandoCarrito', true)"
            class="relative flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-[#0e9384] to-[#0c7569] text-white shadow-xl shadow-teal-900/30 transition hover:brightness-110 active:scale-95"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
            @if($cantidadArticulos > 0)
                <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">{{ $cantidadArticulos }}</span>
            @endif
        </button>
    </div>

    {{-- Overlay carrito móvil --}}
    @if($mostrandoCarrito)
        <div
            class="fixed inset-0 z-50 lg:hidden"
            x-data
            x-init="$nextTick(() => { $el.querySelector('[data-cart-panel]')?.classList.remove('translate-x-full'); })"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="absolute inset-0 bg-black/50 transition-opacity" wire:click="$set('mostrandoCarrito', false)"></div>
            <div
                data-cart-panel
                class="absolute inset-y-0 right-0 flex w-full max-w-sm flex-col bg-white shadow-2xl transition-transform duration-300 ease-out translate-x-0 dark:bg-zinc-900"
            >
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-zinc-700">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-white">Carrito</h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400">{{ $cantidadArticulos }} {{ Str::plural('artículo', $cantidadArticulos) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(count($carrito) > 0)
                            <button wire:click="limpiarCarrito" class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-bold text-red-600 transition hover:bg-red-100 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">Vaciar</button>
                        @endif
                        <button wire:click="$set('mostrandoCarrito', false)" class="rounded p-1 text-slate-400 hover:text-slate-600 dark:text-zinc-500 dark:hover:text-zinc-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Lista --}}
                <div class="flex-1 overflow-y-auto px-5 py-3">
                    @if(empty($carrito))
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <svg class="mb-3 h-14 w-14 text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                            </svg>
                            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">Carrito vacío</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-zinc-500">Agrega productos desde el panel izquierdo</p>
                        </div>
                    @else
                        <div class="flex flex-col gap-2">
                            @foreach($carrito as $index => $item)
                                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-sm font-semibold text-slate-800 dark:text-white line-clamp-1">{{ $item['nombre'] }}</h4>
                                            @if($item['presentacion'])
                                                <p class="text-xs text-slate-500 dark:text-zinc-400">
                                                    {{ ucfirst($item['presentacion']) }}
                                                    @if(($item['tipo_venta'] ?? 'unidad') === 'presentacion')
                                                        &middot; {{ $item['unidades'] ?? 1 }} und
                                                    @endif
                                                </p>
                                            @endif
                                            <p class="mt-1 text-sm font-bold text-[#0c9f9c]">${{ number_format($item['precio'], 2) }}</p>
                                        </div>
                                        <button wire:click="eliminarDelCarrito({{ $index }})" class="shrink-0 rounded p-1 text-slate-400 hover:text-red-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="flex items-center gap-1">
                                            <button wire:click="actualizarCantidad({{ $index }}, {{ $item['cantidad'] - 1 }})" class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600" @disabled($item['cantidad'] <= 1)>
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                            </button>
                                            <span class="w-8 text-center text-sm font-bold text-slate-800 dark:text-white">{{ $item['cantidad'] }}</span>
                                            <button wire:click="actualizarCantidad({{ $index }}, {{ $item['cantidad'] + 1 }})" class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600" @disabled($item['cantidad'] >= (int) floor($item['stock'] / (($item['tipo_venta'] ?? 'unidad') === 'presentacion' ? max(1, (int) ($item['unidades'] ?? 1)) : 1)))>
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                            </button>
                                        </div>
                                        <span class="text-sm font-bold text-slate-800 dark:text-white">${{ number_format($item['precio'] * $item['cantidad'], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Resumen --}}
                <div class="shrink-0 border-t border-slate-200 bg-slate-50/80 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-800/80">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 dark:text-zinc-400">Subtotal</span>
                            <span class="font-medium text-slate-700 dark:text-zinc-200">${{ number_format($subTotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 dark:text-zinc-400">Descuento</span>
                            <span class="font-medium text-slate-700 dark:text-zinc-200">$0.00</span>
                        </div>
                        <div class="border-t border-slate-200 pt-2 dark:border-zinc-600">
                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-slate-800 dark:text-white">Total</span>
                                <span class="text-xl font-extrabold text-[#0c9f9c]">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <button wire:click="abrirCobro" @disabled(empty($carrito)) class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-[#0c9f9c] px-4 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-[#0a8582] disabled:opacity-40">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Cobrar ${{ number_format($total, 2) }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Modal de Cobro / Pago --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($abriendoCobro)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center p-4"
            x-data
            x-init="$nextTick(() => $refs.firstInput?.focus())"
        >
            {{-- Backdrop --}}
            <div
                class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
            ></div>

            {{-- Modal --}}
            <div
                class="relative flex max-h-[88vh] w-full max-w-lg flex-col overflow-hidden rounded-[1.75rem] bg-white shadow-2xl ring-1 ring-black/5 dark:bg-zinc-900 dark:ring-white/10"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                {{-- Header --}}
                <div class="relative bg-[#0e9384] px-8 py-7 text-center dark:bg-[#0c7569]">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/90">Total a pagar</p>
                    <p class="mt-2 text-4xl font-black tracking-tight text-white">${{ number_format($total, 2) }}</p>
                    <p class="mx-auto mt-3 inline-flex items-center gap-1.5 rounded-full bg-white px-3.5 py-1.5 text-xs font-bold text-[#0c7569] shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#0e9384]"></span>
                        {{ $cantidadProductos }} {{ Str::plural('producto', $cantidadProductos) }} · {{ $cantidadArticulos }} artículos
                    </p>
                </div>

                {{-- Error --}}
                @if($errorVenta)
                    <div class="mx-6 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800 dark:bg-red-900/30">
                        <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ $errorVenta }}</p>
                    </div>
                @endif

                {{-- Métodos de pago --}}
                <div class="px-6 pt-5">
                    <p class="mb-3 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Método de pago</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            wire:click="seleccionarMetodo('efectivo')"
                            class="flex flex-col items-center gap-2 rounded-2xl border-2 px-4 py-4 text-xs font-bold transition
                                {{ $metodoPago === 'efectivo' ? 'border-[#0e9384] bg-teal-50 text-[#0c7569] shadow-md shadow-teal-900/10 dark:border-[#5ec8b4] dark:bg-[#0e9384]/20 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-[#0e9384]/40 hover:bg-teal-50/50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-[#5ec8b4]/40' }}"
                        >
                            <span class="grid h-9 w-9 place-items-center rounded-xl {{ $metodoPago === 'efectivo' ? 'bg-[#0e9384] text-white' : 'bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400' }}"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span>
                            Efectivo
                        </button>
                        <button
                            wire:click="seleccionarMetodo('tarjeta')"
                            class="flex flex-col items-center gap-2 rounded-2xl border-2 px-4 py-4 text-xs font-bold transition
                                {{ $metodoPago === 'tarjeta' ? 'border-[#0e9384] bg-teal-50 text-[#0c7569] shadow-md shadow-teal-900/10 dark:border-[#5ec8b4] dark:bg-[#0e9384]/20 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-[#0e9384]/40 hover:bg-teal-50/50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-[#5ec8b4]/40' }}"
                        >
                            <span class="grid h-9 w-9 place-items-center rounded-xl {{ $metodoPago === 'tarjeta' ? 'bg-[#0e9384] text-white' : 'bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400' }}"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></span>
                            Tarjeta
                        </button>
                        <button
                            wire:click="seleccionarMetodo('transferencia')"
                            class="flex flex-col items-center gap-2 rounded-2xl border-2 px-4 py-4 text-xs font-bold transition
                                {{ $metodoPago === 'transferencia' ? 'border-[#0e9384] bg-teal-50 text-[#0c7569] shadow-md shadow-teal-900/10 dark:border-[#5ec8b4] dark:bg-[#0e9384]/20 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-[#0e9384]/40 hover:bg-teal-50/50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-[#5ec8b4]/40' }}"
                        >
                            <span class="grid h-9 w-9 place-items-center rounded-xl {{ $metodoPago === 'transferencia' ? 'bg-[#0e9384] text-white' : 'bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400' }}"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></span>
                            Transf.
                        </button>
                        <button
                            wire:click="seleccionarMetodo('mixto')"
                            class="flex flex-col items-center gap-2 rounded-2xl border-2 px-4 py-4 text-xs font-bold transition
                                {{ $metodoPago === 'mixto' ? 'border-[#0e9384] bg-teal-50 text-[#0c7569] shadow-md shadow-teal-900/10 dark:border-[#5ec8b4] dark:bg-[#0e9384]/20 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-[#0e9384]/40 hover:bg-teal-50/50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-[#5ec8b4]/40' }}"
                        >
                            <span class="grid h-9 w-9 place-items-center rounded-xl {{ $metodoPago === 'mixto' ? 'bg-[#0e9384] text-white' : 'bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400' }}"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg></span>
                            Mixto
                        </button>
                    </div>
                </div>

                {{-- Contenido con scroll --}}
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    {{-- Efectivo --}}
                    @if($metodoPago === 'efectivo')
                        <div>
                            <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-slate-600 dark:text-zinc-300">Efectivo recibido</label>
                            <div class="flex items-center overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition focus-within:border-[#0e9384] focus-within:ring-4 focus-within:ring-[#0e9384]/10 dark:border-zinc-600 dark:bg-zinc-800">
                                <span class="pl-4 text-2xl font-black text-slate-400 dark:text-zinc-500">$</span>
                                <input
                                    x-ref="firstInput"
                                    type="text"
                                    inputmode="decimal"
                                    wire:model.live="efectivoRecibido"
                                    placeholder="0.00"
                                    class="flex-1 border-0 bg-transparent py-4 pr-4 text-2xl font-black tracking-tight text-slate-900 placeholder:text-slate-300 focus:outline-none focus:ring-0 dark:text-white dark:placeholder:text-zinc-500"
                                >
                            </div>

                            {{-- Botones rápidos --}}
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach([500, 200, 100, 50, 20, 10] as $billete)
                                    <button
                                        wire:click="setEfectivoRapido({{ $billete }})"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-[#0c9f9c] hover:text-[#0c9f9c] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-[#0c9f9c]"
                                    >
                                        +${{ $billete }}
                                    </button>
                                @endforeach
                            </div>

                            {{-- Cambio --}}
                            <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-slate-500 dark:text-zinc-400">Cambio</span>
                                    <span class="text-2xl font-extrabold {{ $cambio > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-zinc-500' }}">
                                        ${{ number_format($cambio, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Tarjeta --}}
                    @if($metodoPago === 'tarjeta')
                        <div class="space-y-4">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Monto cobrado</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Total exacto</p>
                                <p class="mt-1 text-2xl font-extrabold text-[#0c9f9c]">${{ number_format($total, 2) }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">N.° de aprobacion <span class="font-normal normal-case tracking-normal">(opcional)</span></label>
                                <input
                                    type="text"
                                    wire:model.live="referenciaTarjeta"
                                    placeholder="123456"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                                >
                            </div>
                        </div>
                    @endif

                    {{-- Transferencia --}}
                    @if($metodoPago === 'transferencia')
                        @php
                            $tieneDatosBancarios = trim($configBancaria->banco) !== '' && trim($configBancaria->clabe) !== '';
                        @endphp

                        <div class="space-y-4">
                            @if(! $tieneDatosBancarios && $esSuperAdmin)
                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20">
                                    <div class="flex items-start gap-3">
                                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-amber-700 dark:text-amber-300">Datos bancarios no configurados</p>
                                            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">Configura los datos bancarios para que el cliente pueda realizar la transferencia.</p>
                                            <a
                                                href="{{ route('settings.transferencia') }}"
                                                class="mt-2 inline-flex items-center gap-1 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-amber-600"
                                                wire:navigate
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                Configurar datos bancarios
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @elseif(! $tieneDatosBancarios && ! $esSuperAdmin)
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center dark:border-zinc-700 dark:bg-zinc-800">
                                    <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-zinc-400">Datos bancarios no disponibles</p>
                                    <p class="mt-1 text-xs text-slate-400 dark:text-zinc-500">Contacta al administrador para configurar los datos de transferencia.</p>
                                </div>
                            @else
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Datos bancarios</p>

                                    <div class="mt-3 space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-slate-500 dark:text-zinc-400">Banco</span>
                                            <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $configBancaria->banco }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-slate-500 dark:text-zinc-400">CLABE</span>
                                            <span class="text-sm font-bold tracking-wider text-slate-800 dark:text-white">{{ $configBancaria->clabe }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-slate-500 dark:text-zinc-400">Beneficiario</span>
                                            <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $configBancaria->beneficiario }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-slate-500 dark:text-zinc-400">Concepto</span>
                                            <span class="text-sm font-bold text-slate-800 dark:text-white">Venta #{{ 'V-' . date('Ymd') }}</span>
                                        </div>
                                    </div>

                                    <div class="mt-3 border-t border-slate-200 pt-3 dark:border-zinc-600">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Monto a transferir</span>
                                            <span class="text-xl font-extrabold text-[#0c9f9c]">${{ number_format($total, 2) }}</span>
                                        </div>
                                        <div class="mt-1.5 flex items-center justify-between">
                                            <span class="text-xs text-slate-500 dark:text-zinc-400">N.° referencia / folio</span>
                                            <span class="text-sm font-bold text-slate-800 dark:text-white">REF-{{ date('Ymd') }}-{{ str_pad($cantidadArticulos + 1, 5, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if($esSuperAdmin)
                                    <a
                                        href="{{ route('settings.transferencia') }}"
                                        class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:border-[#0c9f9c] hover:text-[#0c9f9c] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-[#0c9f9c] dark:hover:text-[#0c9f9c]"
                                        wire:navigate
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Editar datos bancarios
                                    </a>
                                @endif
                            @endif
                        </div>
                    @endif

                    {{-- Pago mixto --}}
                    @if($metodoPago === 'mixto')
                        @php
                            $restante = max(0, $total - $pagado);
                            $tieneDatosBancarios = trim($configBancaria->banco) !== '' && trim($configBancaria->clabe) !== '';
                        @endphp

                        <div
                            class="space-y-4"
                            x-data="{
                                activoEfectivo: @js((float) $pagoMixto['efectivo'] > 0),
                                activoTarjeta: @js((float) $pagoMixto['tarjeta'] > 0),
                                activoTransferencia: @js((float) $pagoMixto['transferencia'] > 0),
                                toggle(metodo) {
                                    if (metodo === 'efectivo') { this.activoEfectivo = !this.activoEfectivo; if (!this.activoEfectivo) $wire.set('pagoMixto.efectivo', 0); }
                                    if (metodo === 'tarjeta') { this.activoTarjeta = !this.activoTarjeta; if (!this.activoTarjeta) $wire.set('pagoMixto.tarjeta', 0); }
                                    if (metodo === 'transferencia') { this.activoTransferencia = !this.activoTransferencia; if (!this.activoTransferencia) $wire.set('pagoMixto.transferencia', 0); }
                                }
                            }"
                        >
                            {{-- Header --}}
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <p class="text-xs font-medium text-slate-600 dark:text-zinc-300">Selecciona los metodos y captura el monto de cada uno. La suma debe ser igual al total.</p>
                                <div class="mt-2 flex flex-wrap gap-2 text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">
                                    <span>Efectivo</span>
                                    <span>Tarjeta</span>
                                    <span>Transferencia</span>
                                </div>
                            </div>

                            {{-- Botones toggle --}}
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    @click="toggle('efectivo')"
                                    class="flex items-center justify-center gap-1.5 rounded-xl border-2 px-3 py-3 text-xs font-bold transition
                                        "
                                    :class="activoEfectivo ? 'border-[#0c9f9c] bg-[#0c9f9c]/10 text-[#0c9f9c] dark:border-[#5ec8b4] dark:bg-[#0e9384]/25 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-[#5ec8b4] dark:hover:text-emerald-200'"
                                >
                                    &#x1F4B5; Efectivo
                                </button>
                                <button
                                    @click="toggle('tarjeta')"
                                    class="flex items-center justify-center gap-1.5 rounded-xl border-2 px-3 py-3 text-xs font-bold transition
                                        "
                                    :class="activoTarjeta ? 'border-[#0c9f9c] bg-[#0c9f9c]/10 text-[#0c9f9c] dark:border-[#5ec8b4] dark:bg-[#0e9384]/25 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-[#5ec8b4] dark:hover:text-emerald-200'"
                                >
                                    &#x1F4B3; Tarjeta
                                </button>
                                <button
                                    @click="toggle('transferencia')"
                                    class="flex items-center justify-center gap-1.5 rounded-xl border-2 px-3 py-3 text-xs font-bold transition
                                        "
                                    :class="activoTransferencia ? 'border-[#0c9f9c] bg-[#0c9f9c]/10 text-[#0c9f9c] dark:border-[#5ec8b4] dark:bg-[#0e9384]/25 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-[#5ec8b4] dark:hover:text-emerald-200'"
                                >
                                    &#x1F4F2; Transf.
                                </button>
                            </div>

                            {{-- Input Efectivo --}}
                            <div
                                x-show="activoEfectivo"
                                x-collapse
                                class="rounded-xl border border-slate-200 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
                            >
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm">&#x1F4B5;</span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">Efectivo</span>
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">$</span>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        wire:model.live="pagoMixto.efectivo"
                                        placeholder="0.00"
                                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-7 pr-3 text-sm font-bold text-slate-800 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    >
                                </div>
                            </div>

                            {{-- Input Tarjeta --}}
                            <div
                                x-show="activoTarjeta"
                                x-collapse
                                class="rounded-xl border border-slate-200 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
                            >
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm">&#x1F4B3;</span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">Tarjeta</span>
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">$</span>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        wire:model.live="pagoMixto.tarjeta"
                                        placeholder="0.00"
                                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-7 pr-3 text-sm font-bold text-slate-800 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                    >
                                </div>
                            </div>

                            {{-- Input Transferencia --}}
                            <div
                                x-show="activoTransferencia"
                                x-collapse
                                class="rounded-xl border border-slate-200 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
                            >
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm">&#x1F4F2;</span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">Transferencia</span>
                                </div>

                                @if(! $tieneDatosBancarios && $esSuperAdmin)
                                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20">
                                        <div class="flex items-start gap-2">
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <div class="flex-1">
                                                <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">Datos bancarios no configurados</p>
                                                <a href="{{ route('settings.transferencia') }}" class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 underline hover:text-amber-700 dark:text-amber-400" wire:navigate>Configurar</a>
                                            </div>
                                        </div>
                                    </div>
                                @elseif(! $tieneDatosBancarios && ! $esSuperAdmin)
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-center dark:border-zinc-600 dark:bg-zinc-800">
                                        <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Datos bancarios no disponibles</p>
                                        <p class="mt-0.5 text-[10px] text-slate-400 dark:text-zinc-500">Contacta al administrador.</p>
                                    </div>
                                @else
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">$</span>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            wire:model.live="pagoMixto.transferencia"
                                            placeholder="0.00"
                                            class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-7 pr-3 text-sm font-bold text-slate-800 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                        >
                                    </div>
                                @endif
                            </div>

                            {{-- Resumen --}}
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="space-y-1.5">
                                    @if((float) $pagoMixto['efectivo'] > 0)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-500 dark:text-zinc-400">&#x1F4B5; Efectivo</span>
                                            <span class="font-bold text-slate-800 dark:text-white">${{ number_format((float) $pagoMixto['efectivo'], 2) }}</span>
                                        </div>
                                    @endif
                                    @if((float) $pagoMixto['tarjeta'] > 0)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-500 dark:text-zinc-400">&#x1F4B3; Tarjeta</span>
                                            <span class="font-bold text-slate-800 dark:text-white">${{ number_format((float) $pagoMixto['tarjeta'], 2) }}</span>
                                        </div>
                                    @endif
                                    @if((float) $pagoMixto['transferencia'] > 0)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-500 dark:text-zinc-400">&#x1F4F2; Transferencia</span>
                                            <span class="font-bold text-slate-800 dark:text-white">${{ number_format((float) $pagoMixto['transferencia'], 2) }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-3 border-t border-slate-200 pt-3 dark:border-zinc-600">
                                    @if($restante > 0)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Faltan ${{ number_format($restante, 2) }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Total de la venta</span>
                                        <span class="text-xl font-extrabold text-[#0c9f9c]">${{ number_format($total, 2) }}</span>
                                    </div>
                                    @if($pagoMixto['efectivo'] > 0 && $cambio > 0)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-500 dark:text-zinc-400">Cambio</span>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($cambio, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex gap-3 border-t border-slate-200 px-6 py-4 dark:border-zinc-700">
                    <button
                        wire:click="cerrarCobro"
                        class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="confirmarVenta"
                        @disabled(! $puedeConfirmar)
                        class="flex-1 flex items-center justify-center gap-2 rounded-xl bg-[#0c9f9c] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-[#0c9f9c]/20 transition hover:bg-[#0a8582] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none"
                    >
                        @if($procesando)
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Procesando...
                        @else
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Confirmar venta
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Modal Receta Medica (medicamentos controlados) --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($mostrandoReceta)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="cerrarReceta"></div>

            <div
                class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900"
                x-data
                x-init="$nextTick(() => $refs.recetaMedico?.focus())"
            >
                {{-- Header --}}
                <div class="bg-amber-500 px-6 py-5 text-center dark:bg-amber-600">
                    <svg class="mx-auto h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-lg font-bold text-white">Medicamento controlado</h3>
                    <p class="mt-1 text-sm font-medium text-amber-100">Receta medica requerida</p>

                    @php
                        $productoReceta = \App\Models\Producto::find($recetaProductoId);
                    @endphp
                    @if($productoReceta)
                        <p class="mt-2 text-sm text-amber-200">
                            {{ $productoReceta->nombre_producto }}
                            @if($productoReceta->presentacion)
                                &middot; {{ ucfirst($productoReceta->presentacion->presentacion) }}
                            @endif
                        </p>
                    @endif
                </div>

                @if($errorReceta)
                    <div class="mx-6 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800 dark:bg-red-900/30">
                        <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ $errorReceta }}</p>
                    </div>
                @endif

                <div class="px-6 py-5">
                    <p class="mb-4 text-xs text-slate-500 dark:text-zinc-400">
                        La venta no puede continuar hasta completar y validar la informacion de la receta medica.
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Nombre del medico</label>
                            <input
                                x-ref="recetaMedico"
                                type="text"
                                wire:model.live="recetaNombreMedico"
                                placeholder="Dr. Juan Perez Lopez"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Cedula profesional</label>
                            <input
                                type="text"
                                wire:model.live="recetaCedula"
                                placeholder="1234567"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                            >
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Folio de receta</label>
                                <input
                                    type="text"
                                    wire:model.live="recetaFolio"
                                    placeholder="RC-2026-00123"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                                >
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Fecha de receta</label>
                                <input
                                    type="date"
                                    wire:model.live="recetaFecha"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 border-t border-slate-200 px-6 py-4 dark:border-zinc-700">
                    <button
                        wire:click="cerrarReceta"
                        class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancelar
                    </button>
                    <button
                        wire:click="validarYAgregar"
                        class="flex-1 flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/20 transition hover:bg-amber-600 active:scale-[0.98]"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Validar y continuar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Modal Selector de venta (presentaciones y/o unidad suelta) --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($selectorProductoId)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="cerrarSelector"></div>

            <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900">
                {{-- Header --}}
                <div class="bg-[#0c9f9c] px-6 py-5 text-center">
                    <svg class="mx-auto h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <h3 class="mt-2 text-lg font-bold text-white">
                        {{ $selectorPrecioUnidad !== null ? 'Elegir cómo se vende' : 'Seleccionar presentación' }}
                    </h3>
                    <p class="mt-1 text-sm font-medium text-emerald-100">{{ $selectorNombreProducto }}</p>
                </div>

                <div class="px-6 py-5">
                    @if($selectorEstadoCajas)
                        <p class="mb-4 rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $selectorEstadoCajas['selladas'] }} cajas selladas
                            <span class="mx-1 font-normal text-slate-400 dark:text-zinc-500">·</span>
                            {{ $selectorEstadoCajas['sueltas'] }} unidades sueltas
                            <span class="font-normal text-slate-500 dark:text-zinc-400">(caja de {{ $selectorEstadoCajas['unidades_por_caja'] }} und)</span>
                        </p>
                    @endif

                    @php
                        $opcionesVenta = collect($selectorPresentaciones)->map(fn (array $presentacion) => [
                            'etiqueta' => ucfirst($presentacion['presentacion']),
                            'detalle' => $presentacion['unidades'].' und',
                            'precio' => $presentacion['precio'],
                            'accion' => 'elegirPresentacion('.$presentacion['id'].')',
                            'agotada' => (int) ($presentacion['maximo'] ?? 0) < 1,
                            'nota' => null,
                        ]);

                        if ($selectorPrecioUnidad !== null) {
                            $opcionesVenta->prepend([
                                'etiqueta' => 'Unidad',
                                'detalle' => '1 und',
                                'precio' => $selectorPrecioUnidad,
                                'accion' => 'elegirUnidad()',
                                'agotada' => false,
                                'nota' => $selectorEstadoCajas !== null
                                    && $selectorEstadoCajas['selladas'] > 0
                                    && $selectorEstadoCajas['sueltas'] === 0
                                        ? 'Al vender 1 ud se abrirá una caja'
                                        : null,
                            ]);
                        }
                    @endphp

                    @if($opcionesVenta->isEmpty())
                        <p class="text-center text-sm text-slate-500 dark:text-zinc-400">
                            Este producto no tiene presentaciones registradas.
                        </p>
                    @else
                        <p class="mb-4 text-xs text-slate-500 dark:text-zinc-400">
                            {{ $selectorPrecioUnidad !== null
                                ? 'Elige si se venderá por presentación o por unidad suelta.'
                                : 'Elige la presentación con la que se venderá este producto.' }}
                        </p>

                        <div class="flex flex-col gap-2">
                            @foreach($opcionesVenta as $opcion)
                                <button
                                    wire:click="{{ $opcion['accion'] }}"
                                    @disabled($opcion['agotada'])
                                    class="flex items-center justify-between rounded-xl border px-4 py-3 text-left transition active:scale-[0.99] {{ $opcion['agotada']
                                        ? 'cursor-not-allowed border-slate-200 bg-slate-100 opacity-70 dark:border-zinc-700 dark:bg-zinc-800/60'
                                        : 'border-slate-200 bg-slate-50 hover:border-[#0c9f9c] hover:bg-[#0c9f9c]/5 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-[#0c9f9c]' }}"
                                >
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-white">
                                            {{ $opcion['etiqueta'] }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-zinc-400">
                                            {{ $opcion['agotada'] ? 'Stock insuficiente' : $opcion['detalle'] }}
                                        </p>
                                        @if($opcion['nota'])
                                            <p class="mt-0.5 text-[11px] font-semibold text-amber-600 dark:text-amber-400">
                                                {{ $opcion['nota'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="text-base font-extrabold text-[#0c9f9c]">
                                        ${{ number_format($opcion['precio'], 2) }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="border-t border-slate-200 px-6 py-4 dark:border-zinc-700">
                    <button
                        wire:click="cerrarSelector"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
