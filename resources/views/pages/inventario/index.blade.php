<x-layouts::app :title="__('Inventario')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado limpio --}}
            <header class="mb-6" aria-label="Encabezado de inventario">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Catálogo · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Inventario farmacéutico</h1>
                    <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500 dark:text-slate-400">Control de existencias por sucursal: unidades reales, cajas cerradas y lotes vigentes en un solo vistazo.</p>
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
  <dl class="grid grid-cols-2 gap-px bg-slate-200 dark:bg-zinc-800 lg:grid-cols-4">
    <div class="relative flex items-center gap-2.5 bg-white px-3.5 py-3 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60">
    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600 ring-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Productos</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $productos->count() }}</p>
        </div>
</div>
    <div @class(['relative flex items-center gap-2.5 bg-white px-3.5 py-3 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60', '!border-amber-200 !bg-amber-50 dark:!border-amber-500/30 dark:!bg-amber-500/10' => $stockCritico > 0])>
    <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-amber-500"></span><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/20 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Stock crítico</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $stockCritico }}</p>
        </div>
</div>
    <div class="relative flex items-center gap-2.5 bg-white px-3.5 py-3 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60">
    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/20 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Unidades</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ number_format($stockTotal) }}</p>
        </div>
</div>
    <div class="relative flex items-center gap-2.5 bg-white px-3.5 py-3 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60">
    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/20 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Valor total</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">${{ number_format($valorTotal, 2) }}</p>
        </div>
</div>
  </dl>
</div>
                </div>
            </header>

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
                        <p class="theme-subtle text-sm">{{ $productos->count() }} producto(s) con existencia · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    </div>
                    <span class="hidden items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 sm:inline-flex">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Unidades reales por lote vigente
                    </span>
                </header>

                {{-- Toolbar empresarial: sucursal + buscador con respiración --}}
                <div class="entradas-toolbar border-b border-slate-100 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-900/40">
                    <div class="entradas-toolbar-sucursal">
                        <span class="entradas-toolbar-label">Sucursal activa</span>
                        <span class="entradas-toolbar-valor flex items-center gap-2">
                            <span class="grid h-8 w-8 place-items-center rounded-xl bg-gradient-to-br from-[#0e9384] to-[#0c7569] text-sm font-black text-white shadow-sm">{{ mb_strtoupper(mb_substr($selectedSucursal?->nombre_sucursal ?? '?', 0, 1)) }}</span>
                            {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}
                        </span>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $productos->count() }} productos · {{ number_format($stockTotal) }} uds. totales</span>
                    </div>

                    <form method="GET" action="{{ route('inventario.stock') }}" class="entradas-toolbar-form">
                        <label class="entradas-toolbar-select">
                            <span>Sucursal</span>
                            <x-option-pick name="sucursal" label="Sucursales" :options="$sucursales->pluck('nombre_sucursal', 'id')" :value="$selectedSucursal?->id" placeholder="Seleccionar sucursal" :autoSubmit="true" />
                        </label>
                        <div class="entradas-toolbar-buscar">
                            <input type="search" name="buscar" value="{{ $busqueda }}" placeholder="Buscar código o nombre…" class="theme-input" aria-label="Buscar producto" @input.debounce.200ms="texto = $event.target.value">
                            <button type="submit" class="theme-button theme-button-secondary !min-h-0 !py-2.5">Buscar</button>
                        </div>
                    </form>
                </div>

                <div class="entradas-tabla-wrap !border-0" style="max-height:380px">
                    <div class="overflow-x-auto">
                        <table class="entradas-tabla !min-w-[860px]">
                            <thead>
                                <tr>
                                    <th class="!py-3.5">Producto</th>
                                    <th class="!py-3.5">Cajas cerradas</th>
                                    <th class="!py-3.5">Caja abierta</th>
                                    <th class="!py-3.5 text-right">Stock total</th>
                                    <th class="!py-3.5 text-center">Lotes</th>
                                    <th class="!py-3.5 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productos as $producto)
                                    @php
                                        $esCritico = (int) $producto->stock_sucursal <= 15;
                                        $stockNum = (int) $producto->stock_sucursal;
                                    @endphp
                                    <tr data-buscar="{{ $producto->codigo_barras }} {{ $producto->nombre_producto }}">
                                        <td class="!py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="hidden h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-sm font-bold text-slate-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700 sm:grid">{{ mb_strtoupper(mb_substr($producto->nombre_producto, 0, 2)) }}</span>
                                                <div class="min-w-0">
                                                    <span class="block font-medium text-slate-800 dark:text-slate-100">{{ $producto->nombre_producto }}</span>
                                                    <span class="block text-xs theme-subtle">{{ $producto->codigo_barras }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="!py-4">
                                            @if($producto->unidades_por_caja !== null)
                                                <span class="block font-medium text-slate-700 dark:text-slate-200">{{ $producto->cajas_cerradas }}</span>
                                                <span class="block text-xs theme-subtle">{{ $producto->unidades_por_caja }} uds. por caja</span>
                                            @else
                                                <span class="theme-subtle">—</span>
                                            @endif
                                        </td>
                                        <td class="!py-4 text-slate-700 dark:text-slate-200">@if($producto->caja_abierta !== null){{ $producto->caja_abierta }}@else<span class="theme-subtle">—</span>@endif</td>
                                        <td class="!py-4 text-right">
                                            <span class="text-[15px] font-extrabold tabular-nums {{ $esCritico ? 'text-amber-600 dark:text-amber-300' : 'text-slate-800 dark:text-slate-100' }}">{{ $producto->stock_sucursal }} uds.</span>
                                            @if($esCritico && $stockNum > 0)
                                                <p class="mt-1 text-[11px] font-bold uppercase tracking-widest text-amber-600 dark:text-amber-400">Reposición sugerida</p>
                                            @elseif($stockNum === 0)
                                                <p class="mt-1 text-[11px] font-bold uppercase tracking-widest text-red-600 dark:text-red-400">Sin existencia</p>
                                            @endif
                                        </td>
                                        <td class="!py-4 text-center">
                                            @if($producto->lotes_sucursal > 0)
                                                <a href="{{ route('lotes.index', ['sucursal' => $selectedSucursal?->id, 'buscar' => $producto->nombre_producto]) }}" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0c7569] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-[#3fbda9] dark:hover:bg-emerald-500/10">
                                                    {{ $producto->lotes_sucursal }} {{ \Illuminate\Support\Str::plural('lote', $producto->lotes_sucursal) }}
                                                    <svg class="h-3 w-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </a>
                                            @else
                                                <span class="stat-pill info !px-3 !py-1 !text-xs">0 lotes</span>
                                            @endif
                                        </td>
                                        <td class="!py-4 text-center">
                                            @if($esCritico)
                                                <span class="inventory-status inventory-status-low stat-pill warning !px-3.5 !py-1.5 !text-xs !normal-case !font-bold">Bajo</span>
                                            @else
                                                <span class="inventory-status inventory-status-available stat-pill positive !px-3.5 !py-1.5 !text-xs !normal-case !font-bold">Disponible</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                <tr data-vacio @if($productos->isNotEmpty()) style="display: none" @endif>
                                    <td colspan="6" class="px-4 py-14 text-center">
                                        <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-2 5a2 2 0 01-2 1.5H8a2 2 0 01-2-1.5l-2-5"/></svg>
                                            </span>
                                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">No se encontraron productos</p>
                                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">Prueba con otro código, nombre o cambia de sucursal. Solo se muestran productos con inventario registrado en la sucursal activa.</p>
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

    @include('partials.buscador-cliente')
</x-layouts::app>
