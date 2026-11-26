@php
    $consolidado = $reporte['sucursal'] === null;
    $totalColumnas = $consolidado ? 8 : 7;
    $interactivo = $interactivo ?? false;
    $vistaPorDias = $interactivo && in_array(
        $reporte['tipo'],
        [\App\Services\VentasReporteService::TIPO_SEMANA, \App\Services\VentasReporteService::TIPO_MES],
        true
    );
@endphp

<section class="module-card pos-glass-highlight !p-0 overflow-hidden !bg-white/70 backdrop-blur-xl supports-[backdrop-filter]:!bg-white/60 !border-white/50 shadow-xl shadow-teal-900/5 dark:!bg-slate-800/60 dark:!border-white/10" aria-label="Resultados del reporte de ventas">
    <header class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 bg-white px-5 py-4 dark:border-slate-700 dark:bg-slate-900">
        <div>
            <h2 class="text-sm font-black tracking-tight text-slate-800 dark:text-white">Ventas del periodo</h2>
            <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                {{ $reporte['etiqueta'] }} ·
                {{ $consolidado ? 'Todas las sucursales' : $reporte['sucursal']->nombre_sucursal }} ·
                {{ $reporte['totales']['cantidad'] }} venta(s)
            </p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            Total ${{ number_format((float) $reporte['totales']['total'], 2) }}
        </span>
    </header>

    <div class="grid grid-cols-1 gap-px border-b border-slate-200 bg-slate-200 dark:border-slate-700 dark:bg-slate-700 sm:grid-cols-2 lg:grid-cols-5">
        <div class="bg-white px-4 py-3 dark:bg-slate-900">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Ventas</p>
            <p class="mt-1 text-lg font-black leading-none text-slate-900 dark:text-white">{{ $reporte['totales']['cantidad'] }}</p>
        </div>
        <div class="bg-white px-4 py-3 dark:bg-slate-900">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Unidades</p>
            <p class="mt-1 text-lg font-black leading-none text-slate-900 dark:text-white">{{ number_format((int) $reporte['totales']['unidades']) }}</p>
        </div>
        <div class="bg-white px-4 py-3 dark:bg-slate-900">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Subtotal</p>
            <p class="mt-1 text-lg font-black leading-none text-slate-900 dark:text-white">${{ number_format((float) $reporte['totales']['subtotal'], 2) }}</p>
        </div>
        <div class="bg-white px-4 py-3 dark:bg-slate-900">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Descuento</p>
            <p class="mt-1 text-lg font-black leading-none text-slate-900 dark:text-white">${{ number_format((float) $reporte['totales']['descuento'], 2) }}</p>
        </div>
        <div class="bg-emerald-50 px-4 py-3 dark:bg-emerald-500/10">
            <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Total</p>
            <p class="mt-1 text-lg font-black leading-none text-emerald-800 dark:text-emerald-200">${{ number_format((float) $reporte['totales']['total'], 2) }}</p>
        </div>
    </div>

    @if(! empty($reporte['totales']['por_metodo']))
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-slate-50/70 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/40">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Por método de pago</span>
            @foreach($reporte['totales']['por_metodo'] as $metodo)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    {{ $metodo['metodo'] }}
                    <span class="text-slate-400 dark:text-slate-500">·</span>
                    ${{ number_format((float) $metodo['total'], 2) }}
                    <span class="text-slate-400 dark:text-slate-500">({{ $metodo['cantidad'] }})</span>
                </span>
            @endforeach
        </div>
    @endif

    @if($vistaPorDias)
        <div class="p-4 sm:p-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                @foreach($reporte['dias'] as $dia)
                    @php($tieneVentas = $dia['cantidad'] > 0)
                    <button
                        type="button"
                        @if($tieneVentas)
                            x-on:click="$dispatch('ver-ventas-dia', { fecha: '{{ $dia['fecha'] }}', sucursal: {{ $reporte['sucursal']?->id ?? 'null' }} })"
                        @else
                            disabled
                        @endif
                        aria-label="{{ $dia['etiqueta'] }}: {{ $tieneVentas ? $dia['cantidad'].' venta(s), $'.number_format((float) $dia['total'], 2) : 'sin ventas' }}"
                        class="group flex min-h-[7.5rem] flex-col justify-between rounded-2xl border p-4 text-left transition {{ $tieneVentas
                            ? 'cursor-pointer border-slate-200 bg-white hover:-translate-y-0.5 hover:border-[#0e9384] hover:shadow-lg hover:shadow-teal-900/10 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-emerald-500'
                            : 'cursor-default border-dashed border-slate-200 bg-slate-50/60 opacity-70 dark:border-slate-700 dark:bg-slate-800/40' }}"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] font-black uppercase tracking-widest {{ $tieneVentas ? 'text-[#0c7569] dark:text-emerald-300' : 'text-slate-400 dark:text-slate-500' }}">
                                {{ $dia['dia_semana_corto'] }} · {{ $dia['dia'] }} {{ $dia['mes_corto'] }}
                            </span>
                            @if($tieneVentas)
                                <svg class="h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-[#0e9384] dark:text-slate-600 dark:group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            @endif
                        </div>

                        <div>
                            <p class="text-xl font-black leading-none {{ $tieneVentas ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }}">
                                ${{ number_format((float) $dia['total'], 2) }}
                            </p>
                            <p class="mt-2 text-[11px] font-semibold {{ $tieneVentas ? 'text-slate-500 dark:text-slate-400' : 'text-slate-400 dark:text-slate-500' }}">
                                @if($tieneVentas)
                                    {{ $dia['cantidad'] }} venta(s) · {{ number_format((int) $dia['unidades']) }} unidad(es)
                                @else
                                    Sin ventas
                                @endif
                            </p>
                        </div>
                    </button>
                @endforeach
            </div>

            <p class="mt-3 text-[11px] font-medium text-slate-400 dark:text-slate-500">
                Toca un día con ventas para ver el detalle de sus ventas.
            </p>
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="entradas-tabla !min-w-[960px]">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha</th>
                    @if($consolidado)
                        <th>Sucursal</th>
                    @endif
                    <th>Caja / turno</th>
                    <th>Pago</th>
                    <th class="entradas-num">Artículos</th>
                    <th class="entradas-num">Descuento</th>
                    <th class="entradas-num">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reporte['ventas'] as $venta)
                    <tr
                        @if($interactivo)
                            class="cursor-pointer"
                            x-on:click="$dispatch('ver-detalle-venta', { idVenta: {{ $venta->id }} })"
                            x-on:keydown.enter.prevent="$dispatch('ver-detalle-venta', { idVenta: {{ $venta->id }} })"
                            x-on:keydown.space.prevent="$dispatch('ver-detalle-venta', { idVenta: {{ $venta->id }} })"
                            tabindex="0"
                            role="button"
                            aria-label="Ver productos vendidos de la venta {{ $venta->folio }}"
                        @endif
                    >
                        <td>
                            <span class="block font-bold text-slate-800 dark:text-slate-100">{{ $venta->folio }}</span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">
                                {{ $venta->detalles->count() }} producto(s)
                            </span>
                        </td>
                        <td class="whitespace-nowrap">
                            {{ $venta->creado_en?->format('d/m/Y') }}
                            <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $venta->creado_en?->format('H:i') }}</span>
                        </td>
                        @if($consolidado)
                            <td>{{ $venta->sucursal?->nombre_sucursal ?? '—' }}</td>
                        @endif
                        <td>
                            @if($venta->corteCaja)
                                <span class="block font-semibold text-slate-700 dark:text-slate-200">{{ $venta->corteCaja->caja }}</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $venta->corteCaja->turno }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                {{ $venta->pago?->metodo ?? 'Sin pago' }}
                            </span>
                        </td>
                        <td class="entradas-num">
                            {{ number_format((int) $venta->detalles->sum('cantidad')) }}
                        </td>
                        <td class="entradas-num">${{ number_format((float) $venta->descuento, 2) }}</td>
                        <td class="entradas-num font-bold text-slate-900 dark:text-white">
                            ${{ number_format((float) $venta->total, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $totalColumnas }}" class="px-4 py-14 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-6m3 6V7m3 10v-4M5 20h14a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                                    </svg>
                                </span>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">No hay ventas en el periodo seleccionado</p>
                                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                                    Prueba con otro día, semana o mes, o cambia la sucursal del reporte.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($reporte['ventas']->isNotEmpty())
                <tfoot>
                    <tr class="bg-slate-50 dark:bg-slate-800/70">
                        <td colspan="{{ $consolidado ? 5 : 4 }}" class="px-4 py-3 text-right text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">
                            Totales del periodo
                        </td>
                        <td class="entradas-num px-4 py-3 font-bold text-slate-900 dark:text-white">
                            {{ number_format((int) $reporte['totales']['unidades']) }}
                        </td>
                        <td class="entradas-num px-4 py-3 font-bold text-slate-900 dark:text-white">
                            ${{ number_format((float) $reporte['totales']['descuento'], 2) }}
                        </td>
                        <td class="entradas-num px-4 py-3 text-sm font-black text-emerald-700 dark:text-emerald-300">
                            ${{ number_format((float) $reporte['totales']['total'], 2) }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    @endif
</section>
