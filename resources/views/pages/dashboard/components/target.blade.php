@php
    $sucursalTarget = $selectedSucursal ?? null;

    if ($sucursalTarget === null) {
        $sucursalTargetId = (int) (session('active_sucursal_id') ?? auth()->user()?->id_sucursal ?? 0);
        $sucursalTarget = $sucursalTargetId > 0
            ? \App\Models\Sucursal::find($sucursalTargetId)
            : null;
    }

    $inventarioService = app(\App\Services\InventarioService::class);
    $todosProximosCaducar = $inventarioService->productosProximosACaducar($sucursalTarget?->id);
    $todoStockBajo = $inventarioService->productosConStockBajo($sucursalTarget?->id);
    $proximosCaducar = $todosProximosCaducar->take(5);
    $stockBajo = $todoStockBajo->take(5);
    $totalProximosCaducar = $todosProximosCaducar->count();
    $totalStockBajo = $todoStockBajo->count();
    $totalAlertas = $totalProximosCaducar + $totalStockBajo;
@endphp

<section class="module-card pos-glass-highlight p-4 sm:p-5" aria-label="Productos que requieren atención">
    <header class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex items-start gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/20">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m0 3.5h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </span>
            <div>
                <h2 class="text-sm font-black tracking-tight text-slate-800 dark:text-white">Inventario que requiere atención</h2>
                <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                    Caducidades y stock mínimo · {{ $sucursalTarget?->nombre_sucursal ?? 'Todas las sucursales' }}
                </p>
            </div>
        </div>

        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
            {{ $totalAlertas }} alerta(s)
        </span>
    </header>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
                <div class="flex items-center gap-2">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3M4 11h16M5 21h14a1 1 0 001-1V7a1 1 0 00-1-1H5a1 1 0 00-1 1v13a1 1 0 001 1z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-black text-slate-800 dark:text-white">Próximos a caducar</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Caducados y dentro de los próximos 90 días</p>
                    </div>
                </div>
                <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-black text-slate-600 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700">
                    {{ $totalProximosCaducar }}
                </span>
            </div>

            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($proximosCaducar as $fila)
                    @php($dias = $fila['dias_restantes'])
                    <li class="pos-card-animate flex items-start justify-between gap-3 px-4 py-3" style="animation-delay: {{ $loop->index * 60 }}ms">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">
                                {{ $fila['producto']?->nombre_producto ?? 'Producto sin nombre' }}
                            </p>
                            <p class="mt-0.5 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                Lote {{ $fila['folio'] }} · {{ $fila['stock'] }} uds. · {{ $fila['sucursal']?->nombre_sucursal ?? 'Sin sucursal' }}
                            </p>
                        </div>
                        <div class="shrink-0 text-right">
                            <span @class([
                                'inline-flex rounded-full px-2 py-0.5 text-[10px] font-black',
                                'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' => $fila['estado'] === 'CADUCADO',
                                'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' => $fila['estado'] !== 'CADUCADO',
                            ])>
                                {{ $fila['estado'] === 'CADUCADO' ? 'Caducado' : 'Por caducar' }}
                            </span>
                            <p class="mt-1 text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                {{ $fila['fecha_caducidad']?->format('d/m/Y') ?? 'Sin fecha' }}
                            </p>
                            @if($dias !== null)
                                <p class="text-[10px] text-slate-400 dark:text-slate-500">
                                    {{ $dias < 0 ? 'Vencido hace '.abs($dias).' día(s)' : ($dias === 0 ? 'Caduca hoy' : "En {$dias} día(s)") }}
                                </p>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                        No hay lotes caducados ni próximos a caducar.
                    </li>
                @endforelse
            </ul>

            <div class="border-t border-slate-100 px-4 py-2.5 dark:border-slate-800">
                <a href="{{ route('lotes.index', array_filter(['sucursal' => $sucursalTarget?->id])) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-[#0e9384] hover:text-[#0c7569] dark:text-emerald-300">
                    Ver lotes y caducidades
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60">
                <div class="flex items-center gap-2">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125zM9 12h6"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-black text-slate-800 dark:text-white">Stock bajo</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Productos en o por debajo de su mínimo</p>
                    </div>
                </div>
                <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-black text-slate-600 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700">
                    {{ $totalStockBajo }}
                </span>
            </div>

            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($stockBajo as $fila)
                    @php($porcentaje = $fila['minimo'] > 0 ? min(100, (int) round(($fila['stock'] / $fila['minimo']) * 100)) : 0)
                    <li class="pos-card-animate px-4 py-3" style="animation-delay: {{ $loop->index * 60 }}ms">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">
                                    {{ $fila['producto']->nombre_producto }}
                                </p>
                                <p class="mt-0.5 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                    {{ $fila['presentacion']?->presentacion?->presentacion ?? 'Unidad base' }} · mínimo {{ $fila['minimo'] }} uds.
                                </p>
                            </div>
                            <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-black text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                {{ $fila['stock'] }} uds.
                            </span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-amber-500" style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                        Todos los productos están por encima de su stock mínimo.
                    </li>
                @endforelse
            </ul>

            <div class="border-t border-slate-100 px-4 py-2.5 dark:border-slate-800">
                <a href="{{ route('inventario.stock', array_filter(['sucursal' => $sucursalTarget?->id])) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-[#0e9384] hover:text-[#0c7569] dark:text-emerald-300">
                    Ver inventario
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
