<x-layouts::app :title="__('Notificaciones')">
    <div class="module-page">
        <div class="module-page-inner noti-feed">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-600 dark:text-emerald-300">Operación</p>
                    <h1 class="mt-2 text-3xl font-bold">Notificaciones</h1>
                    <p class="mt-1 text-sm theme-subtle">Todo lo pendiente de {{ $selectedSucursal?->nombre_sucursal ?? 'la sucursal' }} en un solo listado, como en Facebook.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" action="{{ route('alertas.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="filtro" value="{{ $filtro }}">
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

                    <form method="POST" action="{{ route('alertas.leidas') }}">
                        @csrf
                        <button type="submit" class="theme-button theme-button-secondary" {{ $totalNoLeidas === 0 ? 'disabled' : '' }}>
                            Marcar todas como leídas ({{ $totalNoLeidas }})
                        </button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filtrar notificaciones">
                @foreach([
                    'todas' => "Todas ({$totalAvisos})",
                    'no_leidas' => "No leídas ({$totalNoLeidas})",
                    'traspasos' => "Traspasos ({$tipos['traspasos']})",
                    'caducidades' => "Caducidades ({$tipos['caducidades']})",
                    'ventas' => "Ventas ({$tipos['ventas']})",
                ] as $valor => $etiqueta)
                    <a href="{{ route('alertas.index', ['sucursal' => $selectedSucursal?->id, 'filtro' => $valor]) }}"
                       @class(['noti-filtro', 'activo' => $filtro === $valor])
                       @if($filtro === $valor) aria-current="page" @endif>
                        {{ $etiqueta }}
                    </a>
                @endforeach
            </div>

            <section class="module-card overflow-hidden p-0" aria-label="Listado de notificaciones">
                @forelse($notificaciones as $aviso)
                    <article @class(['noti-fila', 'no-leida' => ! $aviso['leida']])>
                        <span class="noti-avatar prioridad-{{ $aviso['prioridad'] }}" aria-hidden="true">
                            {{ $aviso['avatar'] ?? '•' }}
                            <span class="noti-insignia">{{ $aviso['icono'] }}</span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="noti-texto-fb">
                                <strong>{{ $aviso['titulo'] }}</strong>
                                <span class="theme-subtle">{{ $aviso['detalle'] }}</span>
                            </p>
                            @if(! empty($aviso['mensaje']))
                                <blockquote class="mt-2 rounded-xl border-l-4 border-amber-300 bg-amber-50 px-3 py-2 text-sm italic text-amber-900 dark:border-amber-500/50 dark:bg-amber-500/10 dark:text-amber-100">“{{ $aviso['mensaje'] }}”</blockquote>
                            @endif
                            <p class="noti-tiempo" title="{{ $aviso['fecha']?->format('Y-m-d H:i') ?? '' }}">{{ \App\Support\AlertasFeed::tiempoCorto($aviso['fecha']) }}</p>

                            @if($aviso['tipo'] === 'traspasos' && isset($aviso['traspaso_id']))
                                <div class="noti-acciones">
                                    <form method="POST" action="{{ route('traspasos.aceptar', $aviso['traspaso_id']) }}">
                                        @csrf
                                        <button type="submit" class="theme-button theme-button-primary">Aceptar</button>
                                    </form>
                                    <form method="POST" action="{{ route('traspasos.rechazar', $aviso['traspaso_id']) }}" class="flex flex-col gap-2 sm:flex-row">
                                        @csrf
                                        <input type="text" name="motivo_respuesta" maxlength="1000" placeholder="Motivo (opcional)" class="theme-input sm:w-56">
                                        <button type="submit" class="theme-button theme-button-secondary">Rechazar</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="flex shrink-0 items-start gap-2">
                            @if(! $aviso['leida'])
                                <span class="noti-punto" aria-label="No leída"></span>
                            @endif
                            <details class="noti-menu">
                                <summary aria-label="Opciones">•••</summary>
                                <div class="noti-menu-lista">
                                    <a href="{{ $aviso['url'] }}">Ver detalle</a>
                                    @if(! $aviso['leida'])
                                        <form method="POST" action="{{ route('alertas.leer') }}">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $aviso['id'] }}">
                                            <button type="submit">Marcar como leída</button>
                                        </form>
                                    @endif
                                </div>
                            </details>
                        </div>
                    </article>
                @empty
                    <div class="px-4 py-12 text-center">
                        <p class="text-2xl">🔔</p>
                        <p class="mt-2 font-bold">Sin notificaciones aquí</p>
                        <p class="mt-1 text-sm theme-subtle">Cambia el filtro o revisa otra sucursal.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-layouts::app>
