@php
    $sucursalSeleccionada = $filtros['sucursal'] !== null
        ? $sucursales->firstWhere('id', $filtros['sucursal'])
        : null;

    $parametrosPdf = [
        'tipo' => $filtros['tipo'],
        'sucursal' => $filtros['sucursal'] ?? '',
    ];

    if ($filtros['tipo'] === \App\Services\VentasReporteService::TIPO_DIA) {
        $parametrosPdf['fecha'] = $filtros['fecha'];
    } elseif ($filtros['tipo'] === \App\Services\VentasReporteService::TIPO_SEMANA) {
        $parametrosPdf['semana'] = $filtros['semana'];
    } else {
        $parametrosPdf['mes'] = $filtros['mes'];
    }
@endphp

<section
    class="module-card p-4 sm:p-5"
    x-data="{ tipo: @js($filtros['tipo']) }"
    @option-pick-changed.window="if ($event.detail.name === 'tipo') { tipo = $event.detail.value }"
    aria-label="Filtros del reporte de ventas"
>
    <form method="GET" action="{{ route('reportes.index') }}" class="grid gap-4 lg:grid-cols-12 lg:items-end">
        <div class="grid gap-3 sm:grid-cols-2 lg:col-span-6">
            <div>
                <label for="reporte-tipo" class="mb-1.5 block text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">
                    Tipo de periodo
                </label>
                <x-option-pick
                    id="reporte-tipo"
                    name="tipo"
                    label="Tipo de periodo"
                    :options="['dia' => 'DÍA', 'semana' => 'SEMANA', 'mes' => 'MES']"
                    :value="$filtros['tipo']"
                />
            </div>

            <div x-show="tipo === 'dia'" x-cloak>
                <label for="reporte-fecha" class="mb-1.5 block text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">
                    Fecha
                </label>
                <input
                    id="reporte-fecha"
                    type="date"
                    name="fecha"
                    value="{{ $filtros['fecha'] }}"
                    max="{{ now()->toDateString() }}"
                    class="theme-input w-full"
                >
            </div>

            <div x-show="tipo === 'semana'" x-cloak>
                <label for="reporte-semana" class="mb-1.5 block text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">
                    Semana (lunes a domingo)
                </label>
                <input
                    id="reporte-semana"
                    type="week"
                    name="semana"
                    value="{{ $filtros['semana'] }}"
                    max="{{ now()->format('o-\WW') }}"
                    class="theme-input w-full"
                >
            </div>

            <div x-show="tipo === 'mes'" x-cloak>
                <label for="reporte-mes" class="mb-1.5 block text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">
                    Mes
                </label>
                <input
                    id="reporte-mes"
                    type="month"
                    name="mes"
                    value="{{ $filtros['mes'] }}"
                    max="{{ now()->format('Y-m') }}"
                    class="theme-input w-full"
                >
            </div>
        </div>

        <div class="lg:col-span-3">
            <label for="reporte-sucursal" class="mb-1.5 block text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">
                Sucursal
            </label>

            @if($puedeConsultarVarias)
                <select id="reporte-sucursal" name="sucursal" class="theme-input w-full">
                    <option value="" @selected($filtros['sucursal'] === null)>Todas las sucursales</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" @selected($filtros['sucursal'] === $sucursal->id)>
                            {{ $sucursal->nombre_sucursal }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="hidden" name="sucursal" value="{{ $filtros['sucursal'] ?? '' }}">
                <div class="theme-input flex w-full items-center gap-2 text-slate-700 dark:text-zinc-200">
                    <svg class="h-4 w-4 shrink-0 text-[#0e9384]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-5.25 7-11a7 7 0 10-14 0c0 5.75 7 11 7 11z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10.5h.01"/>
                    </svg>
                    <span class="truncate font-semibold">{{ $sucursalSeleccionada?->nombre_sucursal ?? 'Sin sucursal asignada' }}</span>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2 lg:col-span-12">
            <button type="submit" class="theme-button theme-button-primary !min-h-0 !py-2.5">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Aplicar filtros
            </button>

            <a
                href="{{ route('reportes.pdf', $parametrosPdf) }}"
                target="_blank"
                rel="noopener"
                class="theme-button theme-button-secondary !min-h-0 !py-2.5"
                title="Abre la vista imprimible para guardar el reporte en PDF"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V8l-4-4H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Descargar PDF
            </a>

            <span class="ms-auto inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3M4 11h16M5 21h14a1 1 0 001-1V7a1 1 0 00-1-1H5a1 1 0 00-1 1v13a1 1 0 001 1z"/>
                </svg>
                No se permiten periodos futuros
            </span>
        </div>
    </form>
</section>
