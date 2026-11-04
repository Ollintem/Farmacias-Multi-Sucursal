<x-layouts::app :title="__('Historial y auditoría — Caja')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            <header class="mb-4 flex flex-wrap items-end justify-between gap-4" aria-label="Encabezado de auditoría">
                <div>
                    <a href="{{ route('caja.index', array_filter(['sucursal' => $sucursal?->id])) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0e9384] hover:text-[#0c7569] dark:text-emerald-300">← Volver a caja</a>
                </div>
                <form method="GET" action="{{ route('caja.auditoria') }}" class="flex items-center gap-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Sucursal</label>
                    <select name="sucursal" onchange="this.form.submit()" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-[#0e9384] focus:outline-none focus:ring-2 focus:ring-[#0e9384]/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}" @selected($sucursal?->id === $s->id)>{{ $s->nombre_sucursal }}</option>
                        @endforeach
                    </select>
                </form>
            </header>

            @if(!$sucursal)
                <div class="module-card p-10 text-center">
                    <p class="text-sm font-semibold text-slate-600 dark:text-zinc-300">No hay sucursal seleccionada.</p>
                </div>
            @else
            <section class="module-card !p-0 overflow-hidden" aria-label="Historial de cortes y auditoría">
                <header class="border-b border-slate-200 bg-white px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-black text-slate-800 dark:text-white">Cierres auditables</h2>
                            <p class="text-xs text-slate-500 dark:text-zinc-400">{{ $historial->count() }} cierre(s) filtrados · {{ $sucursal->nombre_sucursal }} · 2 turnos: Mañana (Matutino) y Tarde (Vespertino)</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#0e9384]"></span> Trazabilidad completa
                        </span>
                    </div>
                </header>

                {{-- Herramientas de auditoría: filtros --}}
                <div class="border-b border-slate-100 bg-slate-50/70 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                    <p class="mb-2 text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-zinc-400">Herramientas de auditoría — filtrar cierres</p>
                    <form method="GET" action="{{ route('caja.auditoria') }}" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-6">
                        <input type="hidden" name="sucursal" value="{{ $sucursal->id }}">
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Desde</span>
                            <input type="date" name="aud_desde" value="{{ $audFiltros['aud_desde'] }}" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Hasta</span>
                            <input type="date" name="aud_hasta" value="{{ $audFiltros['aud_hasta'] }}" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Turno</span>
                            <select name="aud_turno" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                                <option value="">Ambos turnos</option>
                                <option value="Matutino" @selected($audFiltros['aud_turno']==='Matutino')>Mañana — Matutino</option>
                                <option value="Vespertino" @selected($audFiltros['aud_turno']==='Vespertino')>Tarde — Vespertino</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Caja</span>
                            <select name="aud_caja" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                                <option value="">Todas</option>
                                <option value="Caja 1" @selected($audFiltros['aud_caja']==='Caja 1')>Caja 1</option>
                                <option value="Caja 2" @selected($audFiltros['aud_caja']==='Caja 2')>Caja 2</option>
                                <option value="Caja 3" @selected($audFiltros['aud_caja']==='Caja 3')>Caja 3</option>
                                <option value="Caja móvil" @selected($audFiltros['aud_caja']==='Caja móvil')>Caja móvil</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Estado</span>
                            <select name="aud_estado" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                                <option value="">Todos</option>
                                <option value="cuadrado" @selected($audFiltros['aud_estado']==='cuadrado')>Cuadrados</option>
                                <option value="sobrante" @selected($audFiltros['aud_estado']==='sobrante')>Sobrante</option>
                                <option value="faltante" @selected($audFiltros['aud_estado']==='faltante')>Faltante</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Operador</span>
                            <select name="aud_usuario" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                                <option value="">Todos</option>
                                @foreach($usuariosFiltro as $u)
                                    <option value="{{ $u->id }}" @selected($audFiltros['aud_usuario']==(string)$u->id)>{{ $u->nombre }} {{ $u->apellido }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="sm:col-span-2 lg:col-span-6 flex gap-2">
                            <button type="submit" class="theme-button theme-button-secondary !py-2 text-sm">Filtrar auditoría</button>
                            <a href="{{ route('caja.auditoria', ['sucursal'=>$sucursal->id]) }}" class="theme-button theme-button-secondary !py-2 text-sm">Limpiar</a>
                            <a href="{{ route('caja.auditoria', array_merge(['sucursal'=>$sucursal->id], array_filter($audFiltros))) }}" onclick="window.print(); return false;" class="ml-auto inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">🖨 Imprimir / PDF</a>
                        </div>
                    </form>
                </div>

                {{-- Resumen de auditoría --}}
                @if($auditoriaResumen)
                    <div class="grid grid-cols-2 gap-px bg-slate-200 dark:bg-zinc-800 lg:grid-cols-5">
                        <div class="bg-white px-3 py-3 text-center dark:bg-zinc-900">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Cierres</p>
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

                <div class="overflow-x-auto">
                    <table class="entradas-tabla !min-w-[860px]">
                        <thead>
                            <tr>
                                <th>Caja / Turno</th>
                                <th>Operador · Auditoría</th>
                                <th class="text-right">Inicial</th>
                                <th class="text-right">Esperado</th>
                                <th class="text-right">Declarado</th>
                                <th class="text-center">Diferencia</th>
                                <th>Cierre</th>
                                <th class="text-center">Trazas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historial as $h)
                                @php
                                    $dif = (float) $h->diferencia;
                                    $duracion = $h->fecha_inicio->diffForHumans($h->fecha_cierre, ['short'=>true]);
                                    $movs = $h->movimientos->count();
                                    $ventasCount = $h->ventas->count();
                                @endphp
                                <tr>
                                    <td>
                                        <span class="font-bold text-slate-800 dark:text-white">{{ $h->caja }}</span>
                                        <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[11px] font-bold {{ $h->turno==='Matutino' ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300' : 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300' }}">{{ $h->turno==='Matutino' ? 'Mañana' : 'Tarde' }}</span>
                                        @if($h->cierre_automatico)
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-1.5 py-0.5 text-[11px] font-bold text-red-700 dark:bg-red-500/15 dark:text-red-300" title="El sistema cerró la caja al vencer la tolerancia del turno">Auto</span>
                                        @endif<br>
                                        <span class="text-xs text-slate-400">{{ $h->fecha_inicio->format('d/m H:i') }} → {{ $h->fecha_cierre?->format('H:i') }}</span>
                                        <span class="text-[11px] text-slate-400">· {{ $duracion }}</span>
                                    </td>
                                    <td class="text-sm">
                                        <span class="font-semibold text-slate-800 dark:text-white">{{ $h->usuario?->nombre }} {{ $h->usuario?->apellido }}</span><br>
                                        <span class="text-xs text-slate-400">ID {{ $h->id_usuario }} · Suc. {{ $h->id_sucursal }}</span>
                                        <span class="block text-[11px] text-slate-400" title="Apertura {{ $h->fecha_inicio->format('Y-m-d H:i:s') }} / Cierre {{ $h->fecha_cierre?->format('Y-m-d H:i:s') }}">Apertura: {{ $h->fecha_inicio->format('H:i') }} · Cierre: {{ $h->fecha_cierre?->format('H:i') ?? '—' }}</span>
                                    </td>
                                    <td class="text-right tabular-nums">${{ number_format((float) $h->efectivo_inicial,2) }}</td>
                                    <td class="text-right tabular-nums">${{ number_format((float) $h->efectivo_esperado,2) }}</td>
                                    <td class="text-right tabular-nums font-bold">${{ number_format((float) $h->efectivo_declarado,2) }}</td>
                                    <td class="text-center">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $dif == 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : ($dif > 0 ? 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300') }}">
                                            {{ $dif > 0 ? '+' : '' }}${{ number_format($dif,2) }}
                                        </span>
                                    </td>
                                    <td class="text-xs text-slate-500">{{ $h->fecha_cierre?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="inline-flex flex-col items-center gap-0.5">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600 dark:bg-zinc-800 dark:text-zinc-300" title="{{ $movs }} movimientos auditados">{{ $movs }} mov.</span>
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300" title="{{ $ventasCount }} ventas ligadas al corte">{{ $ventasCount }} vtas.</span>
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-zinc-400">Sin cierres que auditar con los filtros actuales. Limpia los filtros o revisa otra sucursal.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 bg-slate-50 px-4 py-3 text-xs leading-relaxed text-slate-500 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <span class="font-bold text-slate-700 dark:text-white">Trazabilidad:</span> cada apertura, movimiento y cierre queda registrado con <span class="font-semibold">usuario, caja, turno (Mañana/Tarde), sucursal y timestamp</span>.
                </div>
            </section>
            @endif
        </div>
    </div>
</x-layouts::app>
