@php
    $sucursalGrafica = $selectedSucursal ?? null;

    if ($sucursalGrafica === null) {
        $sucursalGraficaId = (int) (session('active_sucursal_id') ?? auth()->user()?->id_sucursal ?? 0);
        $sucursalGrafica = $sucursalGraficaId > 0
            ? \App\Models\Sucursal::find($sucursalGraficaId)
            : null;
    }

    $reporteHoy = app(\App\Services\VentasReporteService::class)->ventasPorDia(
        now()->toDateString(),
        $sucursalGrafica?->id
    );

    $ventasHoy = $reporteHoy['ventas'];
    $totalesHoy = $reporteHoy['totales'];
    $cantidadVentas = (int) $totalesHoy['cantidad'];
    $totalHoy = (float) $totalesHoy['total'];
    $ticketPromedio = $cantidadVentas > 0 ? $totalHoy / $cantidadVentas : 0.0;

    $porHora = $ventasHoy
        ->groupBy(fn ($venta): string => $venta->creado_en?->format('H') ?? '00')
        ->sortKeys()
        ->map(fn ($grupo, string $hora): array => [
            'hora' => $hora,
            'total' => round((float) $grupo->sum(fn ($venta): float => (float) $venta->total), 2),
            'cantidad' => $grupo->count(),
        ]);

    $maxHora = max(1.0, (float) ($porHora->max('total') ?? 0));
@endphp

<section class="module-card pos-glass-highlight !bg-white/70 backdrop-blur-xl supports-[backdrop-filter]:!bg-white/60 !border-white/50 shadow-xl shadow-teal-900/5 dark:!bg-slate-800/60 dark:!border-white/10 p-4 sm:p-5" aria-label="Gráfica de ventas de hoy">
    <header class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex items-start gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/20">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V5m0 14h16M8 16v-4m4 4V8m4 8v-6"/>
                </svg>
            </span>
            <div>
                <h2 class="text-sm font-black tracking-tight text-slate-800 dark:text-white">Ventas de hoy</h2>
                <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                    {{ $reporteHoy['etiqueta'] }} ·
                    {{ $sucursalGrafica?->nombre_sucursal ?? 'Todas las sucursales' }}
                </p>
            </div>
        </div>

        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            Total ${{ number_format($totalHoy, 2) }}
        </span>
    </header>

    @if($cantidadVentas > 0)
        <div class="mt-5 grid gap-5 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="flex h-44 items-end gap-2 border-b border-slate-200 px-1 pb-1 dark:border-slate-700">
                    @foreach($porHora as $hora)
                        @php($altura = max(6, (int) round(($hora['total'] / $maxHora) * 100)))
                        <div class="flex min-w-0 flex-1 flex-col items-center justify-end gap-1" title="{{ $hora['hora'] }}:00 · {{ $hora['cantidad'] }} venta(s) · ${{ number_format($hora['total'], 2) }}">
                            <span class="hidden text-[10px] font-bold tabular-nums text-slate-500 dark:text-slate-400 sm:block">
                                ${{ number_format($hora['total'], 0) }}
                            </span>
                            <div class="w-full rounded-t-lg bg-gradient-to-t from-[#0e9384] to-[#34d399] shadow-sm" style="height: {{ $altura }}%"></div>
                            <span class="text-[10px] font-bold tabular-nums text-slate-400 dark:text-slate-500">{{ $hora['hora'] }}h</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-[11px] font-medium text-slate-400 dark:text-slate-500">Ventas agrupadas por hora de registro.</p>
            </div>

            <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-1">
                <div class="pos-card-animate rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" style="animation-delay: 0ms">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Ventas</p>
                    <p class="mt-1 text-lg font-black leading-none text-slate-900 dark:text-white">{{ number_format($cantidadVentas) }}</p>
                </div>
                <div class="pos-card-animate rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" style="animation-delay: 70ms">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Unidades</p>
                    <p class="mt-1 text-lg font-black leading-none text-slate-900 dark:text-white">{{ number_format((int) $totalesHoy['unidades']) }}</p>
                </div>
                <div class="pos-card-animate rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800" style="animation-delay: 140ms">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Ticket promedio</p>
                    <p class="mt-1 text-lg font-black leading-none text-slate-900 dark:text-white">${{ number_format($ticketPromedio, 2) }}</p>
                </div>
            </div>
        </div>

        @if(! empty($totalesHoy['por_metodo']))
            <div class="mt-5 grid gap-3 border-t border-slate-100 pt-4 dark:border-slate-800 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($totalesHoy['por_metodo'] as $metodo)
                    @php($porcentaje = $totalHoy > 0 ? round(($metodo['total'] / $totalHoy) * 100) : 0)
                    <div class="pos-card-animate rounded-xl border border-slate-200 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900" style="animation-delay: {{ $loop->index * 70 }}ms">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate text-xs font-bold text-slate-600 dark:text-slate-300">{{ $metodo['metodo'] }}</span>
                            <span class="text-xs font-black text-slate-900 dark:text-white">${{ number_format((float) $metodo['total'], 2) }}</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-[#0e9384]" style="width: {{ $porcentaje }}%"></div>
                        </div>
                        <p class="mt-1 text-[10px] font-medium text-slate-400 dark:text-slate-500">{{ $porcentaje }}% · {{ $metodo['cantidad'] }} venta(s)</p>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="mt-4 rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center dark:border-slate-700">
            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Aún no hay ventas registradas hoy</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                La gráfica se actualizará cuando se registre la primera venta en {{ $sucursalGrafica?->nombre_sucursal ?? 'la sucursal' }}.
            </p>
        </div>
    @endif
</section>
