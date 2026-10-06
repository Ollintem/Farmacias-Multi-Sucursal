<x-layouts::app :title="__('Alertas')">
    <div class="module-page">
        <div class="module-page-inner space-y-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-600 dark:text-emerald-300">Operación</p>
                    <h1 class="mt-2 text-3xl font-bold">Alertas</h1>
                    <p class="mt-1 text-sm theme-subtle">Bandeja de notificaciones de {{ $selectedSucursal?->nombre_sucursal ?? 'la sucursal' }}.</p>
                </div>

                <form method="GET" action="{{ route('alertas.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="seccion" value="{{ $seccion }}">
                    <input type="hidden" name="nivel" value="{{ $nivel }}">
                    <label class="flex items-center gap-2 text-sm font-medium">
                        <span class="theme-subtle">Sucursal:</span>
                        <select name="sucursal" class="branch-select" aria-label="Seleccionar sucursal" onchange="this.form.submit()">
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" {{ $selectedSucursal && $selectedSucursal->id === $sucursal->id ? 'selected' : '' }}>
                                    {{ $sucursal->nombre_sucursal }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </form>
            </div>

            @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2500)" x-show="show" class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex flex-col gap-2 sm:flex-row" role="tablist" aria-label="Secciones de alertas">
                <a href="{{ route('alertas.index', ['sucursal' => $selectedSucursal?->id, 'seccion' => 'traspasos']) }}"
                   @class(['theme-button flex-1 justify-center', 'theme-button-primary' => $seccion === 'traspasos', 'theme-button-secondary' => $seccion !== 'traspasos'])
                   @if($seccion === 'traspasos') aria-current="page" @endif>
                    ⇄ Traspasos recibidos
                    <span class="stat-pill {{ $totalPendientes > 0 ? 'warning' : 'info' }} ml-2">{{ $totalPendientes }}</span>
                </a>
                <a href="{{ route('alertas.index', ['sucursal' => $selectedSucursal?->id, 'seccion' => 'caducidades', 'nivel' => 'rojo']) }}"
                   @class(['theme-button flex-1 justify-center', 'theme-button-primary' => $seccion === 'caducidades', 'theme-button-secondary' => $seccion !== 'caducidades'])
                   @if($seccion === 'caducidades') aria-current="page" @endif>
                    ◉ Caducidades
                    <span class="stat-pill danger ml-2">{{ $totalesCaducidad['rojo'] }}</span>
                </a>
                <a href="{{ route('alertas.index', ['sucursal' => $selectedSucursal?->id, 'seccion' => 'ventas']) }}"
                   @class(['theme-button flex-1 justify-center', 'theme-button-primary' => $seccion === 'ventas', 'theme-button-secondary' => $seccion !== 'ventas'])
                   @if($seccion === 'ventas') aria-current="page" @endif>
                    ▣ Ventas de hoy
                    <span class="stat-pill info ml-2">{{ $resumenVentas['total_ventas'] }}</span>
                </a>
            </div>

            @if($seccion === 'traspasos')
                <section class="module-card p-5 sm:p-6">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                        <div>
                            <h2 class="text-xl font-bold">Bandeja de entrada</h2>
                            <p class="mt-1 text-sm theme-subtle">Solicitudes que otras sucursales enviaron a {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }}. Las nuevas solicitudes se crean en Almacén.</p>
                        </div>
                        <span class="stat-pill {{ $totalPendientes > 0 ? 'warning' : 'positive' }}">{{ $totalPendientes }} pendientes</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse($pendientesRecibidos as $traspaso)
                            <article class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50 px-4 py-2.5 text-xs dark:border-slate-700 dark:bg-slate-800/60">
                                    <span class="font-bold tracking-widest text-amber-600 uppercase dark:text-amber-300">T-{{ $traspaso->id }} · Pendiente</span>
                                    <span class="theme-subtle">{{ $traspaso->creado_en?->format('Y-m-d H:i') }} · {{ $traspaso->sucursalOrigen?->nombre_sucursal ?? 'Origen' }}</span>
                                </div>
                                <div class="flex flex-col gap-4 p-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-lg font-bold">{{ $traspaso->detalles->first()?->lote?->producto?->nombre_producto ?? 'Lotes solicitados' }}</h3>
                                        <p class="mt-1 text-sm font-semibold text-emerald-700 dark:text-emerald-300">{{ (int) $traspaso->detalles->sum('cantidad') }} uds. solicitadas en {{ $traspaso->detalles->count() }} lote(s)</p>
                                        <p class="mt-1 text-sm theme-subtle">Pidió {{ $traspaso->solicitadoPor?->name ?? 'usuario' }}</p>
                                        @if($traspaso->mensaje)
                                            <blockquote class="mt-3 rounded-xl border-l-4 border-amber-300 bg-amber-50 px-3 py-2 text-sm italic text-amber-900 dark:border-amber-500/50 dark:bg-amber-500/10 dark:text-amber-100">“{{ $traspaso->mensaje }}”</blockquote>
                                        @endif
                                    </div>
                                    <div class="w-full shrink-0 space-y-2 lg:w-60">
                                        <form method="POST" action="{{ route('traspasos.aceptar', $traspaso) }}">
                                            @csrf
                                            <button type="submit" class="theme-button theme-button-primary w-full">Aceptar y recibir</button>
                                        </form>
                                        <form method="POST" action="{{ route('traspasos.rechazar', $traspaso) }}" class="space-y-2 rounded-xl bg-slate-50 p-2 dark:bg-slate-800/60">
                                            @csrf
                                            <input type="text" name="motivo_respuesta" maxlength="1000" placeholder="Motivo del rechazo (opcional)" class="theme-input w-full">
                                            <button type="submit" class="theme-button theme-button-secondary w-full">Rechazar</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-12 text-center dark:border-slate-600">
                                <p class="text-2xl">📥</p>
                                <p class="mt-2 font-bold">Todo al día</p>
                                <p class="mt-1 text-sm theme-subtle">Sin solicitudes pendientes para esta sucursal.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="module-card p-5 sm:p-6">
                    <h3 class="text-lg font-bold">Historial reciente</h3>
                    <p class="mt-1 text-sm theme-subtle">Últimas solicitudes aceptadas o rechazadas que involucran a esta sucursal.</p>
                    <div class="module-table mt-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="text-slate-600 dark:text-slate-300">
                                    <tr><th class="px-3 py-2">Folio</th><th class="px-3 py-2">Origen → Destino</th><th class="px-3 py-2">Producto</th><th class="px-3 py-2">Estado</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($historial as $t)
                                        <tr class="border-t border-slate-200 dark:border-slate-700">
                                            <td class="px-3 py-2 font-bold">T-{{ $t->id }}</td>
                                            <td class="px-3 py-2">{{ $t->sucursalOrigen?->nombre_sucursal }} → {{ $t->sucursalDestino?->nombre_sucursal }}</td>
                                            <td class="px-3 py-2">{{ $t->detalles->first()?->lote?->producto?->nombre_producto ?? '—' }} × {{ (int) $t->detalles->sum('cantidad') }}</td>
                                            <td class="px-3 py-2"><span class="status-badge {{ strtolower($t->estado) === 'aceptado' ? 'vigente' : 'expired' }}">{{ ucfirst($t->estado) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-3 py-8 text-center theme-subtle">Aún no hay historial.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif

            @if($seccion === 'caducidades')
                <div class="space-y-4">
                    <aside>
                        <div class="module-card overflow-hidden p-0">
                            <p class="border-b border-slate-200 px-4 py-2 text-xs font-bold uppercase tracking-widest theme-subtle dark:border-slate-700">Semáforo</p>
                            <ul class="grid grid-cols-3 divide-x divide-slate-200 dark:divide-slate-700">
                                @foreach([['rojo', 'Rojo', $totalesCaducidad['rojo'], 'bg-red-500', 'danger'], ['amarillo', 'Amarillo', $totalesCaducidad['amarillo'], 'bg-amber-400', 'warning'], ['verde', 'Verde', $totalesCaducidad['verde'], 'bg-emerald-500', 'positive']] as [$valor, $titulo, $conteo, $dot, $pill])
                                    @php($fondoActivo = match ($valor) {
                                        'rojo' => 'bg-red-100 dark:bg-red-500/20',
                                        'amarillo' => 'bg-amber-100 dark:bg-amber-500/20',
                                        default => 'bg-emerald-100 dark:bg-emerald-500/20',
                                    })
                                    <li>
                                        <a href="{{ route('alertas.index', ['sucursal' => $selectedSucursal?->id, 'seccion' => 'caducidades', 'nivel' => $valor]) }}"
                                           @class(['flex items-center justify-center gap-2 px-3 py-3 transition hover:bg-slate-100 dark:hover:bg-slate-800/60', $fondoActivo => $nivel === $valor])>
                                            <span class="inline-block h-3.5 w-3.5 shrink-0 rounded-full {{ $dot }}"></span>
                                            <span class="truncate text-sm font-bold">{{ $titulo }}</span>
                                            <span class="stat-pill {{ $pill }} shrink-0">{{ $conteo }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </aside>

                    <section class="module-card p-5 sm:p-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-baseline sm:justify-between">
                            <div>
                                <h2 class="text-xl font-bold">
                                    @switch($nivel)
                                        @case('rojo') Productos por caducar
                                        @break
                                        @case('amarillo') Productos con media vida
                                        @break
                                        @case('verde') Productos con buena caducidad
                                        @break
                                        @default Todos los lotes
                                    @endswitch
                                </h2>
                                <p class="mt-1 text-sm theme-subtle">{{ $selectedSucursal?->nombre_sucursal ?? '' }} · {{ $caducidades->count() }} en vista</p>
                            </div>
                            <a href="{{ route('alertas.index', ['sucursal' => $selectedSucursal?->id, 'seccion' => 'caducidades', 'nivel' => 'todos']) }}" class="text-sm font-bold text-emerald-700 hover:underline dark:text-emerald-300">Ver todo ({{ $totalesCaducidad['todos'] }})</a>
                        </div>
                        <div class="module-table mt-4">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="text-slate-600 dark:text-slate-300">
                                        <tr><th class="px-4 py-3">Producto</th><th class="px-4 py-3">Lote</th><th class="px-4 py-3">Stock</th><th class="px-4 py-3">Caducidad</th><th class="px-4 py-3">Estado</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($caducidades as $item)
                                            <tr class="border-t border-slate-200 dark:border-slate-700">
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2 font-medium">
                                                        <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full {{ $item['nivel'] === 'rojo' ? 'bg-red-500' : ($item['nivel'] === 'amarillo' ? 'bg-amber-400' : 'bg-emerald-500') }}"></span>
                                                        {{ $item['producto'] }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 theme-subtle">{{ $item['folio'] }}</td>
                                                <td class="px-4 py-3">{{ $item['stock'] }} uds.</td>
                                                <td class="px-4 py-3">{{ $item['fecha_caducidad'] }}</td>
                                                <td class="px-4 py-3"><span class="status-badge {{ $item['nivel'] === 'rojo' ? 'danger' : ($item['nivel'] === 'amarillo' ? 'warning' : 'vigente') }}">{{ $item['etiqueta'] }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="px-4 py-10 text-center theme-subtle">Sin productos en este nivel.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            @endif

            @if($seccion === 'ventas')
                <section class="module-card overflow-hidden p-0">
                    <div class="px-5 py-6 text-white sm:px-6" style="background: linear-gradient(135deg, #0e9384 0%, #0c7569 100%);">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-100">Hoy · {{ now()->format('Y-m-d') }} · {{ $selectedSucursal?->nombre_sucursal ?? '' }}</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl bg-white/15 px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-widest text-emerald-100">Ventas</p>
                                <p class="mt-1 text-3xl font-black"> {{ $resumenVentas['total_ventas'] }}</p>
                                <p class="mt-1 text-xs text-emerald-100">Tickets emitidos</p>
                            </div>
                            <div class="rounded-2xl bg-white/15 px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-widest text-emerald-100">Monto total</p>
                                <p class="mt-1 text-3xl font-black">${{ number_format($resumenVentas['monto_total'], 2) }}</p>
                                <p class="mt-1 text-xs text-emerald-100">Acumulado del día</p>
                            </div>
                            <div class="rounded-2xl bg-white/15 px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-widest text-emerald-100">Ticket promedio</p>
                                <p class="mt-1 text-3xl font-black">${{ number_format($resumenVentas['ticket_promedio'], 2) }}</p>
                                <p class="mt-1 text-xs text-emerald-100">Promedio por venta</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-4 p-5 sm:p-6 lg:grid-cols-2">
                        <div class="rounded-2xl border border-emerald-200/70 p-5 dark:border-emerald-500/30">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-600 dark:text-emerald-300">Desglose</p>
                            <h3 class="mt-1 text-base font-bold">Por método de pago</h3>
                            <div class="module-table mt-3">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="text-slate-600 dark:text-slate-300"><tr><th class="px-3 py-2">Método</th><th class="px-3 py-2">Ventas</th><th class="px-3 py-2">Monto</th></tr></thead>
                                    <tbody>
                                        @forelse($resumenVentas['por_metodo'] as $metodo => $datos)
                                            <tr class="border-t border-slate-200 dark:border-slate-700">
                                                <td class="px-3 py-2">
                                                    <span class="flex items-center gap-2 font-medium"><span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span>{{ $metodo }}</span>
                                                </td>
                                                <td class="px-3 py-2">{{ $datos['ventas'] }}</td>
                                                <td class="px-3 py-2 font-bold">${{ number_format($datos['monto'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="px-3 py-6 text-center theme-subtle">Sin ventas hoy.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-emerald-200/70 bg-emerald-50/60 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-600 dark:text-emerald-300">Cierre del día</p>
                            <h3 class="mt-1 text-base font-bold">Último movimiento</h3>
                            @if($resumenVentas['ultima_venta'])
                                <p class="mt-3 text-sm">Folio <span class="stat-pill positive ml-1">{{ $resumenVentas['ultima_venta']->folio }}</span></p>
                                <p class="mt-2 text-3xl font-black text-emerald-700 dark:text-emerald-300">${{ number_format($resumenVentas['ultima_venta']->total, 2) }}</p>
                                <p class="mt-1 text-sm theme-subtle">{{ $resumenVentas['ultima_venta']->creado_en?->format('H:i') }} · {{ $resumenVentas['ultima_venta']->pago?->metodo ?? 'Sin método' }}</p>
                                <p class="mt-4 rounded-xl bg-white/70 px-3 py-2 text-sm font-bold dark:bg-emerald-950/30">Hoy hubo {{ $resumenVentas['total_ventas'] }} ventas en {{ $selectedSucursal?->nombre_sucursal ?? 'la sucursal' }}.</p>
                            @else
                                <p class="mt-3 text-sm theme-subtle">Aún no hay ventas hoy. Aparecerán aquí al cobrar en punto de venta.</p>
                            @endif
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-layouts::app>
