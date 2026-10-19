<x-layouts::app :title="__('Notificaciones')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-feed noti-formal">
            {{-- Encabezado empresarial --}}
            <section class="module-card noti-hero" aria-label="Encabezado de notificaciones">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Centro de notificaciones · {{ $selectedSucursal?->nombre_sucursal ?? 'la sucursal' }}</p>
                    <h1 class="mt-2">Panel operativo de avisos</h1>
                    <dl class="noti-resumen noti-kpis">
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg> Total</dt><dd>{{ $totalAvisos }}</dd>
                        </div>
                        <div @class(['noti-kpi', 'noti-kpi-alerta' => $totalNoLeidas > 0])>
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg> Requieren atención</dt><dd>{{ $totalNoLeidas }}</dd>
                        </div>
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg> Traspasos</dt><dd>{{ $tipos['traspasos'] ?? 0 }}</dd>
                        </div>
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Caducidad</dt><dd>{{ $tipos['caducidad'] ?? 0 }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            @if(session('success'))
                <div class="noti-alerta noti-alerta-exito" role="status">
                    <span class="noti-alerta-icono" aria-hidden="true">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="noti-alerta noti-alerta-error" role="alert">
                    <span class="noti-alerta-icono" aria-hidden="true">!</span>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filtros empresariales por grupo --}}
            <nav class="noti-tabs" role="tablist" aria-label="Filtrar notificaciones por grupo">
                @foreach([
                    'todas' => ['Todas', $totalAvisos, 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
                    'no_leidas' => ['No leídas', $totalNoLeidas, 'M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
                    'stock' => ['Stock', $tipos['stock'], 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
                    'caducidad' => ['Caducidad', $tipos['caducidad'], 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'traspasos' => ['Traspasos', $tipos['traspasos'], 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
                    'ventas' => ['Ventas', $tipos['ventas'], 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .298-.119.585-.33.795a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0Z'],
                ] as $valor => [$etiqueta, $conteo, $icono])
                    <a href="{{ route('alertas.index', ['sucursal' => $selectedSucursal?->id, 'filtro' => $valor]) }}"
                       @class(['noti-filtro', 'activo' => $filtro === $valor])
                       @if($filtro === $valor) aria-current="page" @endif>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icono }}"/></svg>
                        <span>{{ $etiqueta }}</span>
                        <span class="noti-filtro-conteo">{{ $conteo }}</span>
                    </a>
                @endforeach
            </nav>

            @if($filtro === 'caducidad')
                <nav class="noti-tabs noti-tabs-nivel" role="tablist" aria-label="Filtrar por nivel de caducidad">
                    @foreach(['todos' => 'Todos los niveles', 'rojo' => 'Nivel rojo · 30 días o menos', 'amarillo' => 'Nivel amarillo · 31 a 90 días', 'verde' => 'Nivel verde · más de 90 días'] as $valor => $etiqueta)
                        <a href="{{ route('alertas.index', ['sucursal' => $selectedSucursal?->id, 'filtro' => 'caducidad', 'nivel' => $valor]) }}"
                           @class(['noti-filtro', 'activo' => $nivel === $valor])>
                            {{ $etiqueta }}
                        </a>
                    @endforeach
                </nav>
            @endif

            {{-- Listado empresarial en cuadro desplegable con scroll. El estado
                abierto/contraído se guarda en localStorage para que se mantenga
                al cambiar de vista y regresar, hasta volver a presionarlo. --}}
            <section class="module-card noti-listado" aria-label="Listado de notificaciones"
                x-data="{ abierto: true,
                    init() { try { const g = localStorage.getItem('noti-listado-abierto'); if (g !== null) { this.abierto = g === '1'; } } catch (e) {} },
                    alternar() { this.abierto = ! this.abierto; try { localStorage.setItem('noti-listado-abierto', this.abierto ? '1' : '0'); } catch (e) {} } }">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Detalle de avisos</h2>
                        <p class="theme-subtle text-sm">{{ count($notificaciones) }} registro(s) · filtro activo: {{ $filtro === 'no_leidas' ? 'no leídas' : $filtro }} · {{ $selectedSucursal?->nombre_sucursal ?? 'sin sucursal' }}</p>
                    </div>
                    <div class="noti-cabecera-lado">
                        <span class="noti-sla">SLA sugerido: alta hoy · media 48 h · baja esta semana</span>
                        <button type="button" @click="alternar()" :aria-expanded="abierto" aria-controls="noti-listado-cuerpo" class="noti-desplegar" :aria-label="abierto ? 'Contraer notificaciones' : 'Desplegar notificaciones'">
                            <svg :class="{ 'girada': !abierto }" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/></svg>
                            <span x-text="abierto ? 'Contraer' : 'Desplegar'"></span>
                        </button>
                    </div>
                </header>
                <div x-show="abierto" x-transition x-cloak class="noti-desplegable" id="noti-listado-cuerpo">
                    <div class="noti-scroll" tabindex="0" role="region" aria-label="Notificaciones con desplazamiento">
                @forelse($notificaciones as $aviso)
                    @php
                        $grupo = $aviso['grupo'] ?? $aviso['tipo'] ?? 'general';
                        $grupoEtiqueta = match ($grupo) {
                            'stock' => 'Stock',
                            'caducidad' => 'Caducidad',
                            'traspasos' => 'Traspaso',
                            'ventas' => 'Ventas',
                            default => ucfirst((string) $grupo),
                        };
                        $prioridadEtiqueta = match ($aviso['prioridad'] ?? 'media') {
                            'alta' => 'Prioridad alta',
                            'baja' => 'Prioridad baja',
                            default => 'Prioridad media',
                        };
                    @endphp
                    <article @class(['noti-fila', 'noti-fila-formal', 'no-leida' => ! $aviso['leida']]) data-noti-fila="{{ $aviso['id'] }}">
                        <span class="noti-barra-prioridad prioridad-{{ $aviso['prioridad'] }}" aria-hidden="true"></span>
                        <span class="noti-avatar prioridad-{{ $aviso['prioridad'] }}" aria-hidden="true">
                            {{ $aviso['avatar'] ?? '•' }}
                            <span class="noti-insignia">{{ $aviso['icono'] }}</span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="noti-meta">
                                <span class="noti-etiqueta noti-etiqueta-grupo">{{ $grupoEtiqueta }}</span>
                                <span @class(['noti-etiqueta', 'noti-etiqueta-prioridad', 'prioridad-'.$aviso['prioridad']])>{{ $prioridadEtiqueta }}</span>
                                <span @class(['noti-etiqueta', 'noti-etiqueta-estado', 'leida' => $aviso['leida'], 'no-leida' => ! $aviso['leida']])>{{ $aviso['leida'] ? 'Leída' : 'No leída' }}</span>
                                <span class="noti-tiempo" title="{{ $aviso['fecha']?->format('Y-m-d H:i') ?? '' }}">{{ \App\Support\AlertasFeed::tiempoCorto($aviso['fecha']) }} · {{ $aviso['fecha']?->format('Y-m-d H:i') ?? 'Sin fecha' }}</span>
                            </div>
                            <button type="button" data-noti-detalle="{{ $aviso['id'] }}" class="noti-titulo-boton" aria-label="Ver breve detalle de {{ $aviso['titulo'] }}">
                                <span class="noti-texto-fb">
                                    <strong>{{ $aviso['titulo'] }}</strong>
                                    <span class="theme-subtle">{{ $aviso['detalle'] }}</span>
                                </span>
                            </button>
                            @if(! empty($aviso['mensaje']))
                                <blockquote class="noti-mensaje">“{{ $aviso['mensaje'] }}”</blockquote>
                            @endif

                            <div class="noti-acciones noti-acciones-formal">
                                <button type="button" data-noti-detalle="{{ $aviso['id'] }}" class="theme-button theme-button-secondary">Ver detalle</button>
                                <a href="{{ $aviso['destino_url'] ?? $aviso['url'] }}" data-noti-ir="{{ $aviso['id'] }}" class="theme-button theme-button-primary">{{ $aviso['destino_etiqueta'] ?? 'Ir al apartado' }}</a>
                            </div>

                            @if($aviso['tipo'] === 'traspasos' && isset($aviso['traspaso_id']))
                                <div class="noti-traspaso-panel">
                                    <p class="noti-traspaso-titulo">Gestión del traspaso T-{{ $aviso['traspaso_id'] }}</p>
                                    <div class="noti-traspaso-acciones">
                                        <form method="POST" action="{{ route('traspasos.aceptar', $aviso['traspaso_id']) }}">
                                            @csrf
                                            <button type="submit" class="theme-button theme-button-primary">Aceptar traspaso</button>
                                        </form>
                                        <form method="POST" action="{{ route('traspasos.rechazar', $aviso['traspaso_id']) }}" class="noti-rechazo-form">
                                            @csrf
                                            <input type="text" name="motivo_respuesta" maxlength="1000" placeholder="Motivo del rechazo (opcional)" class="theme-input" aria-label="Motivo del rechazo">
                                            <button type="submit" class="theme-button theme-button-secondary">Rechazar</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="noti-lateral">
                            @if(! $aviso['leida'])
                                <span class="noti-punto" aria-label="No leída" data-noti-punto></span>
                            @endif
                            <details class="noti-menu">
                                <summary aria-label="Opciones de la notificación">•••</summary>
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
                    <div class="noti-vacio">
                        <span class="noti-vacio-icono" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        </span>
                        <p class="noti-vacio-titulo">Sin notificaciones en esta sección</p>
                        <p class="theme-subtle text-sm">No existen avisos para el filtro seleccionado. Cambie de filtro o seleccione otra sucursal.</p>
                    </div>
                @endforelse
                    </div>
                </div>
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
            const estado = fila.querySelector('.noti-etiqueta-estado');
            if (estado) {
                estado.textContent = leida ? 'Leída' : 'No leída';
                estado.classList.toggle('leida', leida);
                estado.classList.toggle('no-leida', !leida);
            }
        });
    </script>
</x-layouts::app>
