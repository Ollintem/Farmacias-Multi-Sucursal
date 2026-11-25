<x-layouts::app :title="__('Historial y auditoría — Caja')">
    <div class="module-page pos-page noti-page relative isolate !overflow-hidden scrollbar-hide">
        {{-- Fondo agua POS — idéntico a Punto de venta (livewire/punto-de-venta) --}}
        <div class="pos-water" aria-hidden="true">
            <div class="pos-water-gradient"></div>
            <div class="pos-water-highlight"></div>
            <div class="pos-water-layer pos-water-layer--back">
                <div class="pos-wave-track pos-wave-track--slow">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,60 C150,85 350,35 600,60 C850,85 1050,35 1200,60 L1200,120 L0,120 Z" fill="rgba(6,182,212,0.20)"/></svg>
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,60 C150,85 350,35 600,60 C850,85 1050,35 1200,60 L1200,120 L0,120 Z" fill="rgba(6,182,212,0.20)"/></svg>
                </div>
            </div>
            <div class="pos-water-layer pos-water-layer--mid">
                <div class="pos-wave-track pos-wave-track--mid">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,55 C180,90 380,30 600,58 C820,86 1000,28 1200,55 L1200,120 L0,120 Z" fill="rgba(14,147,132,0.18)"/></svg>
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,55 C180,90 380,30 600,58 C820,86 1000,28 1200,55 L1200,120 L0,120 Z" fill="rgba(14,147,132,0.18)"/></svg>
                </div>
            </div>
            <div class="pos-water-layer">
                <div class="pos-wave-track pos-wave-track--fast">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,65 C160,95 340,25 600,65 C860,105 1020,20 1200,65 L1200,120 L0,120 Z" fill="rgba(34,211,238,0.24)"/></svg>
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,65 C160,95 340,25 600,65 C860,105 1020,20 1200,65 L1200,120 L0,120 Z" fill="rgba(34,211,238,0.24)"/></svg>
                </div>
            </div>
        </div>
        <div class="module-page-inner noti-full noti-formal relative z-10">
            <header class="mb-4 flex flex-wrap items-end justify-between gap-4" aria-label="Encabezado de auditoría">
                <div>
                    <a href="{{ route('caja.index', array_filter(['sucursal' => $sucursal?->id])) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0e9384] hover:text-[#0c7569] dark:text-emerald-300">← Volver a caja</a>
                </div>
                <form method="GET" action="{{ route('caja.auditoria') }}" class="flex items-center gap-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Sucursal</label>
                    <select name="sucursal" onchange="this.form.submit()" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-[#0e9384] focus:outline-none focus:ring-2 focus:ring-[#0e9384]/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}" @selected($sucursal?->id === $s->id)>{{ $s->nombre_sucursal }}</option>
                        @endforeach
                    </select>
                </form>
            </header>

            @if(!$sucursal)
                <div class="module-card p-10 text-center">
                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No hay sucursal seleccionada.</p>
                </div>
            @else
            <section class="module-card pos-glass-highlight !p-0 overflow-hidden !bg-white/70 backdrop-blur-xl supports-[backdrop-filter]:!bg-white/60 !border-white/50 shadow-xl shadow-teal-900/5 dark:!bg-slate-800/60 dark:!border-white/10 flex flex-col flex-1 min-h-[520px] lg:min-h-[640px]" aria-label="Historial de cortes y auditoría" x-data="buscadorTabla()" x-effect="filtrar($el)">
                <header class="border-b border-slate-200 bg-white px-5 py-4 dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-black text-slate-800 dark:text-white">Cierres auditables</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $historial->count() }} cierre(s) filtrados · {{ $sucursal->nombre_sucursal }} · 2 turnos: Mañana (Matutino) y Tarde (Vespertino)</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#0e9384]"></span> Trazabilidad completa
                        </span>
                    </div>
                </header>

                {{-- Herramientas de auditoría: filtros --}}
                <div class="border-b border-slate-100 bg-slate-50/70 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/50">
                    <p class="mb-2 text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Herramientas de auditoría — filtrar cierres</p>
                    <form method="GET" action="{{ route('caja.auditoria') }}" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-6">
                        <input type="hidden" name="sucursal" value="{{ $sucursal->id }}">
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Desde</span>
                            <input type="date" name="aud_desde" value="{{ $audFiltros['aud_desde'] }}" class="theme-input mt-1">
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Hasta</span>
                            <input type="date" name="aud_hasta" value="{{ $audFiltros['aud_hasta'] }}" class="theme-input mt-1">
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Turno</span>
                            <select name="aud_turno" class="theme-input mt-1">
                                <option value="">Ambos turnos</option>
                                <option value="Matutino" @selected($audFiltros['aud_turno']==='Matutino')>Mañana — Matutino</option>
                                <option value="Vespertino" @selected($audFiltros['aud_turno']==='Vespertino')>Tarde — Vespertino</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Caja</span>
                            <select name="aud_caja" class="theme-input mt-1">
                                <option value="">Todas</option>
                                <option value="Caja 1" @selected($audFiltros['aud_caja']==='Caja 1')>Caja 1</option>
                                <option value="Caja 2" @selected($audFiltros['aud_caja']==='Caja 2')>Caja 2</option>
                                <option value="Caja 3" @selected($audFiltros['aud_caja']==='Caja 3')>Caja 3</option>
                                <option value="Caja móvil" @selected($audFiltros['aud_caja']==='Caja móvil')>Caja móvil</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Estado</span>
                            <select name="aud_estado" class="theme-input mt-1">
                                <option value="">Todos</option>
                                <option value="cuadrado" @selected($audFiltros['aud_estado']==='cuadrado')>Cuadrados</option>
                                <option value="sobrante" @selected($audFiltros['aud_estado']==='sobrante')>Sobrante</option>
                                <option value="faltante" @selected($audFiltros['aud_estado']==='faltante')>Faltante</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Operador</span>
                            <select name="aud_usuario" class="theme-input mt-1">
                                <option value="">Todos</option>
                                @foreach($usuariosFiltro as $u)
                                    <option value="{{ $u->id }}" @selected($audFiltros['aud_usuario']==(string)$u->id)>{{ $u->nombre }} {{ $u->apellido }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="sm:col-span-2 lg:col-span-6 flex gap-2">
                            <button type="submit" class="theme-button theme-button-primary !py-2 text-sm">Filtrar auditoría</button>
                            <a href="{{ route('caja.auditoria', ['sucursal'=>$sucursal->id]) }}" class="theme-button theme-button-secondary !py-2 text-sm">Limpiar</a>
                            <a href="{{ route('caja.auditoria', array_merge(['sucursal'=>$sucursal->id], array_filter($audFiltros))) }}" onclick="window.print(); return false;" class="theme-button theme-button-secondary !py-2 text-sm ml-auto inline-flex items-center gap-1.5">🖨 Imprimir / PDF</a>
                        </div>
                    </form>
                </div>

                {{-- Resumen de auditoría --}}
                @if($auditoriaResumen)
                    <div class="grid grid-cols-2 gap-px bg-slate-200 dark:bg-slate-800 lg:grid-cols-5">
                        <div class="bg-white px-3 py-3 text-center dark:bg-slate-900">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Cierres</p>
                            <p class="mt-1 text-lg font-black text-slate-900 dark:text-white">{{ $auditoriaResumen['totalCierres'] }}</p>
                            <p class="text-[11px] text-slate-400">en rango filtrado</p>
                        </div>
                        <div class="bg-emerald-50 px-3 py-3 text-center dark:bg-emerald-500/10">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Cuadrados</p>
                            <p class="mt-1 text-lg font-black text-emerald-700 dark:text-emerald-300">{{ $auditoriaResumen['cuadrados'] }}</p>
                            <p class="text-[11px] text-emerald-600/70 dark:text-emerald-300/70">diferencia 0</p>
                        </div>
                        <div class="bg-red-50 px-3 py-3 text-center dark:bg-red-500/10">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-red-700 dark:text-red-300">Faltantes</p>
                            <p class="mt-1 text-lg font-black text-red-700 dark:text-red-300">{{ $auditoriaResumen['faltantes'] }}</p>
                            <p class="text-[11px] text-red-600/70 dark:text-red-300/70">declarado &lt; esperado</p>
                        </div>
                        <div class="bg-sky-50 px-3 py-3 text-center dark:bg-sky-500/10">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-sky-700 dark:text-sky-300">Sobrantes</p>
                            <p class="mt-1 text-lg font-black text-sky-700 dark:text-sky-300">{{ $auditoriaResumen['sobrantes'] }}</p>
                            <p class="text-[11px] text-sky-600/70 dark:text-sky-300/70">declarado &gt; esperado</p>
                        </div>
                        <div class="bg-amber-50 px-3 py-3 text-center dark:bg-amber-500/10">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-300">Balance neto</p>
                            <p class="mt-1 text-lg font-black {{ $auditoriaResumen['sumDiferencia'] == 0 ? 'text-slate-700 dark:text-white' : ($auditoriaResumen['sumDiferencia'] > 0 ? 'text-sky-700 dark:text-sky-300' : 'text-red-700 dark:text-red-300') }}">{{ $auditoriaResumen['sumDiferencia'] > 0 ? '+' : '' }}${{ number_format($auditoriaResumen['sumDiferencia'],2) }}</p>
                            <p class="text-[11px] text-slate-400">Σ declarado − esperado</p>
                        </div>
                    </div>
                @endif

                {{-- Buscador cliente (filtrado instantáneo) --}}
                <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/30 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative flex-1 sm:max-w-sm">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z"/></svg>
                        <input type="search" placeholder="Buscar caja, turno u operador" class="theme-input !py-2.5 !pl-9" aria-label="Buscar en auditoría" @input.debounce.200ms="texto = $event.target.value">
                    </div>
                    <p class="theme-subtle hidden text-xs sm:block">Filtra al instante · búsqueda en cliente</p>
                </div>

                <div class="p-4">
                <div class="module-table">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm !min-w-[860px]">
                            <thead class="bg-slate-50/70 dark:bg-slate-900/40 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                                <tr class="border-b border-slate-200 dark:border-slate-700">
                                    <th class="px-4 py-3.5">Caja / Turno</th>
                                    <th class="px-4 py-3.5">Operador · Auditoría</th>
                                    <th class="px-4 py-3.5 text-right">Inicial</th>
                                    <th class="px-4 py-3.5 text-right">Esperado</th>
                                    <th class="px-4 py-3.5 text-right">Declarado</th>
                                    <th class="px-4 py-3.5 text-center">Diferencia</th>
                                    <th class="px-4 py-3.5">Cierre</th>
                                    <th class="px-4 py-3.5 text-center">Trazas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($historial as $h)
                                    @php
                                        $dif = (float) $h->diferencia;
                                        $duracion = $h->fecha_inicio->diffForHumans($h->fecha_cierre, ['short'=>true]);
                                        $movs = $h->movimientos->count();
                                        $ventasCount = $h->ventas->count();
                                    @endphp
                                    <tr class="pos-card-animate transition hover:bg-emerald-50/50 dark:hover:bg-emerald-500/[0.04]" style="animation-delay: {{ $loop->index * 35 }}ms" data-buscar="{{ $h->caja }} {{ $h->turno }} {{ $h->usuario?->nombre }} {{ $h->usuario?->apellido }} {{ $h->fecha_inicio->format('d/m/Y') }} {{ $h->fecha_cierre?->format('d/m/Y') }}">
                                        <td class="px-4 py-3">
                                            <span class="font-bold text-slate-800 dark:text-white">{{ $h->caja }}</span>
                                            <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[11px] font-bold {{ $h->turno==='Matutino' ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300' : 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300' }}">{{ $h->turno==='Matutino' ? 'Mañana' : 'Tarde' }}</span>
                                            @if($h->cierre_automatico)
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-1.5 py-0.5 text-[11px] font-bold text-red-700 dark:bg-red-500/15 dark:text-red-300" title="El sistema cerró la caja al vencer la tolerancia del turno">Auto</span>
                                            @endif<br>
                                            <span class="text-xs text-slate-400">{{ $h->fecha_inicio->format('d/m H:i') }} → {{ $h->fecha_cierre?->format('H:i') }}</span>
                                            <span class="text-[11px] text-slate-400">· {{ $duracion }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="font-semibold text-slate-800 dark:text-white">{{ $h->usuario?->nombre }} {{ $h->usuario?->apellido }}</span><br>
                                            <span class="text-xs text-slate-400">ID {{ $h->id_usuario }} · Suc. {{ $h->id_sucursal }}</span>
                                            <span class="block text-[11px] text-slate-400" title="Apertura {{ $h->fecha_inicio->format('Y-m-d H:i:s') }} / Cierre {{ $h->fecha_cierre?->format('Y-m-d H:i:s') }}">Apertura: {{ $h->fecha_inicio->format('H:i') }} · Cierre: {{ $h->fecha_cierre?->format('H:i') ?? '—' }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums">${{ number_format((float) $h->efectivo_inicial,2) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums">${{ number_format((float) $h->efectivo_esperado,2) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums font-bold">${{ number_format((float) $h->efectivo_declarado,2) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $dif == 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : ($dif > 0 ? 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300') }}">
                                                {{ $dif > 0 ? '+' : '' }}${{ number_format($dif,2) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-slate-500">{{ $h->fecha_cierre?->format('d/m/Y H:i') ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex flex-col items-center gap-0.5">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300" title="{{ $movs }} movimientos auditados">{{ $movs }} mov.</span>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300" title="{{ $ventasCount }} ventas ligadas al corte">{{ $ventasCount }} vtas.</span>
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Sin cierres que auditar con los filtros actuales. Limpia los filtros o revisa otra sucursal.</td></tr>
                                @endforelse
                                <tr data-vacio @if($historial->isNotEmpty()) style="display: none" @endif>
                                    <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Sin resultados para esta búsqueda.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
                <div class="border-t border-slate-100 bg-slate-50 px-4 py-3 text-xs leading-relaxed text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                    <span class="font-bold text-slate-700 dark:text-white">Trazabilidad:</span> cada apertura, movimiento y cierre queda registrado con <span class="font-semibold">usuario, caja, turno (Mañana/Tarde), sucursal y timestamp</span>.
                </div>
            </section>
            @endif
        </div>
    </div>

    @include('partials.buscador-cliente')
</x-layouts::app>
