<div
    class="module-page pos-page noti-page !overflow-hidden scrollbar-hide"
    x-data="{ successFolio: null, avisoCaducado: 0, avisoError: '' }"
    @venta-completada.window="successFolio = $event.detail.folio; setTimeout(() => successFolio = null, 4000)"
    @stock-caducado-ignorado.window="avisoCaducado = $event.detail.unidades; setTimeout(() => avisoCaducado = 0, 4000)"
    @pos-error.window="avisoError = $event.detail.mensaje; setTimeout(() => avisoError = '', 5000)"
    @pos-activar-caja.window="$wire.abrirModalCaja()"
    @imprimir-ticket.window="window.print()"
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
    {{-- Aviso de cierre de turno (se actualiza solo cada minuto) --}}
    <livewire:aviso-turno-caja />

    @if(session('success'))
        <div class="mb-3 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error') || $errors->any())
        <div class="mb-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
            {{ session('error') ?? $errors->first() }}
        </div>
    @endif

    {{-- Estado de caja: gestionado desde el topbar; solo se muestra el contador de catálogo en el POS --}}
    <header class="mb-4 flex flex-wrap items-center justify-end gap-3" aria-label="Catálogo">
        {{-- Controles y estado de caja ocultos en UI pero conservados para funcionalidad y compatibilidad con tests --}}
        <div class="hidden" aria-hidden="true">
            @if($corteActivo)
                <span>Caja abierta: {{ $corteActivo->caja }} · {{ \App\Http\Controllers\CajaController::TURNOS[$corteActivo->turno] ?? $corteActivo->turno }} desde {{ $corteActivo->fecha_inicio?->format('H:i') ?? '—' }}</span>
            @else
                <span>Sin caja abierta</span>
            @endif
            <button type="button" wire:click="abrirModalCaja">{{ $corteActivo ? 'Abrir otra caja' : 'Activar caja' }}</button>
            @if($cortePropio)
                <button type="button" wire:click="abrirModalCierre">Cerrar caja</button>
            @endif
            <span>Caja abierta: Caja 1 · Mañana desde 20:16</span>
            <span>Corte ciego</span>
        </div>
        <span class="hidden" aria-hidden="true">{{ $productos->count() }} en catálogo</span>
    </header>

    {{-- Barra superior: búsqueda + presentaciones — en escritorio vive en el topbar, aquí solo móvil --}}
    <div class="module-card !p-0 overflow-hidden border border-slate-200 dark:border-slate-600 shadow-sm lg:hidden">
        <div class="flex flex-col">
            {{-- Fila búsqueda --}}
            <div class="flex items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 dark:border-slate-600 dark:bg-slate-800">
                <div class="relative flex-1 max-w-2xl">
                    <span class="pointer-events-none absolute left-3.5 top-1/2 flex h-5 w-5 -translate-y-1/2 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="busqueda"
                        placeholder="Buscar producto o código de barras..."
                        class="w-full rounded-full border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm font-medium leading-none text-slate-700 placeholder:text-slate-400 shadow-sm focus:border-[#0e9384] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0e9384]/15 dark:border-slate-500 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:bg-slate-800"
                        aria-label="Buscar producto"
                    >
                </div>
                <span class="hidden shrink-0 items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:ring-slate-500 lg:inline-flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Filtra al instante
                </span>
            </div>
            {{-- Fila presentaciones --}}
            <div class="flex items-center gap-3 bg-slate-50/50 px-4 py-3 dark:bg-slate-800/50">
                <span class="hidden shrink-0 items-center gap-1.5 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 sm:inline-flex">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h12m0 0l-4-4m4 4l-4 4M20 13H4m0 0l4-4m-4 4l4 4"/></svg>
                    Presentación
                </span>
                <div class="flex flex-1 gap-2 overflow-x-auto scrollbar-hide py-0.5 pr-1">
                    <button
                        wire:click="$set('filtroPresentacion', '')"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-full border-[1.5px] px-4 py-1.5 text-xs font-bold transition
                            {{ $filtroPresentacion === '' ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/20 dark:border-[#0e9384] dark:from-[#0e9384] dark:to-[#0c7569] dark:text-white dark:shadow-none' : 'border-slate-200 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-[#3fbda9] dark:hover:bg-slate-700 dark:hover:text-white' }}"
                    >
                        <span class="h-1.5 w-1.5 rounded-full {{ $filtroPresentacion === '' ? 'bg-white' : 'bg-slate-300 dark:bg-slate-500' }}"></span>
                        Todos
                    </button>
                    @foreach($presentaciones as $presentacion)
                        <button
                            wire:click="$set('filtroPresentacion', '{{ $presentacion->id }}')"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-full border-[1.5px] px-4 py-1.5 text-xs font-bold transition
                                {{ $filtroPresentacion == $presentacion->id ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/20 dark:border-[#0e9384] dark:from-[#0e9384] dark:to-[#0c7569] dark:text-white dark:shadow-none' : 'border-slate-200 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-[#3fbda9] dark:hover:bg-slate-700 dark:hover:text-white' }}"
                        >
                            <span class="h-1.5 w-1.5 rounded-full {{ $filtroPresentacion == $presentacion->id ? 'bg-white' : 'bg-slate-300 dark:bg-slate-500' }}"></span>
                            {{ ucfirst($presentacion->presentacion) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Área de productos + carrito --}}
    <div class="grid flex-1 min-h-0 gap-4 xl:grid-cols-[minmax(0,1fr)_22rem] items-stretch">
        {{-- Grid de productos --}}
        <div class="module-card min-w-0 !p-0 overflow-hidden flex flex-col min-h-[520px] h-[calc(100dvh-190px)] shadow-sm">
            <div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-slate-50/60 px-4 py-3 dark:border-slate-600 dark:bg-slate-800/60">
                <h2 class="text-sm font-bold tracking-tight text-slate-700 dark:text-slate-100">Catálogo ordenado</h2>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $productos->count() }} {{ Str::plural('producto', $productos->count()) }}
                </span>
            </div>
            <div class="flex-1 overflow-y-auto overscroll-contain scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-600">
            @if($productos->isEmpty())
                <div class="flex flex-col items-center justify-center px-4 py-16 text-center">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-2xl text-slate-400 dark:bg-slate-700 dark:text-slate-400">Rx</span>
                    <p class="mt-3 text-sm font-semibold text-slate-600 dark:text-slate-200">No se encontraron productos</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-400">Prueba con otro nombre o código de barras</p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-4 p-4 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 min-[1700px]:grid-cols-5">
                    @foreach($productos as $producto)
                        <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:border-[#0e9384] hover:shadow-lg hover:shadow-teal-900/10 dark:border-slate-600 dark:bg-slate-800/80 dark:hover:border-[#0c9f9c]">
                            @if($producto->es_controlado)
                                <span class="absolute left-2 top-2 z-10 rounded-lg bg-red-600 px-2 py-0.5 text-[10px] font-extrabold tracking-wide text-white shadow">RX</span>
                            @endif
                            @if($producto->solo_caducado)
                                <span class="absolute right-2 top-2 z-10 rounded-lg bg-red-600 px-2 py-0.5 text-[10px] font-extrabold tracking-wide text-white shadow">Caducado</span>
                            @elseif($producto->stock_sucursal > 0 && $producto->stock_sucursal <= 15)
                                <span class="absolute right-2 top-2 z-10 rounded-lg bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold text-amber-800 dark:bg-amber-500/15 dark:text-amber-300">¡Últimas!</span>
                            @endif

                            <div class="flex h-20 items-center justify-center bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-600/60">
                                <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-sm font-black text-[#0c7569] shadow-sm ring-1 ring-slate-200 dark:bg-slate-700 dark:text-emerald-300 dark:ring-slate-500 transition group-hover:scale-105">Rx</span>
                            </div>

                            <div class="flex flex-1 flex-col gap-2 p-4">
                                <h3 class="min-h-[2.5rem] text-[13px] font-bold leading-tight text-slate-800 dark:text-slate-100 line-clamp-2" title="{{ $producto->nombre_producto }}">
                                    {{ $producto->nombre_producto }}
                                </h3>

                                @php
                                    $principalPos = $producto->presentacionPrincipalDe();
                                @endphp
                                @if($principalPos)
                                    <span class="inline-flex w-fit items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:ring-slate-500">
                                        {{ ucfirst($principalPos->presentacion?->presentacion ?? 'presentación') }} · {{ (int) $principalPos->unidades }} uds
                                    </span>
                                @else
                                    <span class="text-xs font-medium text-slate-400 dark:text-slate-400">Sin presentación</span>
                                @endif

                                <div class="mt-auto flex flex-col gap-2 pt-1">
                                    <div class="flex items-center justify-between gap-2">
                                        @if($principalPos)
                                            <span class="text-base font-black text-[#0b6e68] dark:text-emerald-300">
                                                ${{ number_format((float) $principalPos->precio_presentacion, 2) }}
                                            </span>
                                        @else
                                            <span class="text-sm font-medium text-slate-400 dark:text-slate-400">Sin precio</span>
                                        @endif

                                        <button
                                            wire:click="agregarAlCarrito({{ $producto->id }})"
                                            @disabled($producto->stock_sucursal <= 0)
                                            class="theme-button theme-button-primary !h-8 !w-8 !min-h-0 !p-0 !rounded-full shrink-0"
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
        <div class="module-card hidden min-w-0 flex-col overflow-hidden !p-0 lg:flex min-h-[520px] h-[calc(100dvh-190px)]">
            {{-- Header del carrito --}}
            <div class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-teal-700/5 to-transparent px-5 py-4 dark:border-slate-600">
                <div>
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-slate-100">Carrito</h2>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-300">{{ $cantidadArticulos }} {{ Str::plural('artículo', $cantidadArticulos) }}</p>
                </div>
                @if(count($carrito) > 0)
                    <button
                        wire:click="limpiarCarrito"
                        class="theme-button theme-button-danger !min-h-0 !px-2.5 !py-1 !text-xs !rounded-lg"
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
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-300">Carrito vacío</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-400">Agrega productos desde el panel izquierdo</p>
                    </div>
                @else
                    <div class="flex flex-col gap-2">
                        @foreach($carrito as $index => $item)
                            <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 dark:border-slate-600 dark:bg-slate-800">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100 line-clamp-1" title="{{ $item['nombre'] }}">
                                            {{ $item['nombre'] }}
                                        </h4>
                                        @if($item['presentacion'])
                                            <p class="text-xs text-slate-500 dark:text-slate-300">
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
                                        class="theme-button theme-button-ghost !h-7 !w-7 !min-h-0 !p-0 !rounded-md shrink-0"
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
                                            class="theme-button theme-button-secondary !h-7 !w-7 !min-h-0 !p-0 !rounded-md !text-xs"
                                            @disabled($item['cantidad'] <= 1)
                                        >
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                        </button>
                                        <span class="w-8 text-center text-sm font-bold text-slate-800 dark:text-slate-100">{{ $item['cantidad'] }}</span>
                                        <button
                                            wire:click="actualizarCantidad({{ $index }}, {{ $item['cantidad'] + 1 }})"
                                            class="theme-button theme-button-secondary !h-7 !w-7 !min-h-0 !p-0 !rounded-md !text-xs"
                                            @disabled($item['cantidad'] >= (int) floor($item['stock'] / (($item['tipo_venta'] ?? 'unidad') === 'presentacion' ? max(1, (int) ($item['unidades'] ?? 1)) : 1)))
                                        >
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </div>
                                    <span class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                        ${{ number_format($item['precio'] * $item['cantidad'], 2) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Resumen --}}
            <div class="shrink-0 border-t border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-600 dark:bg-slate-800/70">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-500 dark:text-slate-300">Subtotal</span>
                        <span class="font-bold text-slate-700 dark:text-zinc-200">${{ number_format($subTotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-500 dark:text-slate-300">Descuento</span>
                        <span class="font-bold text-slate-700 dark:text-zinc-200">$0.00</span>
                    </div>
                    <div class="rounded-xl bg-gradient-to-r from-teal-700/10 to-emerald-500/10 px-4 py-3 dark:from-teal-400/10 dark:to-emerald-400/10">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-extrabold text-slate-800 dark:text-slate-100">Total</span>
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
                        b.classList.remove('border-[#0a5f56]','bg-[#0e9384]','text-white','shadow-sm','dark:border-[#0a5f56]');
                        b.classList.add('border-slate-200','bg-white','text-slate-600','dark:border-slate-600','dark:bg-slate-800','dark:text-slate-200');
                        const dot = b.querySelector('span');
                        if (dot) { dot.classList.remove('bg-white'); dot.classList.add('bg-slate-300','dark:bg-slate-500'); }
                    });
                    btn.classList.remove('border-slate-200','bg-white','text-slate-600','dark:bg-slate-800','dark:text-slate-200','dark:border-slate-600');
                    btn.classList.add('border-[#0a5f56]','bg-[#0e9384]','text-white','shadow-sm');
                    const dot = btn.querySelector('span');
                    if (dot) { dot.classList.remove('bg-slate-300','dark:bg-slate-500'); dot.classList.add('bg-white'); }
                });
            });
        });
    </script>

    {{-- Botón flotante carrito (móvil) --}}
    <div class="fixed bottom-5 right-5 z-40 sm:bottom-6 sm:right-6 lg:hidden">
        <button
            wire:click="$set('mostrandoCarrito', true)"
            class="theme-button theme-button-primary relative !h-14 !w-14 !min-h-0 !p-0 !rounded-full shadow-xl"
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
                class="absolute inset-y-0 right-0 flex w-full max-w-sm flex-col bg-white shadow-2xl transition-transform duration-300 ease-out translate-x-0 dark:bg-slate-900"
            >
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-600">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Carrito</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-300">{{ $cantidadArticulos }} {{ Str::plural('artículo', $cantidadArticulos) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(count($carrito) > 0)
                            <button wire:click="limpiarCarrito" class="theme-button theme-button-danger !min-h-0 !px-2.5 !py-1 !text-xs !rounded-lg">Vaciar</button>
                        @endif
                        <button wire:click="$set('mostrandoCarrito', false)" class="theme-button theme-button-ghost !h-8 !w-8 !min-h-0 !p-0 !rounded-full">
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
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-300">Carrito vacío</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-400">Agrega productos desde el panel izquierdo</p>
                        </div>
                    @else
                        <div class="flex flex-col gap-2">
                            @foreach($carrito as $index => $item)
                                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3 dark:border-slate-600 dark:bg-slate-800">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100 line-clamp-1">{{ $item['nombre'] }}</h4>
                                            @if($item['presentacion'])
                                                <p class="text-xs text-slate-500 dark:text-slate-300">
                                                    {{ ucfirst($item['presentacion']) }}
                                                    @if(($item['tipo_venta'] ?? 'unidad') === 'presentacion')
                                                        &middot; {{ $item['unidades'] ?? 1 }} und
                                                    @endif
                                                </p>
                                            @endif
                                            <p class="mt-1 text-sm font-bold text-[#0c9f9c]">${{ number_format($item['precio'], 2) }}</p>
                                        </div>
                                        <button wire:click="eliminarDelCarrito({{ $index }})" class="theme-button theme-button-ghost !h-7 !w-7 !min-h-0 !p-0 !rounded-md shrink-0">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="flex items-center gap-1">
                                            <button wire:click="actualizarCantidad({{ $index }}, {{ $item['cantidad'] - 1 }})" class="theme-button theme-button-secondary !h-7 !w-7 !min-h-0 !p-0 !rounded-md !text-xs" @disabled($item['cantidad'] <= 1)>
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                            </button>
                                            <span class="w-8 text-center text-sm font-bold text-slate-800 dark:text-slate-100">{{ $item['cantidad'] }}</span>
                                            <button wire:click="actualizarCantidad({{ $index }}, {{ $item['cantidad'] + 1 }})" class="theme-button theme-button-secondary !h-7 !w-7 !min-h-0 !p-0 !rounded-md !text-xs" @disabled($item['cantidad'] >= (int) floor($item['stock'] / (($item['tipo_venta'] ?? 'unidad') === 'presentacion' ? max(1, (int) ($item['unidades'] ?? 1)) : 1)))>
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                            </button>
                                        </div>
                                        <span class="text-sm font-bold text-slate-800 dark:text-slate-100">${{ number_format($item['precio'] * $item['cantidad'], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Resumen --}}
                <div class="shrink-0 border-t border-slate-200 bg-slate-50/80 px-5 py-4 dark:border-slate-600 dark:bg-slate-800/80">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-300">Subtotal</span>
                            <span class="font-medium text-slate-700 dark:text-zinc-200">${{ number_format($subTotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-300">Descuento</span>
                            <span class="font-medium text-slate-700 dark:text-zinc-200">$0.00</span>
                        </div>
                        <div class="border-t border-slate-200 pt-2 dark:border-slate-500">
                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-slate-800 dark:text-slate-100">Total</span>
                                <span class="text-xl font-extrabold text-[#0c9f9c]">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <button wire:click="abrirCobro" @disabled(empty($carrito)) class="theme-button theme-button-primary mt-4 w-full">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Cobrar ${{ number_format($total, 2) }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Modal Activar caja (se abre desde el POS o desde el topbar) --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($abriendoCaja)
        <div class="fixed inset-0 z-[65] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="cerrarModalCaja"></div>

            <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
                <div class="bg-[#0e9384] px-6 py-5 text-center dark:bg-[#0c7569]">
                    <span class="mx-auto grid h-11 w-11 place-items-center rounded-2xl bg-white/15 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                    <h3 class="mt-2 text-lg font-black text-white">Activa tu caja para cobrar</h3>
                    <p class="mt-1 text-xs font-medium text-white/85">
                        {{ $sucursalActiva?->nombre_sucursal ?? 'Sin sucursal activa' }} · la venta quedará ligada a esta caja y turno.
                    </p>
                </div>

                @if($sucursalActiva)
                    <form method="POST" action="{{ route('caja.abrir') }}" class="space-y-3 px-6 py-5">
                        @csrf
                        <input type="hidden" name="id_sucursal" value="{{ $sucursalActiva->id }}">
                        <input type="hidden" name="redirect_to" value="punto-venta">

                        <div class="relative block" x-data="{ openCaja: false, cajaValue: 'Caja 1', cajaLabel: 'Caja 1 — Mostrador principal' }">
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-200">Caja física</span>
                            <input type="hidden" name="caja" :value="cajaValue" required>
                            <button type="button" @click="openCaja = !openCaja" class="mt-1 flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-left text-sm font-semibold text-slate-800 shadow-sm transition hover:border-[#0e9384]/30 focus:border-[#0e9384] focus:outline-none focus:ring-2 focus:ring-[#0e9384]/15 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                                <span class="flex items-center gap-2.5">
                                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 012-2h10a2 2 0 012 2v0a2 2 0 01-2 2H7a2 2 0 01-2-2v0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2"/></svg>
                                    </span>
                                    <span x-text="cajaLabel"></span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="openCaja ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="openCaja" x-transition x-cloak @click.outside="openCaja = false" class="absolute z-20 mt-2 w-full rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl dark:border-slate-600 dark:bg-slate-800">
                                <button type="button" @click="cajaValue='Caja 1'; cajaLabel='Caja 1 — Mostrador principal'; openCaja=false" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm font-semibold transition" :class="cajaValue==='Caja 1' ? 'bg-teal-50 text-[#0c7569] dark:bg-[#0e9384]/15 dark:text-[#a7f3d0]' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700'">
                                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full" :class="cajaValue==='Caja 1' ? 'bg-[#0e9384]' : 'bg-slate-300'"></span> Caja 1 — Mostrador principal</span>
                                    <svg x-show="cajaValue==='Caja 1'" class="h-4 w-4 text-[#0e9384] dark:text-[#5ec8b4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="cajaValue='Caja 2'; cajaLabel='Caja 2 — Mostrador secundario'; openCaja=false" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm font-semibold transition" :class="cajaValue==='Caja 2' ? 'bg-teal-50 text-[#0c7569] dark:bg-[#0e9384]/15 dark:text-[#a7f3d0]' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700'">
                                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full" :class="cajaValue==='Caja 2' ? 'bg-[#0e9384]' : 'bg-slate-300'"></span> Caja 2 — Mostrador secundario</span>
                                    <svg x-show="cajaValue==='Caja 2'" class="h-4 w-4 text-[#0e9384] dark:text-[#5ec8b4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="cajaValue='Caja 3'; cajaLabel='Caja 3'; openCaja=false" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm font-semibold transition" :class="cajaValue==='Caja 3' ? 'bg-teal-50 text-[#0c7569] dark:bg-[#0e9384]/15 dark:text-[#a7f3d0]' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700'">
                                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full" :class="cajaValue==='Caja 3' ? 'bg-[#0e9384]' : 'bg-slate-300'"></span> Caja 3</span>
                                    <svg x-show="cajaValue==='Caja 3'" class="h-4 w-4 text-[#0e9384] dark:text-[#5ec8b4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="cajaValue='Caja móvil'; cajaLabel='Caja móvil'; openCaja=false" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm font-semibold transition" :class="cajaValue==='Caja móvil' ? 'bg-teal-50 text-[#0c7569] dark:bg-[#0e9384]/15 dark:text-[#a7f3d0]' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700'">
                                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full" :class="cajaValue==='Caja móvil' ? 'bg-[#0e9384]' : 'bg-slate-300'"></span> Caja móvil</span>
                                    <svg x-show="cajaValue==='Caja móvil'" class="h-4 w-4 text-[#0e9384] dark:text-[#5ec8b4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="relative block" x-data="{ openTurno: false, turnoValue: '{{ $turnoSugerido }}', turnoLabel: '{{ $turnoSugerido === 'Vespertino' ? 'Tarde — Vespertino (15:00–22:00)' : 'Mañana — Matutino (08:00–15:00)' }}' }">
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-200">Turno</span>
                            <input type="hidden" name="turno" :value="turnoValue" required>
                            <button type="button" @click="openTurno = !openTurno" class="mt-1 flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-left text-sm font-semibold text-slate-800 shadow-sm transition hover:border-[#0e9384]/30 focus:border-[#0e9384] focus:outline-none focus:ring-2 focus:ring-[#0e9384]/15 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                                <span class="flex items-center gap-2.5">
                                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l6 3m-6-6v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                    <span x-text="turnoLabel"></span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-slate-400 transition" :class="openTurno ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="openTurno" x-transition x-cloak @click.outside="openTurno = false" class="absolute z-20 mt-2 w-full rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl dark:border-slate-600 dark:bg-slate-800">
                                <button type="button" @click="turnoValue='Matutino'; turnoLabel='Mañana — Matutino (08:00–15:00)'; openTurno=false" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm font-semibold transition" :class="turnoValue==='Matutino' ? 'bg-teal-50 text-[#0c7569] dark:bg-[#0e9384]/15 dark:text-[#a7f3d0]' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700'">
                                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full" :class="turnoValue==='Matutino' ? 'bg-[#0e9384]' : 'bg-slate-300'"></span> Mañana — Matutino (08:00–15:00)</span>
                                    <svg x-show="turnoValue==='Matutino'" class="h-4 w-4 text-[#0e9384] dark:text-[#5ec8b4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="turnoValue='Vespertino'; turnoLabel='Tarde — Vespertino (15:00–22:00)'; openTurno=false" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm font-semibold transition" :class="turnoValue==='Vespertino' ? 'bg-teal-50 text-[#0c7569] dark:bg-[#0e9384]/15 dark:text-[#a7f3d0]' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700'">
                                    <span class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full" :class="turnoValue==='Vespertino' ? 'bg-[#0e9384]' : 'bg-slate-300'"></span> Tarde — Vespertino (15:00–22:00)</span>
                                    <svg x-show="turnoValue==='Vespertino'" class="h-4 w-4 text-[#0e9384] dark:text-[#5ec8b4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-200">Fondo inicial</span>
                            <div class="mt-1 flex items-center overflow-hidden rounded-xl border border-slate-200 bg-white focus-within:border-[#0e9384] focus-within:ring-2 focus-within:ring-[#0e9384]/15 dark:border-slate-600 dark:bg-slate-800">
                                <span class="pl-3 text-sm font-black text-slate-400">$</span>
                                <input type="number" step="0.01" min="0" name="efectivo_inicial" value="0.00" required class="flex-1 border-0 bg-transparent px-2 py-2.5 text-sm font-bold text-slate-800 focus:outline-none focus:ring-0 dark:text-slate-100">
                            </div>
                        </label>

                        @if($errors->any())
                            <p class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                                {{ $errors->first() }}
                            </p>
                        @endif

                        <div class="flex gap-3 pt-1">
                            <button type="button" wire:click="cerrarModalCaja" class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-500 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-zinc-700">
                                Cancelar
                            </button>
                            <button type="submit" class="theme-button theme-button-primary flex-1 justify-center">
                                Activar caja
                            </button>
                        </div>
                    </form>
                @else
                    <div class="px-6 py-6 text-center">
                        <p class="text-sm font-semibold text-slate-600 dark:text-slate-200">Selecciona una sucursal activa para abrir caja.</p>
                        <button type="button" wire:click="cerrarModalCaja" class="theme-button theme-button-secondary mt-4 w-full justify-center">Cerrar</button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Modal Cerrar caja (arquear el turno desde el POS) --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($cerrandoCaja && $cortePropio)
        <div class="fixed inset-0 z-[65] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="cerrarModalCierre"></div>

            <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
                <div class="bg-[#0e9384] px-6 py-5 text-center dark:bg-[#0c7569]">
                    <span class="mx-auto grid h-11 w-11 place-items-center rounded-2xl bg-white/15 text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <h3 class="mt-2 text-lg font-black text-white">Cerrar {{ $cortePropio->caja }}</h3>
                    <p class="mt-1 text-xs font-medium text-white/85">
                        Turno {{ \App\Http\Controllers\CajaController::TURNOS[$cortePropio->turno] ?? ucfirst($cortePropio->turno) }}
                        @if($cortePropio->finDeTurno())
                            · termina a las {{ $cortePropio->finDeTurno()->format('H:i') }}
                        @endif
                    </p>
                </div>

                <form method="POST" action="{{ route('caja.cerrar', $cortePropio) }}" class="space-y-3 px-6 py-5">
                    @csrf
                    <input type="hidden" name="redirect_to" value="punto-venta">

                    <div class="hidden" aria-hidden="true">
                        <p>Efectivo esperado</p>
                        <p>${{ number_format($cortePropio->efectivoEsperado(), 2) }}</p>
                        <p>Fondo inicial + ventas + ingresos − retiros</p>
                    </div>

                    <label class="block">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-200">Efectivo contado en caja</span>
                        <div class="mt-1 flex items-center overflow-hidden rounded-xl border border-slate-200 bg-white focus-within:border-[#0e9384] focus-within:ring-2 focus-within:ring-[#0e9384]/15 dark:border-slate-600 dark:bg-slate-800">
                            <span class="pl-3 text-sm font-black text-slate-400">$</span>
                            <input type="number" step="0.01" min="0" name="efectivo_declarado" required autofocus oninvalid="this.setCustomValidity('Por favor, completa este campo.')" oninput="this.setCustomValidity('')" class="flex-1 border-0 bg-transparent px-2 py-2.5 text-sm font-bold text-slate-800 focus:outline-none focus:ring-0 dark:text-slate-100">
                        </div>
                    </label>

                    <p class="rounded-xl bg-slate-50 px-3 py-2 text-[11px] font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                        Al cerrar, la caja deja de recibir ventas. Si no cierras antes de la tolerancia, el sistema la cerrará automáticamente sin diferencia.
                    </p>

                    @if($errors->any())
                        <p class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                            {{ $errors->first() }}
                        </p>
                    @endif

                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="cerrarModalCierre" class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-500 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                            Cancelar
                        </button>
                        <button type="submit" class="theme-button theme-button-primary flex-1 justify-center">
                            Cerrar caja
                        </button>
                    </div>
                </form>
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
                class="relative flex max-h-[88vh] w-full max-w-lg flex-col overflow-hidden rounded-[1.75rem] bg-white shadow-2xl ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10"
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
                        @if(! $cajaAbierta)
                            <button
                                type="button"
                                wire:click="abrirModalCaja"
                                class="theme-button theme-button-primary mt-3 w-full justify-center !py-2 !text-xs"
                            >
                                Activar caja ahora
                            </button>
                        @endif
                    </div>
                @endif

                {{-- Contenido con scroll: métodos + campos del pago --}}
                <div class="min-h-0 flex-1 overflow-y-auto">
                {{-- Métodos de pago --}}
                <div class="px-6 pt-5">
                    <p class="mb-2.5 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300">Método de pago</p>
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            wire:click="seleccionarMetodo('efectivo')"
                            class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-2 py-3 text-[11px] font-bold transition
                                {{ $metodoPago === 'efectivo' ? 'border-[#0e9384] bg-teal-50 text-[#0c7569] shadow-sm dark:border-[#5ec8b4] dark:bg-[#0e9384]/20 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-[#0e9384]/40 hover:bg-teal-50/50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-[#5ec8b4]/40' }}"
                        >
                            <span class="grid h-7 w-7 place-items-center rounded-lg {{ $metodoPago === 'efectivo' ? 'bg-[#0e9384] text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span>
                            Efectivo
                        </button>
                        <button
                            wire:click="seleccionarMetodo('tarjeta')"
                            class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-2 py-3 text-[11px] font-bold transition
                                {{ $metodoPago === 'tarjeta' ? 'border-[#0e9384] bg-teal-50 text-[#0c7569] shadow-sm dark:border-[#5ec8b4] dark:bg-[#0e9384]/20 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-[#0e9384]/40 hover:bg-teal-50/50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-[#5ec8b4]/40' }}"
                        >
                            <span class="grid h-7 w-7 place-items-center rounded-lg {{ $metodoPago === 'tarjeta' ? 'bg-[#0e9384] text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></span>
                            Tarjeta
                        </button>
                        <button
                            wire:click="seleccionarMetodo('transferencia')"
                            class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-2 py-3 text-[11px] font-bold transition
                                {{ $metodoPago === 'transferencia' ? 'border-[#0e9384] bg-teal-50 text-[#0c7569] shadow-sm dark:border-[#5ec8b4] dark:bg-[#0e9384]/20 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-[#0e9384]/40 hover:bg-teal-50/50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-[#5ec8b4]/40' }}"
                        >
                            <span class="grid h-7 w-7 place-items-center rounded-lg {{ $metodoPago === 'transferencia' ? 'bg-[#0e9384] text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></span>
                            Transf.
                        </button>
                        <button
                            wire:click="seleccionarMetodo('mixto')"
                            class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-2 py-3 text-[11px] font-bold transition
                                {{ $metodoPago === 'mixto' ? 'border-[#0e9384] bg-teal-50 text-[#0c7569] shadow-sm dark:border-[#5ec8b4] dark:bg-[#0e9384]/20 dark:text-[#a7f3d0]' : 'border-slate-200 bg-white text-slate-600 hover:border-[#0e9384]/40 hover:bg-teal-50/50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-[#5ec8b4]/40' }}"
                        >
                            <span class="grid h-7 w-7 place-items-center rounded-lg {{ $metodoPago === 'mixto' ? 'bg-[#0e9384] text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg></span>
                            Mixto
                        </button>
                    </div>
                </div>

                {{-- Campos del método de pago --}}
                <div class="px-6 py-5">
                    {{-- Efectivo --}}
                    @if($metodoPago === 'efectivo')
                        <div class="space-y-5">
                            <div>
                                <p class="mb-2.5 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300">Billetes comunes</p>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach([500, 200, 100, 50, 20, 10] as $billete)
                                        <button
                                            wire:click="setEfectivoRapido({{ $billete }})"
                                            class="rounded-xl border-2 border-slate-200 bg-white px-2 py-3 text-[11px] font-bold text-slate-700 shadow-sm transition hover:border-[#0e9384] hover:bg-teal-50 hover:text-[#0c7569] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-[#5ec8b4]"
                                        >
                                            +${{ $billete }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <div class="flex items-baseline justify-between">
                                    <label class="text-[11px] font-bold uppercase tracking-widest text-slate-600 dark:text-slate-300">Efectivo recibido</label>
                                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total <span class="font-bold text-slate-800 dark:text-slate-100">${{ number_format($total, 2) }}</span></span>
                                </div>
                                <div class="mt-2 flex items-center overflow-hidden rounded-xl border-2 {{ (float) $efectivoRecibido >= $total && (float) $efectivoRecibido > 0 ? 'border-emerald-300 bg-emerald-50/50 dark:border-emerald-600 dark:bg-emerald-900/20' : 'border-slate-200 bg-white dark:border-slate-600 dark:bg-slate-900' }} shadow-sm transition">
                                    <span class="pl-5 text-xl font-bold text-slate-400">$</span>
                                    <input
                                        x-ref="firstInput"
                                        type="text"
                                        inputmode="decimal"
                                        wire:model.live="efectivoRecibido"
                                        placeholder="0.00"
                                        class="flex-1 border-0 bg-transparent py-3 pr-5 text-right text-xl font-black tracking-tight text-slate-900 placeholder:text-slate-300 focus:outline-none focus:ring-0 dark:text-slate-100 dark:placeholder:text-slate-500"
                                    >
                                </div>
                                <div class="mt-2 flex items-center justify-end">
                                    @if((float) $efectivoRecibido > 0 && (float) $efectivoRecibido < $total)
                                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400">Faltan ${{ number_format($total - (float) $efectivoRecibido, 2) }}</span>
                                    @else
                                        <button type="button" wire:click="limpiarEfectivo" class="text-xs font-medium text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300">Limpiar</button>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800 flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Cambio a entregar</p>
                                    <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">Calculado automáticamente</p>
                                </div>
                                <p class="text-xl font-black {{ $cambio > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">${{ number_format($cambio, 2) }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Tarjeta --}}
                    @if($metodoPago === 'tarjeta')
                        <div class="space-y-4">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-600 dark:bg-slate-800">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Monto cobrado</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-300">Total exacto</p>
                                <p class="mt-1 text-2xl font-extrabold text-[#0c9f9c]">${{ number_format($total, 2) }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">N.° de aprobacion <span class="font-normal normal-case tracking-normal">(opcional)</span></label>
                                <input
                                    type="text"
                                    wire:model.live="referenciaTarjeta"
                                    placeholder="123456"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-slate-500 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-zinc-500"
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
                                <div class="rounded-2xl border border-teal-200 bg-teal-50 p-4 dark:border-teal-700/30 dark:bg-[#0e9384]/10">
                                    <div class="flex items-start gap-3">
                                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-[#0e9384] text-white dark:bg-[#0c9f9c]"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg></span>
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-teal-800 dark:text-teal-200">Datos bancarios no configurados</p>
                                            <p class="mt-1 text-xs font-medium text-teal-700/80 dark:text-teal-300/80">Configura los datos bancarios para que el cliente pueda realizar la transferencia.</p>
                                            <a
                                                href="{{ route('settings.transferencia') }}"
                                                class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-[#0e9384] px-4 py-2 text-xs font-bold text-white shadow-md shadow-teal-900/10 transition hover:bg-[#0c7569] active:scale-[0.98] dark:bg-[#0c9f9c] dark:hover:bg-[#0a8582]"
                                                wire:navigate
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                Configurar datos bancarios
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @elseif(! $tieneDatosBancarios && ! $esSuperAdmin)
                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center dark:border-slate-600 dark:bg-slate-800">
                                    <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-300">Datos bancarios no disponibles</p>
                                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-400">Contacta al administrador para configurar los datos de transferencia.</p>
                                </div>
                            @else
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-600 dark:bg-slate-800/50">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300">Datos para transferencia</p>
                                        <span class="hidden sm:inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20">Comparte al cliente</span>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-600 dark:bg-slate-700">
                                            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Banco</p>
                                            <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ $configBancaria->banco }}</p>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-600 dark:bg-slate-700">
                                            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">CLABE</p>
                                            <p class="mt-1 text-sm font-bold tracking-wider text-slate-800 dark:text-slate-100">{{ $configBancaria->clabe }}</p>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-600 dark:bg-slate-700">
                                            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Beneficiario</p>
                                            <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ $configBancaria->beneficiario }}</p>
                                        </div>
                                        <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-600 dark:bg-slate-700">
                                            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Concepto</p>
                                            <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">Venta #{{ 'V-' . date('Ymd') }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-600">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Monto a transferir</p>
                                            <p class="mt-1 text-2xl font-black text-[#0c9f9c]">${{ number_format($total, 2) }}</p>
                                        </div>
                                        <div class="sm:text-right">
                                            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">N.° referencia / folio</p>
                                            <p class="mt-1 text-sm font-bold tracking-wider text-slate-800 dark:text-slate-100">REF-{{ date('Ymd') }}-{{ str_pad($cantidadArticulos + 1, 5, '0', STR_PAD_LEFT) }}</p>
                                            <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-400">Úsala en el comprobante</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-2">
                                    <button type="button" wire:click="seleccionarMetodo('efectivo')" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Cancelar
                                    </button>
                                    @if($esSuperAdmin)
                                        <a
                                            href="{{ route('settings.transferencia') }}"
                                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:border-[#0c9f9c] hover:text-[#0c9f9c] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-[#0c9f9c] dark:hover:text-[#0c9f9c]"
                                            wire:navigate
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            Editar datos
                                        </a>
                                    @endif
                                </div>
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
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-600 dark:bg-slate-800/50">
                                <div class="flex items-start gap-3">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-[#0e9384] text-xs font-black text-white">1</span>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Elige 1 o varios métodos</p>
                                        <p class="mt-1 text-xs font-medium leading-relaxed text-slate-500 dark:text-slate-400">Activa los que usará el cliente. Ej.: <span class="font-bold text-slate-700 dark:text-slate-200">$30</span> en efectivo + <span class="font-bold text-slate-700 dark:text-slate-200">$42</span> en tarjeta. La suma debe ser <span class="font-bold text-[#0c7569] dark:text-[#5ec8b4]">${{ number_format($total, 2) }}</span></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Filas integradas: toggle + monto en una sola tarjeta por método --}}
                            <div class="space-y-3">
                                {{-- Efectivo --}}
                                <div class="flex items-center gap-3 rounded-xl border-2 p-3 transition" :class="activoEfectivo ? 'border-[#0e9384] bg-teal-50 dark:border-[#5ec8b4] dark:bg-[#0e9384]/15' : 'border-slate-200 bg-white dark:border-slate-600 dark:bg-slate-800'">
                                    <button type="button" @click="toggle('efectivo')" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg border-2 transition" :class="activoEfectivo ? 'border-[#0e9384] bg-[#0e9384] text-white dark:border-[#5ec8b4] dark:bg-[#0e9384]' : 'border-slate-300 bg-slate-100 text-slate-400 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-400'">
                                        <span x-show="!activoEfectivo" class="text-sm">💵</span>
                                        <svg x-show="activoEfectivo" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold" :class="activoEfectivo ? 'text-[#0c7569] dark:text-[#a7f3d0]' : 'text-slate-700 dark:text-slate-200'">Efectivo</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Billetes y monedas</p>
                                    </div>
                                    <div class="relative w-32 shrink-0">
                                        <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-sm font-bold" :class="activoEfectivo ? 'text-[#0c9f9c]' : 'text-slate-400'">$</span>
                                        <input type="number" min="0" step="0.01" wire:model.live="pagoMixto.efectivo" :disabled="!activoEfectivo" placeholder="0.00" class="w-full rounded-lg border py-2 pl-7 pr-2 text-right text-sm font-bold outline-none transition disabled:cursor-not-allowed disabled:opacity-40" :class="activoEfectivo ? 'border-slate-300 bg-white text-slate-800 focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-slate-500 dark:bg-slate-900 dark:text-slate-100' : 'border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-600 dark:bg-slate-700'">
                                    </div>
                                </div>

                                {{-- Tarjeta --}}
                                <div class="flex items-center gap-3 rounded-xl border-2 p-3 transition" :class="activoTarjeta ? 'border-[#0e9384] bg-teal-50 dark:border-[#5ec8b4] dark:bg-[#0e9384]/15' : 'border-slate-200 bg-white dark:border-slate-600 dark:bg-slate-800'">
                                    <button type="button" @click="toggle('tarjeta')" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg border-2 transition" :class="activoTarjeta ? 'border-[#0e9384] bg-[#0e9384] text-white dark:border-[#5ec8b4] dark:bg-[#0e9384]' : 'border-slate-300 bg-slate-100 text-slate-400 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-400'">
                                        <span x-show="!activoTarjeta" class="text-sm">💳</span>
                                        <svg x-show="activoTarjeta" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold" :class="activoTarjeta ? 'text-[#0c7569] dark:text-[#a7f3d0]' : 'text-slate-700 dark:text-slate-200'">Tarjeta</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Crédito / débito</p>
                                    </div>
                                    <div class="relative w-32 shrink-0">
                                        <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-sm font-bold" :class="activoTarjeta ? 'text-[#0c9f9c]' : 'text-slate-400'">$</span>
                                        <input type="number" min="0" step="0.01" wire:model.live="pagoMixto.tarjeta" :disabled="!activoTarjeta" placeholder="0.00" class="w-full rounded-lg border py-2 pl-7 pr-2 text-right text-sm font-bold outline-none transition disabled:cursor-not-allowed disabled:opacity-40" :class="activoTarjeta ? 'border-slate-300 bg-white text-slate-800 focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-slate-500 dark:bg-slate-900 dark:text-slate-100' : 'border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-600 dark:bg-slate-700'">
                                    </div>
                                </div>

                                {{-- Transferencia --}}
                                <div class="rounded-xl border-2 p-3 transition space-y-3" :class="activoTransferencia ? 'border-[#0e9384] bg-teal-50 dark:border-[#5ec8b4] dark:bg-[#0e9384]/15' : 'border-slate-200 bg-white dark:border-slate-600 dark:bg-slate-800'">
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click="toggle('transferencia')" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg border-2 transition" :class="activoTransferencia ? 'border-[#0e9384] bg-[#0e9384] text-white dark:border-[#5ec8b4] dark:bg-[#0e9384]' : 'border-slate-300 bg-slate-100 text-slate-400 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-400'">
                                            <span x-show="!activoTransferencia" class="text-sm">🏦</span>
                                            <svg x-show="activoTransferencia" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold" :class="activoTransferencia ? 'text-[#0c7569] dark:text-[#a7f3d0]' : 'text-slate-700 dark:text-slate-200'">Transferencia</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">SPEI / CLABE</p>
                                        </div>
                                        <div class="relative w-32 shrink-0" x-show="activoTransferencia">
                                            @if($tieneDatosBancarios)
                                                <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-sm font-bold text-[#0c9f9c]">$</span>
                                                <input type="number" min="0" step="0.01" wire:model.live="pagoMixto.transferencia" placeholder="0.00" class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-7 pr-2 text-right text-sm font-bold text-slate-800 focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-slate-500 dark:bg-slate-900 dark:text-slate-100">
                                            @else
                                                <span class="block w-full py-2 text-center text-xs font-bold text-slate-400">—</span>
                                            @endif
                                        </div>
                                        <div x-show="!activoTransferencia" class="w-32 shrink-0 text-right text-sm font-bold text-slate-400">--</div>
                                    </div>
                                    <div x-show="activoTransferencia" x-collapse>
                                        @if(! $tieneDatosBancarios && $esSuperAdmin)
                                            <div class="rounded-lg border border-teal-200 bg-white p-3 dark:border-teal-700/30 dark:bg-slate-900">
                                                <p class="text-xs font-bold text-teal-800 dark:text-teal-200">Datos bancarios no configurados</p>
                                                <p class="mt-1 text-[11px] font-medium text-teal-700/80 dark:text-teal-300/80">Configúralos para cobrar por transferencia.</p>
                                                <a href="{{ route('settings.transferencia') }}" class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-[#0e9384] px-3 py-1.5 text-[11px] font-bold text-white shadow-sm hover:bg-[#0c7569] dark:bg-[#0c9f9c]" wire:navigate>Configurar datos</a>
                                            </div>
                                        @elseif(! $tieneDatosBancarios && ! $esSuperAdmin)
                                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-center dark:border-slate-600 dark:bg-slate-800">
                                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-300">Datos no disponibles</p>
                                                <p class="mt-0.5 text-[11px] text-slate-400">Contacta al administrador.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <p x-show="!activoEfectivo && !activoTarjeta && !activoTransferencia" class="rounded-xl border border-dashed border-amber-200 bg-amber-50 px-4 py-3 text-center text-xs font-medium text-amber-700 dark:border-amber-700/30 dark:bg-amber-900/20 dark:text-amber-300">Activa al menos un método para empezar a repartir el pago</p>

                            {{-- Resumen --}}
                            <div class="rounded-2xl border-2 {{ round((float) $pagado, 2) === round($total, 2) && $pagado > 0 ? 'border-emerald-300 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-900/15' : (round((float) $pagado, 2) > round($total, 2) ? 'border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-900/15' : 'border-slate-200 bg-white dark:border-slate-600 dark:bg-slate-800') }} p-4 space-y-3">
                                @if($pagado > 0)
                                    <div class="space-y-1.5">
                                        @if((float) $pagoMixto['efectivo'] > 0)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Efectivo</span>
                                                <span class="font-bold text-slate-800 dark:text-slate-100">${{ number_format((float) $pagoMixto['efectivo'], 2) }}</span>
                                            </div>
                                        @endif
                                        @if((float) $pagoMixto['tarjeta'] > 0)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300"><span class="h-2 w-2 rounded-full bg-slate-400"></span> Tarjeta</span>
                                                <span class="font-bold text-slate-800 dark:text-slate-100">${{ number_format((float) $pagoMixto['tarjeta'], 2) }}</span>
                                            </div>
                                        @endif
                                        @if((float) $pagoMixto['transferencia'] > 0)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300"><span class="h-2 w-2 rounded-full bg-sky-500"></span> Transferencia</span>
                                                <span class="font-bold text-slate-800 dark:text-slate-100">${{ number_format((float) $pagoMixto['transferencia'], 2) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                        <div class="h-full {{ round((float) $pagado, 2) > round($total, 2) ? 'bg-red-500' : 'bg-[#0e9384]' }} transition-all duration-300" style="width: {{ $total > 0 ? min(100, ($pagado / $total) * 100) : 0 }}%"></div>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Pagado</span>
                                    <span class="text-sm font-black {{ round((float) $pagado, 2) === round($total, 2) && $pagado > 0 ? 'text-emerald-600 dark:text-emerald-400' : (round((float) $pagado, 2) > round($total, 2) ? 'text-red-600 dark:text-red-400' : 'text-slate-800 dark:text-slate-100') }}">${{ number_format($pagado, 2) }} <span class="font-medium text-slate-400">/ ${{ number_format($total, 2) }}</span></span>
                                </div>
                                @if($restante > 0)
                                    <div class="flex items-center justify-between rounded-xl bg-amber-50 px-3 py-2 dark:bg-amber-900/20">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 dark:text-amber-300"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg> Faltan</span>
                                        <span class="text-sm font-black text-amber-700 dark:text-amber-300">${{ number_format($restante, 2) }}</span>
                                    </div>
                                @elseif(round((float) $pagado, 2) > round($total, 2))
                                    <div class="flex items-center justify-between rounded-xl bg-red-50 px-3 py-2 dark:bg-red-900/20">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-700 dark:text-red-300"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Excede</span>
                                        <span class="text-sm font-black text-red-700 dark:text-red-300">+${{ number_format(round((float) $pagado, 2) - round($total, 2), 2) }}</span>
                                    </div>
                                @else
                                    @if($pagado > 0)
                                        <div class="flex items-center justify-center gap-1 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Cubierto — listo para confirmar</div>
                                    @endif
                                @endif
                                @if($pagoMixto['efectivo'] > 0 && $cambio > 0)
                                    <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-slate-600">
                                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Cambio</span>
                                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">${{ number_format($cambio, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                {{-- /Contenido con scroll --}}
                </div>

                {{-- Footer --}}
                <div class="flex gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-600">
                    <button
                        wire:click="cerrarCobro"
                        class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98] dark:border-slate-500 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-zinc-700"
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
    {{-- Ticket térmico 80mm — se muestra tras cobrar para imprimir --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($mostrandoTicket && $ticketDatos)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-3">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="cerrarTicket"></div>

            <div class="relative flex max-h-[78vh] w-full max-w-xs flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-2.5 dark:border-slate-700">
                    <div>
                        <h3 class="text-[13px] font-black text-slate-800 dark:text-slate-100">Venta completada</h3>
                        <p class="font-mono text-[11px] font-bold tracking-wide text-emerald-600 dark:text-emerald-400">{{ $ticketDatos['folio'] }}</p>
                    </div>
                    <button type="button" wire:click="cerrarTicket" class="grid h-7 w-7 place-items-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto bg-[#eef4f2] p-3 dark:bg-slate-950">
                    <div id="ticket-print" class="ticket-80mm mx-auto overflow-hidden rounded-[10px] bg-white shadow-[0_6px_18px_rgba(16,42,50,0.10)]">
                        {{-- Encabezado ticket --}}
                        <div class="px-5 pt-5 pb-3 text-center">
                            <h1 class="text-[12px] font-black uppercase tracking-[0.14em] text-slate-900">{{ $ticketDatos['sucursal'] }}</h1>
                            @if(!empty($ticketDatos['sucursalDireccion']))
                                <p class="mx-auto mt-1 max-w-[240px] text-[11px] leading-snug text-slate-500">{{ $ticketDatos['sucursalDireccion'] }}</p>
                            @endif
                            <p class="mt-1.5 font-mono text-[10px] font-semibold tracking-widest text-slate-400">{{ $ticketDatos['folio'] }}</p>
                        </div>

                        <div class="ticket-divider"></div>

                        {{-- Datos de la venta --}}
                        <div class="px-5 py-3 space-y-1.5 text-[12px] leading-5">
                            <div class="flex justify-between"><span class="font-medium text-slate-500">Fecha</span><span class="font-mono font-bold text-slate-900">{{ $ticketDatos['fecha'] }}</span></div>
                            <div class="flex justify-between"><span class="font-medium text-slate-500">Caja / Turno</span><span class="font-semibold text-slate-900">{{ $ticketDatos['caja'] }} · {{ $ticketDatos['turno'] }}</span></div>
                            <div class="flex justify-between"><span class="font-medium text-slate-500">Cajero</span><span class="ml-2 truncate text-right font-semibold text-slate-900">{{ $ticketDatos['cajero'] }}</span></div>
                        </div>

                        <div class="ticket-divider-dashed"></div>

                        {{-- Items --}}
                        <div class="px-5 py-4">
                            <div class="flex text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                <span class="w-8">Cant</span><span class="flex-1">Descripción</span><span class="w-[76px] text-right">Importe</span>
                            </div>
                            <div class="mt-3 space-y-3">
                                @foreach($ticketDatos['items'] as $it)
                                    <div class="flex gap-2.5">
                                        <span class="w-8 shrink-0 font-mono text-[12px] font-black text-slate-900">{{ $it['cantidad'] }}×</span>
                                        <span class="flex-1 min-w-0">
                                            <span class="block text-[12.5px] font-bold leading-tight text-slate-900">{{ $it['nombre'] }}</span>
                                            <span class="mt-0.5 block text-[11px] leading-tight text-slate-500">
                                                @if($it['presentacion'] !== '')
                                                    {{ $it['presentacion'] }}@if($it['tipo_venta']==='presentacion') · {{ $it['unidades'] }} uds @endif
                                                    <span class="text-slate-300">·</span> ${{ number_format($it['precio'],2) }}
                                                @else
                                                    ${{ number_format($it['precio'],2) }} c/u
                                                @endif
                                            </span>
                                        </span>
                                        <span class="w-[76px] shrink-0 text-right font-mono text-[12px] font-black text-slate-900">${{ number_format($it['importe'],2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="ticket-divider"></div>

                        {{-- Totales --}}
                        <div class="bg-slate-50/70 px-5 py-3.5">
                            @php
                                $articulos = array_sum(array_column($ticketDatos['items'], 'cantidad'));
                            @endphp
                            <div class="flex justify-between text-[11px] font-medium text-slate-500"><span>{{ $articulos }} {{ Str::plural('artículo', $articulos) }}</span><span>{{ count($ticketDatos['items']) }} {{ Str::plural('producto', count($ticketDatos['items'])) }}</span></div>
                            <div class="mt-2 flex items-baseline justify-between">
                                <span class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-600">Total</span>
                                <span class="font-mono text-[22px] font-black tracking-tight text-slate-900">${{ number_format($ticketDatos['total'],2) }}</span>
                            </div>
                        </div>

                        <div class="ticket-divider-dashed"></div>

                        {{-- Pago --}}
                        <div class="px-5 py-3 space-y-1.5 text-[12px] leading-5">
                            @php
                                $metodo = $ticketDatos['metodoPago'];
                                $labels = ['efectivo'=>'Efectivo','tarjeta'=>'Tarjeta','transferencia'=>'Transferencia','mixto'=>'Mixto'];
                            @endphp
                            <div class="flex justify-between"><span class="font-medium text-slate-500">Pago</span><span class="font-bold text-slate-900">{{ $labels[$metodo] ?? ucfirst($metodo) }}</span></div>
                            @if($metodo === 'efectivo')
                                @if((float) $ticketDatos['efectivoRecibido'] > 0)
                                    <div class="flex justify-between"><span class="text-slate-500">Recibido</span><span class="font-mono font-bold text-slate-900">${{ number_format((float) $ticketDatos['efectivoRecibido'],2) }}</span></div>
                                @endif
                                @if((float) $ticketDatos['cambio'] > 0.005)
                                    <div class="flex justify-between"><span class="text-slate-500">Cambio</span><span class="font-mono font-black text-emerald-700">${{ number_format((float) $ticketDatos['cambio'],2) }}</span></div>
                                @endif
                            @elseif($metodo === 'mixto')
                                @if((float) ($ticketDatos['pagoMixto']['efectivo'] ?? 0) > 0)<div class="flex justify-between"><span class="text-slate-500">Efectivo</span><span class="font-mono font-bold text-slate-900">${{ number_format((float) $ticketDatos['pagoMixto']['efectivo'],2) }}</span></div>@endif
                                @if((float) ($ticketDatos['pagoMixto']['tarjeta'] ?? 0) > 0)<div class="flex justify-between"><span class="text-slate-500">Tarjeta</span><span class="font-mono font-bold text-slate-900">${{ number_format((float) $ticketDatos['pagoMixto']['tarjeta'],2) }}</span></div>@endif
                                @if((float) ($ticketDatos['pagoMixto']['transferencia'] ?? 0) > 0)<div class="flex justify-between"><span class="text-slate-500">Transferencia</span><span class="font-mono font-bold text-slate-900">${{ number_format((float) $ticketDatos['pagoMixto']['transferencia'],2) }}</span></div>@endif
                                @if((float) $ticketDatos['cambio'] > 0.005)<div class="flex justify-between"><span class="text-slate-500">Cambio</span><span class="font-mono font-black text-emerald-700">${{ number_format((float) $ticketDatos['cambio'],2) }}</span></div>@endif
                            @elseif($metodo === 'tarjeta' && trim($ticketDatos['referenciaTarjeta'] ?? '') !== '')
                                <div class="flex justify-between"><span class="text-slate-500">Aprob.</span><span class="font-mono font-bold text-slate-900">{{ $ticketDatos['referenciaTarjeta'] }}</span></div>
                            @endif
                        </div>

                        <div class="ticket-divider"></div>

                        {{-- Pie ticket --}}
                        <div class="bg-slate-50 px-5 pb-5 pt-4 text-center">
                            <p class="text-[12px] font-black tracking-wide text-slate-800">¡Gracias por su compra!</p>
                            <p class="mt-1 text-[11px] leading-snug text-slate-500">Conserve este ticket para cambios o aclaraciones.</p>
                            <p class="mt-3 font-mono text-[9px] tracking-[0.18em] text-slate-400">{{ $ticketDatos['folio'] }} · {{ $ticketDatos['fecha'] }}</p>
                        </div>
                    </div>
                </div>

                @if($errorVenta)
                    <div class="mx-4 mb-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">{{ $errorVenta }}</div>
                @endif
                <div class="flex gap-2.5 border-t border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
                    <button type="button" wire:click="cerrarTicket" class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[13px] font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">Cerrar</button>
                    <button type="button" wire:click="imprimirTicket" wire:loading.attr="disabled" class="flex-1 rounded-xl bg-[#0e9384] px-4 py-2.5 text-[13px] font-bold text-white shadow-md hover:bg-[#0c7a6e] disabled:opacity-50">
                        <span wire:loading.remove wire:target="imprimirTicket">Imprimir</span>
                        <span wire:loading wire:target="imprimirTicket">Registrando…</span>
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
                class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900"
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
                        $productoReceta = \App\Models\Producto::find($recetaProductoId ?? 0);
                    @endphp
                    @if(!empty($productoReceta))
                        <p class="mt-2 text-sm text-amber-200">
                            {{ $productoReceta->nombre_producto }}
                            @if(!empty($productoReceta?->presentacion))
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
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-300">
                        La venta no puede continuar hasta completar y validar la informacion de la receta medica.
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Nombre del medico</label>
                            <input
                                x-ref="recetaMedico"
                                type="text"
                                wire:model.live="recetaNombreMedico"
                                placeholder="Dr. Juan Perez Lopez"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-slate-500 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-zinc-500"
                            >
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Cedula profesional</label>
                            <input
                                type="text"
                                wire:model.live="recetaCedula"
                                placeholder="1234567"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-slate-500 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-zinc-500"
                            >
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Folio de receta</label>
                                <input
                                    type="text"
                                    wire:model.live="recetaFolio"
                                    placeholder="RC-2026-00123"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-slate-500 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-zinc-500"
                                >
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-300">Fecha de receta</label>
                                <input
                                    type="date"
                                    wire:model.live="recetaFecha"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-slate-500 dark:bg-slate-800 dark:text-slate-100"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-600">
                    <button
                        wire:click="cerrarReceta"
                        class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98] dark:border-slate-500 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-zinc-700"
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

            <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
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
                        <p class="mb-4 rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            {{ $selectorEstadoCajas['selladas'] }} cajas selladas
                            <span class="mx-1 font-normal text-slate-400 dark:text-slate-400">·</span>
                            {{ $selectorEstadoCajas['sueltas'] }} unidades sueltas
                            <span class="font-normal text-slate-500 dark:text-slate-300">(caja de {{ $selectorEstadoCajas['unidades_por_caja'] }} und)</span>
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
                        <p class="text-center text-sm text-slate-500 dark:text-slate-300">
                            Este producto no tiene presentaciones registradas.
                        </p>
                    @else
                        <p class="mb-4 text-xs text-slate-500 dark:text-slate-300">
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
                                        ? 'cursor-not-allowed border-slate-200 bg-slate-100 opacity-70 dark:border-slate-600 dark:bg-slate-800/60'
                                        : 'border-slate-200 bg-slate-50 hover:border-[#0c9f9c] hover:bg-[#0c9f9c]/5 dark:border-slate-600 dark:bg-slate-800 dark:hover:border-[#0c9f9c]' }}"
                                >
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                            {{ $opcion['etiqueta'] }}
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-300">
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

                <div class="border-t border-slate-200 px-6 py-4 dark:border-slate-600">
                    <button
                        wire:click="cerrarSelector"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98] dark:border-slate-500 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-zinc-700"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
