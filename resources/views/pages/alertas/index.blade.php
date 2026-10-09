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
                    'stock' => "Stock ({$tipos['stock']})",
                    'caducidad' => "Caducidad ({$tipos['caducidad']})",
                    'traspasos' => "Traspasos ({$tipos['traspasos']})",
                    'ventas' => "Ventas ({$tipos['ventas']})",
                ] as $valor => $etiqueta)
                    <a href="{{ route('alertas.index', ['sucursal' => $selectedSucursal?->id, 'filtro' => $valor]) }}"
                       @class(['noti-filtro', 'activo' => $filtro === $valor])
                       @if($filtro === $valor) aria-current="page" @endif>
                        {{ $etiqueta }}
                    </a>
                @endforeach
            </div>

            @if($filtro === 'caducidad')
                <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filtrar por semáforo">
                    @foreach(['todos' => 'Todos', 'rojo' => '🔴 Rojo · ≤30 días', 'amarillo' => '🟡 Amarillo · 31-90', 'verde' => '🟢 Verde · >90'] as $valor => $etiqueta)
                        <a href="{{ route('alertas.index', ['sucursal' => $selectedSucursal?->id, 'filtro' => 'caducidad', 'nivel' => $valor]) }}"
                           @class(['noti-filtro', 'activo' => $nivel === $valor])>
                            {{ $etiqueta }}
                        </a>
                    @endforeach
                </div>
            @endif

            <section class="module-card overflow-hidden p-0" aria-label="Listado de notificaciones">
                @forelse($notificaciones as $aviso)
                    <article @class(['noti-fila', 'no-leida' => ! $aviso['leida']]) data-noti-fila="{{ $aviso['id'] }}">
                        <span class="noti-avatar prioridad-{{ $aviso['prioridad'] }}" aria-hidden="true">
                            {{ $aviso['avatar'] ?? '•' }}
                            <span class="noti-insignia">{{ $aviso['icono'] }}</span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <button type="button" data-noti-detalle="{{ $aviso['id'] }}" class="noti-titulo-boton" aria-label="Ver breve detalle de {{ $aviso['titulo'] }}">
                                <span class="noti-texto-fb">
                                    <strong>{{ $aviso['titulo'] }}</strong>
                                    <span class="theme-subtle">{{ $aviso['detalle'] }}</span>
                                </span>
                            </button>
                            @if(! empty($aviso['mensaje']))
                                <blockquote class="mt-2 rounded-xl border-l-4 border-amber-300 bg-amber-50 px-3 py-2 text-sm italic text-amber-900 dark:border-amber-500/50 dark:bg-amber-500/10 dark:text-amber-100">“{{ $aviso['mensaje'] }}”</blockquote>
                            @endif
                            <p class="noti-tiempo" title="{{ $aviso['fecha']?->format('Y-m-d H:i') ?? '' }}">{{ \App\Support\AlertasFeed::tiempoCorto($aviso['fecha']) }} · {{ $aviso['leida'] ? 'Leída' : 'No leída' }}</p>

                            <div class="noti-acciones">
                                <button type="button" data-noti-detalle="{{ $aviso['id'] }}" class="theme-button theme-button-secondary">Ver detalle</button>
                                <a href="{{ $aviso['destino_url'] ?? $aviso['url'] }}" data-noti-ir="{{ $aviso['id'] }}" class="theme-button theme-button-primary">{{ $aviso['destino_etiqueta'] ?? 'Ir al apartado' }}</a>
                            </div>

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
                                <span class="noti-punto" aria-label="No leída" data-noti-punto></span>
                            @endif
                            <details class="noti-menu">
                                <summary aria-label="Opciones">•••</summary>
                                <div class="noti-menu-lista">
                                    <button type="button" data-noti-detalle="{{ $aviso['id'] }}">Ver detalle</button>
                                    <a href="{{ $aviso['destino_url'] ?? $aviso['url'] }}" data-noti-ir="{{ $aviso['id'] }}">{{ $aviso['destino_etiqueta'] ?? 'Ir al apartado' }}</a>
                                    @if(! $aviso['leida'])
                                        <form method="POST" action="{{ route('alertas.leer') }}">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $aviso['id'] }}">
                                            <button type="submit">Marcar como leída</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('alertas.no-leida') }}">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $aviso['id'] }}">
                                            <button type="submit">Desmarcar (no leída)</button>
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

    <script>
        // Refleja en la fila el cambio de leída/no leída sin recargar la página.
        document.addEventListener('notificacion:actualizada', (evento) => {
            const { id, leida } = evento.detail || {};
            if (!id) {
                return;
            }
            const fila = document.querySelector('[data-noti-fila="' + CSS.escape(id) + '"]');
            if (!fila) {
                return;
            }
            fila.classList.toggle('no-leida', !leida);
            fila.querySelector('[data-noti-punto]')?.remove();
            const tiempo = fila.querySelector('.noti-tiempo');
            if (tiempo) {
                tiempo.textContent = tiempo.textContent.replace(leida ? 'No leída' : 'Leída', leida ? 'Leída' : 'No leída');
            }
        });
    </script>
</x-layouts::app>
