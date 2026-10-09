<x-layouts::app :title="__('Entradas de almacén')">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="module-hero">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="farma-kicker">Almacén</p>
                        <h1 class="mt-2">Entradas de almacén</h1>
                        <p class="mt-1 text-sm theme-subtle">Recepción de proveedores (pedidos) y movimientos entre sucursales (traspasos).</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        @if(Route::has('pedidos.create'))
                            <a href="{{ route('pedidos.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-primary">+ Nuevo pedido</a>
                        @else
                            <a href="{{ route('entradas-de-almacen.index', ['sucursal' => $selectedSucursal?->id, 'tipo' => 'pedidos']) }}" class="theme-button theme-button-primary" title="Filtrar entradas de tipo pedido">+ Nuevo pedido</a>
                        @endif

                        @if(Route::has('traspasos.create'))
                            <a href="{{ route('traspasos.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-secondary">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                <span>Nuevo traspaso</span>
                            </a>
                        @else
                            <a href="{{ route('entradas-de-almacen.index', ['sucursal' => $selectedSucursal?->id, 'tipo' => 'traspasos']) }}" class="theme-button theme-button-secondary" title="Filtrar entradas de tipo traspaso">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                <span>Nuevo traspaso</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @php($tipoActual = $tipo ?? request('tipo', 'todas'))
            @php($totalTodas = $totalEntradas ?? ($entradas->count() ?? 0))
            @php($esTodas = in_array($tipoActual, ['todas', '', null], true))
            @php($esPedidos = $tipoActual === 'pedidos')
            @php($esTraspasos = $tipoActual === 'traspasos')

            <nav class="inventario-segment" aria-label="Secciones de entradas">
                <a
                    href="{{ route('entradas-de-almacen.index', ['sucursal' => $selectedSucursal?->id]) }}"
                    @class(['inventario-seg-btn', 'inventario-seg-btn-activo' => $esTodas])
                    @if($esTodas) aria-current="page" @endif
                >
                    <svg @class(['h-4 w-4', 'invisible' => ! $esTodas]) fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    <span>Todas las entradas</span>
                    <span class="seg-contador">{{ $totalTodas }}</span>
                </a>
                <a
                    href="{{ route('entradas-de-almacen.index', ['sucursal' => $selectedSucursal?->id, 'tipo' => 'pedidos']) }}"
                    @class(['inventario-seg-btn', 'inventario-seg-btn-activo' => $esPedidos])
                    @if($esPedidos) aria-current="page" @endif
                >
                    <svg @class(['h-4 w-4', 'invisible' => ! $esPedidos]) fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    <span>Pedidos</span>
                    <span class="seg-contador">{{ $totalPedidos ?? 0 }}</span>
                </a>
                <a
                    href="{{ route('entradas-de-almacen.index', ['sucursal' => $selectedSucursal?->id, 'tipo' => 'traspasos']) }}"
                    @class(['inventario-seg-btn', 'inventario-seg-btn-activo' => $esTraspasos])
                    @if($esTraspasos) aria-current="page" @endif
                >
                    <svg @class(['h-4 w-4', 'invisible' => ! $esTraspasos]) fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    <span>Traspasos</span>
                    <span class="seg-contador">{{ $totalTraspasos ?? 0 }}</span>
                </a>
            </nav>

            @if($esTodas)
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="module-stat entradas-stat">
                    <span class="entradas-stat-icono total" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m0 10V11m0 10l-8-4V7m8 4L4 7"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="entradas-stat-numero">{{ $totalTodas }}</p>
                        <p class="entradas-stat-etiqueta">Entradas</p>
                        <p class="mt-1 text-xs theme-subtle">Registros en la vista</p>
                    </div>
                </div>

                <div class="module-stat entradas-stat">
                    <span class="entradas-stat-icono positive" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="entradas-stat-numero">{{ $totalPedidos ?? 0 }}</p>
                        <p class="entradas-stat-etiqueta">Pedidos</p>
                        <p class="mt-1 text-xs theme-subtle">Recepción de proveedores</p>
                    </div>
                </div>

                <div class="module-stat entradas-stat">
                    <span class="entradas-stat-icono warning" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="entradas-stat-numero">{{ $totalTraspasos ?? 0 }}</p>
                        <p class="entradas-stat-etiqueta">Traspasos</p>
                        <p class="mt-1 text-xs theme-subtle">Movimientos entre sucursales</p>
                    </div>
                </div>

                <div class="module-stat entradas-stat">
                    <span class="entradas-stat-icono info" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="entradas-stat-numero">{{ $unidadesRecibidas ?? 0 }}</p>
                        <p class="entradas-stat-etiqueta">Unidades</p>
                        <p class="mt-1 text-xs theme-subtle">Unidades recibidas</p>
                    </div>
                </div>
            </div>
            @endif

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
                    class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
                    role="alert"
                >
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-red-600 text-xs font-bold text-white">!</span>
                    {{ session('error') }}
                </div>
            @endif

            <div class="module-card p-5 sm:p-6" x-data="buscadorTabla()" x-effect="filtrar($el)">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</h2>
                    </div>

                    <form method="GET" action="{{ route('entradas-de-almacen.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <input type="hidden" name="tipo" value="{{ $tipoActual === 'todas' ? '' : $tipoActual }}">
                        <label class="flex items-center gap-2 text-sm font-medium">
                            <span class="theme-subtle">Sucursal:</span>
                            <select name="sucursal" class="branch-select" aria-label="Seleccionar sucursal" onchange="this.form.submit()">
                                @forelse($sucursales ?? [] as $sucursal)
                                    <option value="{{ $sucursal->id }}" {{ ($selectedSucursal && $selectedSucursal->id === $sucursal->id) ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @empty
                                    <option value="">Sin sucursales registradas</option>
                                @endforelse
                            </select>
                        </label>
                        <input type="search" name="buscar" value="{{ old('buscar', $busqueda ?? '') }}" placeholder="Buscar folio, proveedor o producto" class="theme-input w-full sm:w-64" aria-label="Buscar entrada" @input.debounce.200ms="texto = $event.target.value">
                        <button type="submit" class="theme-button theme-button-secondary whitespace-nowrap">Buscar</button>
                    </form>
                </div>

                @if($tipoActual === 'traspasos')
                    <div class="mt-6 flex flex-col gap-6">
                        <section class="entradas-seccion" aria-label="Solicitudes de traspasos">
                            <div class="entradas-seccion-head">
                                <span class="entradas-stat-icono warning" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5M7.5 21L3 16.5m0 0L7.5 12"/></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <h3>Solicitudes de traspasos</h3>
                                    <p class="theme-subtle text-sm">Lo que otras sucursales solicitan a {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }}.</p>
                                </div>
                                <span class="seg-contador">{{ ($solicitudes ?? collect())->count() }}</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="entradas-tabla">
                                    <thead>
                                        <tr>
                                            <th>Folio</th>
                                            <th>Sucursal origen</th>
                                            <th>Sucursal destino</th>
                                            <th>Solicitado por</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($solicitudes ?? [] as $solicitud)
                                            <tr>
                                                <td><span class="entradas-folio">{{ $solicitud['folio'] }}</span></td>
                                                <td>{{ $solicitud['origen'] }}</td>
                                                <td>{{ $solicitud['destino'] }}</td>
                                                <td>{{ $solicitud['solicitado_por'] }}</td>
                                                <td class="entradas-fecha">{{ $solicitud['fecha'] }}</td>
                                                <td>
                                                    <span class="status-badge {{ $solicitud['estado_class'] }}">{{ $solicitud['estado'] }}</span>
                                                </td>
                                                <td>
                                                    @if(($solicitud['estado_raw'] ?? '') === 'enviado')
                                                        <div class="entradas-acciones">
                                                            <form method="POST" action="{{ route('traspasos.aceptar', $solicitud['id']) }}">
                                                                @csrf
                                                                <button type="submit" class="theme-button theme-button-primary">Aceptar</button>
                                                            </form>
                                                            <form method="POST" action="{{ route('traspasos.rechazar', $solicitud['id']) }}">
                                                                @csrf
                                                                <button type="submit" class="theme-button theme-button-secondary">Rechazar</button>
                                                            </form>
                                                        </div>
                                                    @elseif(($solicitud['estado_raw'] ?? '') === 'pendiente')
                                                        <span class="entradas-mudo">En espera de envío</span>
                                                    @else
                                                        <span class="entradas-mudo">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">
                                                    <div class="entradas-vacio">
                                                        <span class="entradas-vacio-icono" aria-hidden="true">
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                                        </span>
                                                        <p class="entradas-vacio-titulo">Sin solicitudes</p>
                                                        <p class="theme-subtle text-sm">No hay solicitudes de traspasos para esta sucursal.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="entradas-seccion" aria-label="Traspasos solicitados">
                            <div class="entradas-seccion-head">
                                <span class="entradas-stat-icono info" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5H7.5m0 13.5L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12"/></svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <h3>Traspasos solicitados</h3>
                                    <p class="theme-subtle text-sm">Lo que {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }} solicitó a otras sucursales.</p>
                                </div>
                                <span class="seg-contador">{{ ($solicitados ?? collect())->count() }}</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="entradas-tabla">
                                    <thead>
                                        <tr>
                                            <th>Folio</th>
                                            <th>Sucursal origen</th>
                                            <th>Sucursal destino</th>
                                            <th>Solicitado por</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($solicitados ?? [] as $solicitado)
                                            <tr>
                                                <td><span class="entradas-folio">{{ $solicitado['folio'] }}</span></td>
                                                <td>{{ $solicitado['origen'] }}</td>
                                                <td>{{ $solicitado['destino'] }}</td>
                                                <td>{{ $solicitado['solicitado_por'] }}</td>
                                                <td class="entradas-fecha">{{ $solicitado['fecha'] }}</td>
                                                <td>
                                                    <span class="status-badge {{ $solicitado['estado_class'] }}">{{ $solicitado['estado'] }}</span>
                                                </td>
                                                <td>
                                                    @if($solicitado['pendiente'])
                                                        <div class="entradas-acciones">
                                                            @if(($solicitado['estado_raw'] ?? '') === 'pendiente')
                                                                <form method="POST" action="{{ route('traspasos.enviar', $solicitado['id']) }}">
                                                                    @csrf
                                                                    <button type="submit" class="theme-button theme-button-primary">Enviar</button>
                                                                </form>
                                                            @endif
                                                            <form method="POST" action="{{ route('traspasos.cancelar', $solicitado['id']) }}">
                                                                @csrf
                                                                <button type="submit" class="theme-button theme-button-secondary">Cancelar</button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <span class="entradas-mudo">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">
                                                    <div class="entradas-vacio">
                                                        <span class="entradas-vacio-icono" aria-hidden="true">
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m0 10V11m0 10l-8-4V7m8 4L4 7"/></svg>
                                                        </span>
                                                        <p class="entradas-vacio-titulo">Sin traspasos solicitados</p>
                                                        <p class="theme-subtle text-sm">Esta sucursal no ha solicitado traspasos.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                @else
                <section class="entradas-seccion mt-6" aria-label="Listado de entradas">
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
                    <div class="overflow-x-auto">
                        <table class="entradas-tabla">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Tipo</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th class="entradas-num">Unidades</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entradas ?? [] as $entrada)
                                    <tr data-buscar="{{ ($entrada['folio'] ?? $entrada->folio ?? '') }} {{ ($entrada['origen'] ?? '') }} {{ ($entrada['producto'] ?? '') }}">
                                        <td><span class="entradas-folio">{{ $entrada['folio'] ?? $entrada->folio ?? ('#' . ($entrada['id'] ?? $entrada->id ?? '—')) }}</span></td>
                                        <td>
                                            @php($tipoEntrada = strtolower($entrada['tipo'] ?? $entrada->tipo ?? $tipoActual))
                                            @if($tipoEntrada === 'traspaso' || $tipoEntrada === 'traspasos')
                                                <span class="stat-pill warning">Traspaso</span>
                                            @else
                                                <span class="stat-pill positive">Pedido</span>
                                            @endif
                                        </td>
                                        <td>{{ $entrada['origen'] ?? $entrada->origen ?? '—' }}</td>
                                        <td>{{ $entrada['destino'] ?? $entrada->destino ?? ($selectedSucursal?->nombre_sucursal ?? '—') }}</td>
                                        <td class="entradas-num"><strong>{{ $entrada['unidades'] ?? $entrada->unidades ?? 0 }}</strong> <span class="theme-subtle text-xs">uds.</span></td>
                                        <td class="entradas-fecha">{{ $entrada['fecha'] ?? $entrada->fecha ?? '—' }}</td>
                                        <td>
                                            <span class="status-badge {{ $entrada['estado_class'] ?? '' }}">{{ $entrada['estado'] ?? $entrada->estado ?? 'Registrada' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr data-vacio>
                                        <td colspan="7">
                                            <div class="entradas-vacio">
                                                <span class="entradas-vacio-icono" aria-hidden="true">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m0 10V11m0 10l-8-4V7m8 4L4 7"/></svg>
                                                </span>
                                                <p class="entradas-vacio-titulo">Sin entradas</p>
                                                <p class="theme-subtle text-sm">No se encontraron entradas para esta sucursal o búsqueda.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
                @endif
            </div>
        </div>
    </div>

    @include('partials.buscador-cliente')
</x-layouts::app>
