<x-layouts::app :title="__('Inventario')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado homologado: hero verde + KPIs blancos --}}
            <section class="module-card noti-hero" aria-label="Encabezado de inventario">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Catálogo · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    <h1 class="mt-2">Inventario farmacéutico</h1>
                    <dl class="noti-resumen noti-kpis">
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg> Productos</dt><dd>{{ $productos->count() }}</dd>
                        </div>
                        <div @class(['noti-kpi', 'noti-kpi-alerta' => $stockCritico > 0])>
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg> Stock crítico</dt><dd>{{ $stockCritico }}</dd>
                        </div>
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg> Unidades</dt><dd>{{ $stockTotal }}</dd>
                        </div>
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Valor total</dt><dd>${{ number_format($valorTotal, 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            @include('pages.inventario.tabs', ['seccion' => 'stock', 'selectedSucursal' => $selectedSucursal])

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
                    class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
                    role="alert"
                >
                    {{ session('error') }}
                </div>
            @endif

            <section class="module-card noti-listado" aria-label="Stock por sucursal" x-data="buscadorTabla()" x-effect="filtrar($el)">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Stock por sucursal</h2>
                        <p class="theme-subtle text-sm">{{ $productos->count() }} producto(s) · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    </div>
                </header>
                <div class="p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</h2>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <form method="GET" action="{{ route('inventario.stock') }}" class="flex w-full flex-col gap-2">
                            <span class="theme-subtle text-xs font-semibold uppercase tracking-[0.2em]">Sucursal</span>
                            <x-option-pick name="sucursal" label="Sucursales" :options="$sucursales->pluck('nombre_sucursal', 'id')" :value="$selectedSucursal?->id" placeholder="Seleccionar sucursal" :autoSubmit="true" />
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <input type="search" name="buscar" value="{{ $busqueda }}" placeholder="Buscar código o nombre" class="theme-input w-full sm:w-64" aria-label="Buscar producto" @input.debounce.200ms="texto = $event.target.value">
                                <button type="submit" class="theme-button theme-button-secondary whitespace-nowrap">Buscar</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="module-table mt-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Producto</th>
                                    <th class="px-4 py-3 font-semibold">Cajas cerradas</th>
                                    <th class="px-4 py-3 font-semibold">Caja abierta</th>
                                    <th class="px-4 py-3 font-semibold">Stock en unidades</th>
                                    <th class="px-4 py-3 font-semibold">Lotes</th>
                                    <th class="px-4 py-3 font-semibold">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productos as $producto)
                                    <tr class="border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5" data-buscar="{{ $producto->codigo_barras }} {{ $producto->nombre_producto }}">
                                        <td class="px-4 py-3">
                                            <span class="block font-medium text-slate-800 dark:text-slate-100">{{ $producto->nombre_producto }}</span>
                                            <span class="block text-xs theme-subtle">{{ $producto->codigo_barras }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($producto->unidades_por_caja !== null)
                                                <span class="block font-medium text-slate-700 dark:text-slate-200">{{ $producto->cajas_cerradas }}</span>
                                                <span class="block text-xs theme-subtle">{{ $producto->unidades_por_caja }} uds. por caja</span>
                                            @else
                                                <span class="theme-subtle">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">@if($producto->caja_abierta !== null){{ $producto->caja_abierta }}@else<span class="theme-subtle">—</span>@endif</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $producto->stock_sucursal }} uds.</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                            @if($producto->lotes_sucursal > 0)
                                                <a href="{{ route('lotes.index', ['sucursal' => $selectedSucursal?->id, 'buscar' => $producto->nombre_producto]) }}" class="font-medium transition hover:text-emerald-600 hover:underline dark:hover:text-emerald-400">
                                                    {{ $producto->lotes_sucursal }} {{ \Illuminate\Support\Str::plural('lote', $producto->lotes_sucursal) }}
                                                </a>
                                            @else
                                                0 lotes
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($producto->stock_sucursal <= 15)
                                                <span class="inventory-status inventory-status-low">Bajo</span>
                                            @else
                                                <span class="inventory-status inventory-status-available">Disponible</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                    <tr data-vacio @if($productos->isNotEmpty()) style="display: none" @endif>
                                        <td colspan="6" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                            No se encontraron productos para esta sucursal o búsqueda.
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
            </section>
        </div>
    </div>

    @include('partials.buscador-cliente')
</x-layouts::app>
