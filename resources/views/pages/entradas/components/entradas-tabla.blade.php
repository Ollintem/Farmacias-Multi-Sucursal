<section class="module-card noti-listado pos-glass-highlight" aria-label="Listado de entradas" x-data="buscadorTabla()" x-effect="filtrar($el)">
    <header class="noti-listado-cabecera">
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/15 ring-1 ring-black/5" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m0 10V11m0 10l-8-4V7m8 4L4 7"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-bold leading-tight text-slate-900 dark:text-white">{{ $tipoActual === 'compras' ? 'Compras recibidas' : 'Todas las entradas' }}</h2>
                <p class="theme-subtle text-sm">{{ $tipoActual === 'compras' ? 'Recepción de proveedores en ' . ($selectedSucursal?->nombre_sucursal ?? 'la sucursal') . '.' : 'Compras y traspasos recibidos en ' . ($selectedSucursal?->nombre_sucursal ?? 'la sucursal') . '.' }}</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-emerald-500/12 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-500/20 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/25">{{ ($entradas ?? collect())->count() }}</span>
        </div>
    </header>

    <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/30 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative flex-1 sm:max-w-sm">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z"/></svg>
            <input type="search" placeholder="Buscar folio, tipo o estado" class="theme-input !py-2.5 !pl-9" aria-label="Buscar entrada" @input.debounce.200ms="texto = $event.target.value">
        </div>
        <p class="theme-subtle hidden text-xs sm:block">Filtra al instante · folio, tipo y movimiento</p>
    </div>

    <div class="module-table">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-400">
                        <th class="px-5 py-3.5">Folio</th>
                        <th class="px-5 py-3.5">Tipo</th>
                        <th class="px-5 py-3.5">Movimiento</th>
                        <th class="px-5 py-3.5">Stock total</th>
                        <th class="px-5 py-3.5">Fecha</th>
                        <th class="px-5 py-3.5">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($entradas ?? [] as $entrada)
                        @php($folioEntrada = $entrada['folio'] ?? $entrada->folio ?? ('#' . ($entrada['id'] ?? $entrada->id ?? '—')))
                        @php($tipoEntrada = strtolower($entrada['tipo'] ?? $entrada->tipo ?? $tipoActual))
                        @php($origenEntrada = $entrada['origen'] ?? $entrada->origen ?? '—')
                        @php($destinoEntrada = $entrada['destino'] ?? $entrada->destino ?? ($selectedSucursal?->nombre_sucursal ?? '—'))
                        @php($fechaEntrada = $entrada['fecha'] ?? $entrada->fecha ?? '—')
                        @php($estadoTexto = $entrada['estado'] ?? $entrada->estado ?? 'Registrada')
                        @php($estadoLower = strtolower($estadoTexto))
                        @php($esPendiente = str_contains($estadoLower, 'pendiente') || str_contains($estadoLower, 'solicit') || str_contains($estadoLower, 'espera'))
                        @php($esCancelado = str_contains($estadoLower, 'cancel') || str_contains($estadoLower, 'rechaz') || str_contains($estadoLower, 'inactiva'))
                        <tr class="pos-card-animate group transition hover:bg-emerald-50/50 dark:hover:bg-emerald-500/[0.04]" data-buscar="{{ $folioEntrada }} {{ $tipoEntrada }} {{ $origenEntrada }} {{ $destinoEntrada }} {{ $fechaEntrada }} {{ $estadoTexto }}" style="animation-delay: {{ $loop->index * 35 }}ms">
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold tracking-tight text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $folioEntrada }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if($tipoEntrada === 'traspaso' || $tipoEntrada === 'traspasos')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/12 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-500/20 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/25">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Traspaso
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/12 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-500/20 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/25">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Compra
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5 text-sm">
                                    <span class="font-medium text-slate-700 dark:text-slate-200">{{ $origenEntrada }}</span>
                                    <span class="text-slate-400 dark:text-slate-500" aria-hidden="true">→</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $destinoEntrada }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-0.5">
                                    @php($detallePresentaciones = collect($entrada['presentaciones_detalle'] ?? []))
                                    @forelse($detallePresentaciones as $presentacion)
                                        <span class="block text-sm leading-tight">
                                            <strong class="font-bold text-slate-900 dark:text-white">{{ $presentacion['cantidad'] }}</strong>
                                            <span class="theme-subtle text-xs">{{ \Illuminate\Support\Str::plural($presentacion['nombre'], $presentacion['cantidad']) }}</span>
                                        </span>
                                    @empty
                                        @php($presentaciones = (int) ($entrada['presentaciones'] ?? 0))
                                        <span class="text-sm leading-tight">
                                            <strong class="font-bold text-slate-900 dark:text-white">{{ $presentaciones }}</strong>
                                            <span class="theme-subtle text-xs">{{ $presentaciones === 1 ? 'presentación' : 'presentaciones' }}</span>
                                        </span>
                                    @endforelse
                                    <span class="block text-[11px] font-medium theme-subtle">{{ $entrada['unidades'] ?? $entrada->unidades ?? 0 }} uds.</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                    <svg class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $fechaEntrada }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($esCancelado)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-bold text-red-700 ring-1 ring-red-500/20 dark:bg-red-500/15 dark:text-red-300 dark:ring-red-500/25">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        {{ $estadoTexto }}
                                    </span>
                                @elseif($esPendiente)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/12 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-500/20 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/25">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        {{ $estadoTexto }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/12 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-500/20 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/25">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        <svg class="h-3 w-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        {{ $estadoTexto }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m0 10V11m0 10l-8-4V7m8 4L4 7"/></svg>
                                    </span>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Sin entradas</p>
                                    <p class="text-sm leading-relaxed theme-subtle">No se encontraron entradas para esta sucursal.</p>
                                    <a href="{{ route('entradas-de-almacen.index') }}" class="theme-button theme-button-primary !py-2 !text-sm">Ver entradas</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr data-vacio style="display: none">
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Sin resultados para esta búsqueda.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
@include('partials.buscador-cliente')
