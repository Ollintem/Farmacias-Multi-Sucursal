<div
    class="-m-6 flex h-screen flex-col overflow-hidden rounded-none border-0 bg-[rgba(245,246,243,0.96)] p-0 dark:bg-[rgba(15,23,42,0.78)] lg:-m-8"
    x-data="{ successFolio: null }"
    @venta-completada.window="successFolio = $event.detail.folio; setTimeout(() => successFolio = null, 4000)"
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
            class="fixed right-4 top-4 z-[100] rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 shadow-lg dark:border-emerald-800 dark:bg-emerald-900/90"
        >
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-200">Venta registrada: <span x-text="successFolio"></span></span>
            </div>
        </div>
    </template>

    {{-- Barra de búsqueda --}}
    <div class="shrink-0 border-b border-slate-200 bg-white/80 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900/80">
        <div class="flex items-center gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="busqueda"
                        placeholder="Buscar producto o código de barras..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                    >
            </div>
        </div>
    </div>

    {{-- Filtros por presentación --}}
    <div class="shrink-0 border-b border-slate-100 bg-white/50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <button
                wire:click="$set('filtroPresentacion', '')"
                class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold transition
                    {{ $filtroPresentacion === '' ? 'bg-[#0c9f9c] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700' }}"
            >
                Todos
            </button>
            @foreach($presentaciones as $presentacion)
                <button
                    wire:click="$set('filtroPresentacion', '{{ $presentacion->id }}')"
                    class="shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold transition
                        {{ $filtroPresentacion == $presentacion->id ? 'bg-[#0c9f9c] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700' }}"
                >
                    {{ ucfirst($presentacion->presentacion) }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Área de productos + carrito --}}
    <div class="flex min-h-0 flex-1">
        {{-- Grid de productos --}}
        <div class="flex-1 overflow-y-auto p-5">
            @if($productos->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">No se encontraron productos</p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach($productos as $producto)
                        <div class="group relative flex flex-col rounded-xl border border-slate-200 bg-white transition hover:border-[#0c9f9c]/40 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-[#0c9f9c]/40">
                            @if($producto->es_controlado)
                                <span class="absolute left-2 top-2 z-10 rounded-md bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-sm">RX</span>
                            @endif

                            <div class="flex h-28 items-center justify-center rounded-t-xl bg-gradient-to-br from-slate-50 to-slate-100 dark:from-zinc-700 dark:to-zinc-800">
                                <svg class="h-12 w-12 text-[#0c9f9c]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>

                            <div class="flex flex-1 flex-col p-3">
                                <h3 class="text-sm font-bold leading-tight text-slate-800 dark:text-white line-clamp-2" title="{{ $producto->nombre_producto }}">
                                    {{ $producto->nombre_producto }}
                                </h3>

                                @if($producto->presentacion)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                                        {{ ucfirst($producto->presentacion->presentacion) }}
                                    </p>
                                @endif

                                <div class="mt-auto pt-2">
                                    <div class="flex items-end justify-between">
                                        <span class="text-lg font-extrabold text-[#0c9f9c]">
                                            ${{ number_format($producto->precio, 2) }}
                                        </span>

                                        <button
                                            wire:click="agregarAlCarrito({{ $producto->id }})"
                                            @disabled($producto->stock <= 0)
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#0c9f9c] text-white shadow-sm transition hover:bg-[#0a8582] active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                                            title="Agregar al carrito"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <p class="mt-1.5 text-xs {{ $producto->stock <= 15 ? 'font-semibold text-amber-600 dark:text-amber-400' : 'text-slate-500 dark:text-zinc-400' }}">
                                        {{ $producto->stock }} disp.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Panel derecho: Carrito (desktop) --}}
        <div class="hidden w-80 shrink-0 flex-col border-l border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 lg:flex xl:w-96">
            {{-- Header del carrito --}}
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-zinc-700">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-white">Carrito</h2>
                    <p class="text-xs text-slate-500 dark:text-zinc-400">{{ $cantidadArticulos }} {{ Str::plural('artículo', $cantidadArticulos) }}</p>
                </div>
                @if(count($carrito) > 0)
                    <button
                        wire:click="limpiarCarrito"
                        class="text-xs font-medium text-red-500 hover:text-red-600 transition"
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
                                            <p class="text-xs text-slate-500 dark:text-zinc-400">{{ ucfirst($item['presentacion']) }}</p>
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
                                            @disabled($item['cantidad'] >= $item['stock'])
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

                <button
                    wire:click="abrirCobro"
                    @disabled(empty($carrito))
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-[#0c9f9c] px-4 py-3 text-sm font-bold text-white shadow-lg shadow-[#0c9f9c]/20 transition hover:bg-[#0a8582] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Cobrar ${{ number_format($total, 2) }}
                </button>
            </div>
        </div>
    </div>

    {{-- Botón flotante carrito (móvil) --}}
    <div class="fixed bottom-5 right-5 z-40 sm:bottom-6 sm:right-6 lg:hidden">
        <button
            wire:click="$set('mostrandoCarrito', true)"
            class="relative flex h-14 w-14 items-center justify-center rounded-full bg-[#0c9f9c] text-white shadow-xl shadow-[#0c9f9c]/30 transition hover:bg-[#0a8582] active:scale-95"
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
                            <button wire:click="limpiarCarrito" class="text-xs font-medium text-red-500 hover:text-red-600">Vaciar</button>
                        @endif
                        <button wire:click="$set('mostrandoCarrito', false)" class="rounded p-1 text-slate-400 hover:text-slate-600">
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
                                                <p class="text-xs text-slate-500 dark:text-zinc-400">{{ ucfirst($item['presentacion']) }}</p>
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
                                            <button wire:click="actualizarCantidad({{ $index }}, {{ $item['cantidad'] + 1 }})" class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600" @disabled($item['cantidad'] >= $item['stock'])>
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
                wire:click="cerrarCobro"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
            ></div>

            {{-- Modal --}}
            <div
                class="relative flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                {{-- Header --}}
                <div class="bg-slate-900 px-6 py-5 text-center dark:bg-zinc-800">
                    <p class="text-sm font-medium text-slate-400 dark:text-zinc-400">Total a pagar</p>
                    <p class="mt-1 text-3xl font-extrabold text-white">${{ number_format($total, 2) }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-zinc-500">{{ $cantidadProductos }} {{ Str::plural('producto', $cantidadProductos) }} en carrito</p>
                </div>

                {{-- Error --}}
                @if($errorVenta)
                    <div class="mx-6 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800 dark:bg-red-900/30">
                        <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ $errorVenta }}</p>
                    </div>
                @endif

                {{-- Métodos de pago --}}
                <div class="px-6 pt-5">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Método de pago</p>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        <button
                            wire:click="seleccionarMetodo('efectivo')"
                            class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-3 py-3 text-xs font-bold transition
                                {{ $metodoPago === 'efectivo' ? 'border-[#0c9f9c] bg-[#0c9f9c]/10 text-[#0c9f9c]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Efectivo
                        </button>
                        <button
                            wire:click="seleccionarMetodo('tarjeta')"
                            class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-3 py-3 text-xs font-bold transition
                                {{ $metodoPago === 'tarjeta' ? 'border-[#0c9f9c] bg-[#0c9f9c]/10 text-[#0c9f9c]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Tarjeta
                        </button>
                        <button
                            wire:click="seleccionarMetodo('transferencia')"
                            class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-3 py-3 text-xs font-bold transition
                                {{ $metodoPago === 'transferencia' ? 'border-[#0c9f9c] bg-[#0c9f9c]/10 text-[#0c9f9c]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            Transf.
                        </button>
                        <button
                            wire:click="seleccionarMetodo('mixto')"
                            class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-3 py-3 text-xs font-bold transition
                                {{ $metodoPago === 'mixto' ? 'border-[#0c9f9c] bg-[#0c9f9c]/10 text-[#0c9f9c]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            Mixto
                        </button>
                    </div>
                </div>

                {{-- Contenido con scroll --}}
                <div class="flex-1 overflow-y-auto px-6 py-5">
                    {{-- Efectivo --}}
                    @if($metodoPago === 'efectivo')
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Efectivo recibido</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-slate-400">$</span>
                                <input
                                    x-ref="firstInput"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    wire:model.live="efectivoRecibido"
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-8 pr-4 text-lg font-bold text-slate-800 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
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
                                    :class="activoEfectivo ? 'border-[#0c9f9c] bg-[#0c9f9c]/10 text-[#0c9f9c]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'"
                                >
                                    &#x1F4B5; Efectivo
                                </button>
                                <button
                                    @click="toggle('tarjeta')"
                                    class="flex items-center justify-center gap-1.5 rounded-xl border-2 px-3 py-3 text-xs font-bold transition
                                        "
                                    :class="activoTarjeta ? 'border-[#0c9f9c] bg-[#0c9f9c]/10 text-[#0c9f9c]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'"
                                >
                                    &#x1F4B3; Tarjeta
                                </button>
                                <button
                                    @click="toggle('transferencia')"
                                    class="flex items-center justify-center gap-1.5 rounded-xl border-2 px-3 py-3 text-xs font-bold transition
                                        "
                                    :class="activoTransferencia ? 'border-[#0c9f9c] bg-[#0c9f9c]/10 text-[#0c9f9c]' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'"
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
</div>
