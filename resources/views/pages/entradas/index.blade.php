<x-layouts::app :title="__('Entradas de almacén')">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Almacén</p>
                    <h1 class="mt-2 text-3xl font-bold">Entradas de almacén</h1>
                    <p class="mt-1 text-sm theme-subtle">Recepción de proveedores (pedidos) y movimientos entre sucursales (traspasos).</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if(Route::has('pedidos.create'))
                        <a href="{{ route('pedidos.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-primary">+ Nuevo pedido</a>
                    @else
                        <a href="{{ route('entradas-de-almacen.index', ['sucursal' => $selectedSucursal?->id, 'tipo' => 'pedidos']) }}" class="theme-button theme-button-primary" title="Filtrar entradas de tipo pedido">+ Nuevo pedido</a>
                    @endif

                    @if(Route::has('traspasos.create'))
                        <a href="{{ route('traspasos.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-secondary">⇄ Nuevo traspaso</a>
                    @else
                        <a href="{{ route('entradas-de-almacen.index', ['sucursal' => $selectedSucursal?->id, 'tipo' => 'traspasos']) }}" class="theme-button theme-button-secondary" title="Filtrar entradas de tipo traspaso">⇄ Nuevo traspaso</a>
                    @endif
                </div>
            </div>

            @php($tipoActual = $tipo ?? request('tipo', 'todas'))
            <nav class="module-tabs" aria-label="Secciones de entradas">
                <a
                    href="{{ route('entradas-de-almacen.index', ['sucursal' => $selectedSucursal?->id]) }}"
                    @class(['module-tab', 'module-tab-active' => in_array($tipoActual, ['todas', '', null], true)])
                    @if(in_array($tipoActual, ['todas', '', null], true)) aria-current="page" @endif
                >Todas las entradas</a>
                <a
                    href="{{ route('entradas-de-almacen.index', ['sucursal' => $selectedSucursal?->id, 'tipo' => 'pedidos']) }}"
                    @class(['module-tab', 'module-tab-active' => $tipoActual === 'pedidos'])
                    @if($tipoActual === 'pedidos') aria-current="page" @endif
                >Pedidos</a>
                <a
                    href="{{ route('entradas-de-almacen.index', ['sucursal' => $selectedSucursal?->id, 'tipo' => 'traspasos']) }}"
                    @class(['module-tab', 'module-tab-active' => $tipoActual === 'traspasos'])
                    @if($tipoActual === 'traspasos') aria-current="page" @endif
                >Traspasos</a>
            </nav>

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

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Entradas</span>
                        <span class="stat-pill info">{{ $totalEntradas ?? ($entradas->count() ?? 0) }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $totalEntradas ?? ($entradas->count() ?? 0) }}</p>
                    <p class="mt-1 text-xs theme-subtle">Registros en la vista</p>
                </div>

                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Pedidos</span>
                        <span class="stat-pill positive">{{ $totalPedidos ?? 0 }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $totalPedidos ?? 0 }}</p>
                    <p class="mt-1 text-xs theme-subtle">Recepción de proveedores</p>
                </div>

                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Traspasos</span>
                        <span class="stat-pill warning">{{ $totalTraspasos ?? 0 }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $totalTraspasos ?? 0 }}</p>
                    <p class="mt-1 text-xs theme-subtle">Movimientos entre sucursales</p>
                </div>

                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Unidades</span>
                        <span class="stat-pill info">Total</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $unidadesRecibidas ?? 0 }}</p>
                    <p class="mt-1 text-xs theme-subtle">Unidades recibidas</p>
                </div>
            </div>

            <div class="module-card p-5 sm:p-6" x-data="buscadorTabla()" x-effect="filtrar($el)">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</h2>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
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

                        <div class="flex items-center gap-2">
                            @if(Route::has('pedidos.index'))
                                <a href="{{ route('pedidos.index', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-secondary whitespace-nowrap">Ver pedidos</a>
                            @endif
                            @if(Route::has('traspasos.index'))
                                <a href="{{ route('traspasos.index', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-secondary whitespace-nowrap">Ver traspasos</a>
                            @endif
                        </div>
                    </div>
                </div>

                @if($tipoActual === 'traspasos')
                    <div class="mt-6 flex flex-col gap-6">
                        <section class="module-table" aria-label="Solicitudes de traspasos">
                            <div class="px-4 pt-4">
                                <h3 class="text-lg font-bold">Solicitudes de traspasos</h3>
                                <p class="mt-1 text-sm theme-subtle">Lo que otras sucursales solicitan a {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }}.</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="text-slate-600 dark:text-slate-300">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Folio</th>
                                            <th class="px-4 py-3 font-semibold">Sucursal origen</th>
                                            <th class="px-4 py-3 font-semibold">Sucursal destino</th>
                                            <th class="px-4 py-3 font-semibold">Solicitado por</th>
                                            <th class="px-4 py-3 font-semibold">Fecha</th>
                                            <th class="px-4 py-3 font-semibold">Estado</th>
                                            <th class="px-4 py-3 font-semibold">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($solicitudes ?? [] as $solicitud)
                                            <tr class="border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5">
                                                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $solicitud['folio'] }}</td>
                                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $solicitud['origen'] }}</td>
                                                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $solicitud['destino'] }}</td>
                                                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $solicitud['solicitado_por'] }}</td>
                                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $solicitud['fecha'] }}</td>
                                                <td class="px-4 py-3">
                                                    <span class="status-badge {{ $solicitud['estado_class'] }}">{{ $solicitud['estado'] }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if(($solicitud['estado_raw'] ?? '') === 'enviado')
                                                        <div class="flex flex-wrap gap-2">
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
                                                        <span class="text-slate-400 dark:text-slate-500">En espera de envío</span>
                                                    @else
                                                        <span class="text-slate-400 dark:text-slate-500">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                                    No hay solicitudes de traspasos para esta sucursal.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="module-table" aria-label="Traspasos solicitados">
                            <div class="px-4 pt-4">
                                <h3 class="text-lg font-bold">Traspasos solicitados</h3>
                                <p class="mt-1 text-sm theme-subtle">Lo que {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }} solicitó a otras sucursales.</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="text-slate-600 dark:text-slate-300">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Folio</th>
                                            <th class="px-4 py-3 font-semibold">Sucursal origen</th>
                                            <th class="px-4 py-3 font-semibold">Sucursal destino</th>
                                            <th class="px-4 py-3 font-semibold">Solicitado por</th>
                                            <th class="px-4 py-3 font-semibold">Fecha</th>
                                            <th class="px-4 py-3 font-semibold">Estado</th>
                                            <th class="px-4 py-3 font-semibold">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($solicitados ?? [] as $solicitado)
                                            <tr class="border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5">
                                                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $solicitado['folio'] }}</td>
                                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $solicitado['origen'] }}</td>
                                                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $solicitado['destino'] }}</td>
                                                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $solicitado['solicitado_por'] }}</td>
                                                <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $solicitado['fecha'] }}</td>
                                                <td class="px-4 py-3">
                                                    <span class="status-badge {{ $solicitado['estado_class'] }}">{{ $solicitado['estado'] }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($solicitado['pendiente'])
                                                        <div class="flex flex-wrap gap-2">
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
                                                        <span class="text-slate-400 dark:text-slate-500">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                                    Esta sucursal no ha solicitado traspasos.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                @else
                <div class="module-table mt-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Folio</th>
                                    <th class="px-4 py-3 font-semibold">Tipo</th>
                                    <th class="px-4 py-3 font-semibold">Origen</th>
                                    <th class="px-4 py-3 font-semibold">Destino</th>
                                    <th class="px-4 py-3 font-semibold">Unidades</th>
                                    <th class="px-4 py-3 font-semibold">Fecha</th>
                                    <th class="px-4 py-3 font-semibold">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entradas ?? [] as $entrada)
                                    <tr class="border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5" data-buscar="{{ ($entrada['folio'] ?? $entrada->folio ?? '') }} {{ ($entrada['origen'] ?? '') }} {{ ($entrada['producto'] ?? '') }}">
                                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $entrada['folio'] ?? $entrada->folio ?? ('#' . ($entrada['id'] ?? $entrada->id ?? '—')) }}</td>
                                        <td class="px-4 py-3">
                                            @php($tipoEntrada = strtolower($entrada['tipo'] ?? $entrada->tipo ?? $tipoActual))
                                            @if($tipoEntrada === 'traspaso' || $tipoEntrada === 'traspasos')
                                                <span class="stat-pill warning">Traspaso</span>
                                            @else
                                                <span class="stat-pill positive">Pedido</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $entrada['origen'] ?? $entrada->origen ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $entrada['destino'] ?? $entrada->destino ?? ($selectedSucursal?->nombre_sucursal ?? '—') }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $entrada['unidades'] ?? $entrada->unidades ?? 0 }} uds.</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $entrada['fecha'] ?? $entrada->fecha ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="status-badge {{ $entrada['estado_class'] ?? '' }}">{{ $entrada['estado'] ?? $entrada->estado ?? 'Registrada' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr data-vacio>
                                        <td colspan="7" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                            No se encontraron entradas para esta sucursal o búsqueda. Usa los botones de Pedidos y Traspasos para registrar movimiento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @include('partials.buscador-cliente')
</x-layouts::app>
