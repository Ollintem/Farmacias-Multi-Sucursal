<x-layouts::app :title="__('Entradas de almacén')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            @php($tipoActual = $tipo ?? request('tipo', 'todas'))
            @php($totalTodas = $totalEntradas ?? ($entradas->count() ?? 0))
            @php($esTodas = in_array($tipoActual, ['todas', '', null], true))
            @php($esPedidos = $tipoActual === 'pedidos')
            @php($esTraspasos = $tipoActual === 'traspasos')

            {{-- Encabezado con botones a la derecha --}}
            <header class="mb-6" aria-label="Encabezado de entradas de almacén">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Almacén · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Entradas de almacén</h1>
                        <p class="theme-subtle mt-1 text-sm">
                            @if($esTodas) Pedidos y traspasos recibidos
                            @elseif($esPedidos) Recepción de pedidos de proveedores
                            @else Gestión de traspasos entre sucursales @endif
                            · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        @if(Route::has('pedidos.create'))
                            <a href="{{ route('pedidos.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-primary whitespace-nowrap">
                                + Nuevo pedido
                            </a>
                        @endif
                        @if(Route::has('traspasos.create'))
                            <a href="{{ route('traspasos.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-secondary whitespace-nowrap">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                <span>Nuevo traspaso</span>
                            </a>
                        @endif
                    </div>
                </div>
            </header>

            @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2000)" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2000)" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-red-600 text-xs font-bold text-white">!</span>
                    {{ session('error') }}
                </div>
            @endif

            <section class="module-card noti-listado noti-listado--formal" aria-label="Listado de entradas" x-data="{ traspasoModal: null }">
                <header class="noti-listado-cabecera noti-listado-cabecera--formal">
                    <div>
                        <h2>Listado de entradas</h2>
                        <p class="theme-subtle text-sm">{{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }} · {{ $totalTodas }} registro(s)</p>
                    </div>
                </header>

                <div class="p-5 sm:p-6">
                @if($tipoActual === 'traspasos')
                    {{-- Dos tarjetas como únicos botones --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        <button type="button" @click="traspasoModal = 'solicitudes'" class="group flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50/70 p-5 text-left shadow-sm transition hover:border-amber-300 hover:bg-amber-50 hover:shadow-md dark:border-amber-500/20 dark:bg-amber-500/10 dark:hover:bg-amber-500/15 text-slate-900 dark:text-white">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/20">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold leading-none">Solicitudes de traspaso</p>
                                <p class="theme-subtle mt-1 text-xs leading-snug">Lo que otras sucursales solicitan a {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }}.</p>
                            </div>
                            <span class="grid h-8 min-w-[2rem] place-items-center rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">{{ ($solicitudes ?? collect())->count() }}</span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <button type="button" @click="traspasoModal = 'solicitados'" class="group flex items-center gap-4 rounded-2xl border border-sky-200 bg-sky-50/70 p-5 text-left shadow-sm transition hover:border-sky-300 hover:bg-sky-50 hover:shadow-md dark:border-sky-500/20 dark:bg-sky-500/10 dark:hover:bg-sky-500/15 text-slate-900 dark:text-white">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-700 ring-1 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-500/20">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5H7.5m0 13.5L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold leading-none">Traspasos<span> </span>solicitados</p>
                                <p class="theme-subtle mt-1 text-xs leading-snug">Lo que {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }} solicitó a otras sucursales.</p>
                            </div>
                            <span class="grid h-8 min-w-[2rem] place-items-center rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">{{ ($solicitados ?? collect())->count() }}</span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                    {{-- Modal Solicitudes — solo cierra con el tache --}}
                    <div x-show="traspasoModal === 'solicitudes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
                        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
                        <div x-show="traspasoModal === 'solicitudes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative flex max-h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-[#0b1322]" @click.stop>
                            <header class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-5 py-4 dark:border-slate-700 dark:bg-slate-900/40">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg></span>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Solicitudes de traspasos</h3>
                                        <p class="theme-subtle text-xs">{{ ($solicitudes ?? collect())->count() }} solicitud(es) · {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="traspasoModal = null" class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </header>
                            <div class="flex-1 overflow-auto">
                                <div class="entradas-tabla-wrap !rounded-none !border-0">
                                    <table class="entradas-tabla entradas-tabla--compact">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Movimiento</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th class="text-right">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($solicitudes ?? [] as $solicitud)
                                                <tr>
                                                    <td><span class="entradas-folio">{{ $solicitud['folio'] }}</span></td>
                                                    <td>
                                                        <div class="entradas-ruta">
                                                            <span class="entradas-ruta-origen">{{ $solicitud['origen'] }}</span>
                                                            <span class="entradas-ruta-sep" aria-hidden="true">→</span>
                                                            <span class="entradas-ruta-destino">{{ $solicitud['destino'] }}</span>
                                                        </div>
                                                        <span class="entradas-ruta-meta">Solicitado por {{ $solicitud['solicitado_por'] }}</span>
                                                    </td>
                                                    <td class="entradas-fecha">{{ $solicitud['fecha'] }}</td>
                                                    <td><span class="status-badge {{ $solicitud['estado_class'] }}">{{ $solicitud['estado'] }}</span></td>
                                                    <td class="text-right">
                                                        @if(($solicitud['estado_raw'] ?? '') === 'enviado')
                                                            <div class="flex items-center justify-end gap-2">
                                                                <form method="POST" action="{{ route('traspasos.aceptar', $solicitud['id']) }}">@csrf<button type="submit" class="theme-button theme-button-primary !min-h-0 !px-3 !py-1.5 !text-xs">Aceptar</button></form>
                                                                <form method="POST" action="{{ route('traspasos.rechazar', $solicitud['id']) }}">@csrf<button type="submit" class="theme-button theme-button-secondary !min-h-0 !px-3 !py-1.5 !text-xs">Rechazar</button></form>
                                                            </div>
                                                        @elseif(($solicitud['estado_raw'] ?? '') === 'pendiente')
                                                            <span class="entradas-mudo">En espera de envío</span>
                                                        @else
                                                            <span class="entradas-mudo">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5"><div class="entradas-vacio"><p class="entradas-vacio-titulo">Sin solicitudes</p><p class="theme-subtle text-sm">No hay solicitudes para esta sucursal.</p></div></td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Solicitados — solo cierra con el tache --}}
                    <div x-show="traspasoModal === 'solicitados'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
                        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
                        <div x-show="traspasoModal === 'solicitados'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative flex max-h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-[#0b1322]" @click.stop>
                            <header class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-5 py-4 dark:border-slate-700 dark:bg-slate-900/40">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5H7.5m0 13.5L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12"/></svg></span>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Traspasos solicitados</h3>
                                        <p class="theme-subtle text-xs">{{ ($solicitados ?? collect())->count() }} traspaso(s) · {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="traspasoModal = null" class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </header>
                            <div class="flex-1 overflow-auto">
                                <div class="entradas-tabla-wrap !rounded-none !border-0">
                                    <table class="entradas-tabla entradas-tabla--compact">
                                        <thead>
                                            <tr>
                                                <th>Folio</th>
                                                <th>Movimiento</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th class="text-right">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($solicitados ?? [] as $solicitado)
                                                <tr>
                                                    <td><span class="entradas-folio">{{ $solicitado['folio'] }}</span></td>
                                                    <td>
                                                        <div class="entradas-ruta">
                                                            <span class="entradas-ruta-origen">{{ $solicitado['origen'] }}</span>
                                                            <span class="entradas-ruta-sep" aria-hidden="true">→</span>
                                                            <span class="entradas-ruta-destino">{{ $solicitado['destino'] }}</span>
                                                        </div>
                                                        <span class="entradas-ruta-meta">Solicitado por {{ $solicitado['solicitado_por'] }}</span>
                                                    </td>
                                                    <td class="entradas-fecha">{{ $solicitado['fecha'] }}</td>
                                                    <td><span class="status-badge {{ $solicitado['estado_class'] }}">{{ $solicitado['estado'] }}</span></td>
                                                    <td class="text-right">
                                                        @if($solicitado['pendiente'])
                                                            <div class="flex items-center justify-end gap-2">
                                                                @if(($solicitado['estado_raw'] ?? '') === 'pendiente')
                                                                    <form method="POST" action="{{ route('traspasos.enviar', $solicitado['id']) }}">@csrf<button type="submit" class="theme-button theme-button-primary !min-h-0 !px-3 !py-1.5 !text-xs">Enviar</button></form>
                                                                @endif
                                                                <form method="POST" action="{{ route('traspasos.cancelar', $solicitado['id']) }}">@csrf<button type="submit" class="theme-button theme-button-secondary !min-h-0 !px-3 !py-1.5 !text-xs">Cancelar</button></form>
                                                            </div>
                                                        @else
                                                            <span class="entradas-mudo">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5"><div class="entradas-vacio"><p class="entradas-vacio-titulo">Sin traspasos solicitados</p><p class="theme-subtle text-sm">Esta sucursal no ha solicitado traspasos.</p></div></td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                <section class="entradas-seccion" aria-label="Listado de entradas">
                    <div class="entradas-seccion-head">
                        <span class="entradas-stat-icono total" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m0 10V11m0 10l-8-4V7m8 4L4 7"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3>{{ $tipoActual === 'pedidos' ? 'Pedidos recibidos' : 'Todas las entradas' }}</h3>
                            <p class="theme-subtle text-sm">{{ $tipoActual === 'pedidos' ? 'Recepción de proveedores en ' . ($selectedSucursal?->nombre_sucursal ?? 'la sucursal') . '.' : 'Pedidos y traspasos recibidos en ' . ($selectedSucursal?->nombre_sucursal ?? 'la sucursal') . '.' }}</p>
                        </div>
                        <span class="seg-contador">{{ ($entradas ?? collect())->count() }}</span>
                    </div>
                    <div class="entradas-tabla-wrap">
                        <table class="entradas-tabla entradas-tabla--compact">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Tipo</th>
                                    <th>Movimiento</th>
                                    <th class="entradas-num">Unidades</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entradas ?? [] as $entrada)
                                    <tr>
                                        <td><span class="entradas-folio">{{ $entrada['folio'] ?? $entrada->folio ?? ('#' . ($entrada['id'] ?? $entrada->id ?? '—')) }}</span></td>
                                        <td>
                                            @php($tipoEntrada = strtolower($entrada['tipo'] ?? $entrada->tipo ?? $tipoActual))
                                            @if($tipoEntrada === 'traspaso' || $tipoEntrada === 'traspasos')
                                                <span class="stat-pill warning">Traspaso</span>
                                            @else
                                                <span class="stat-pill positive">Pedido</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="entradas-ruta">
                                                <span class="entradas-ruta-origen">{{ $entrada['origen'] ?? $entrada->origen ?? '—' }}</span>
                                                <span class="entradas-ruta-sep" aria-hidden="true">→</span>
                                                <span class="entradas-ruta-destino">{{ $entrada['destino'] ?? $entrada->destino ?? ($selectedSucursal?->nombre_sucursal ?? '—') }}</span>
                                            </div>
                                        </td>
                                        <td class="entradas-num"><strong>{{ $entrada['unidades'] ?? $entrada->unidades ?? 0 }}</strong> <span class="theme-subtle text-xs">uds.</span></td>
                                        <td class="entradas-fecha">{{ $entrada['fecha'] ?? $entrada->fecha ?? '—' }}</td>
                                        <td><span class="status-badge {{ $entrada['estado_class'] ?? '' }}">{{ $entrada['estado'] ?? $entrada->estado ?? 'Registrada' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6"><div class="entradas-vacio"><p class="entradas-vacio-titulo">Sin entradas</p><p class="theme-subtle text-sm">No se encontraron entradas para esta sucursal.</p></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
                @endif
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
