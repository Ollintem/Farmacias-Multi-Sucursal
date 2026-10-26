<div class="module-page noti-page" wire:poll.30s x-data x-on:abrir-notificacion.window="$wire.abrirDetalle($event.detail.id)">
    <div class="module-page-inner noti-full noti-formal">
        {{-- Encabezado compacto — filtros movidos al topbar --}}
        <header class="flex flex-col gap-3" aria-label="Encabezado de notificaciones">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-zinc-400">Centro de notificaciones · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                <h1 class="mt-1 text-xl font-black tracking-tight text-slate-900 dark:text-white">Panel operativo de avisos</h1>
            </div>
        </header>



        <div wire:loading.delay class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            Actualizando…
        </div>

        {{-- Listado colapsable con memoria localStorage --}}
        <section class="module-card noti-listado overflow-hidden !p-0" aria-label="Listado de notificaciones" x-data="{ abierto: true, init() { try { const v = localStorage.getItem('noti-listado-abierto'); if (v !== null) this.abierto = v === '1'; } catch(e) {} }, alternar() { this.abierto = !this.abierto; try { localStorage.setItem('noti-listado-abierto', this.abierto ? '1' : '0'); } catch(e) {} } }">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-gradient-to-r from-teal-50/60 to-transparent px-4 py-3 dark:border-zinc-700 dark:from-zinc-800/50 sm:px-5">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-700 dark:text-zinc-200">Detalle de avisos</h2>
                    <p class="text-xs font-medium text-slate-500 dark:text-zinc-400">{{ count($notificaciones) }} registro(s) · filtro: <span class="font-bold text-slate-700 dark:text-zinc-200">{{ $filtro === 'no_leidas' ? 'no leídas' : $filtro }}</span> · {{ $selectedSucursal?->nombre_sucursal ?? 'sin sucursal' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="hidden sm:inline-flex rounded-full border border-dashed border-teal-600 px-2.5 py-1 text-[11px] font-bold text-teal-700 dark:border-teal-500 dark:text-teal-300">Operación farma</span>
                    <span class="text-xs text-slate-400">{{ count($notificaciones) }} de {{ $totalAvisos }}</span>
                    <button type="button" @click="alternar()" :aria-expanded="abierto.toString()" aria-controls="noti-listado-cuerpo" class="noti-desplegar">
                        <svg :class="{ 'girada': !abierto }" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/></svg>
                        <span x-text="abierto ? 'Contraer' : 'Desplegar'"></span>
                    </button>
                </div>
            </header>

            <div x-show="abierto" x-transition x-cloak id="noti-listado-cuerpo" class="noti-desplegable">
            <div class="noti-scroll max-h-[62vh] min-h-[18rem] overflow-y-auto overscroll-contain">
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
                            'alta' => 'Alta',
                            'baja' => 'Baja',
                            default => 'Media',
                        };
                    @endphp
                    <article class="group relative flex items-stretch gap-3 border-b border-slate-100 bg-white px-4 py-4 transition hover:bg-slate-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800/60 sm:px-5 {{ ! $aviso['leida'] ? '!bg-teal-50/40 dark:!bg-teal-900/10' : '' }}">
                        <span class="absolute inset-y-0 left-0 w-1 {{ $aviso['prioridad'] === 'alta' ? 'bg-red-500' : ($aviso['prioridad'] === 'media' ? 'bg-amber-500' : 'bg-teal-500') }}"></span>
                        <span class="noti-avatar prioridad-{{ $aviso['prioridad'] }} relative grid h-11 w-11 shrink-0 place-items-center rounded-2xl text-sm font-black text-white shadow-sm @if($aviso['prioridad'] === 'alta') bg-gradient-to-br from-red-500 to-red-700 @elseif($aviso['prioridad'] === 'media') bg-gradient-to-br from-amber-500 to-orange-600 @else bg-gradient-to-br from-teal-600 to-teal-700 @endif">
                            {{ $aviso['avatar'] ?? '•' }}
                            <span class="noti-insignia absolute -bottom-1 -right-1 grid h-5 w-5 place-items-center rounded-full border-2 border-white bg-white text-[10px] font-black text-teal-700 shadow dark:border-zinc-900">{{ $aviso['icono'] }}</span>
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="rounded-full bg-slate-900 px-2 py-0.5 text-[10px] font-black uppercase tracking-widest text-white dark:bg-white dark:text-slate-900">{{ $grupoEtiqueta }}</span>
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-black uppercase tracking-widest @if($aviso['prioridad']==='alta') border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 @elseif($aviso['prioridad']==='media') border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300 @else border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300 @endif">{{ $prioridadEtiqueta }}</span>
                                <span class="rounded-full border px-2 py-0.5 text-[10px] font-black uppercase tracking-widest @if(! $aviso['leida']) border-teal-600 bg-teal-600 text-white @else border-slate-200 bg-white text-slate-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 @endif">{{ $aviso['leida'] ? 'Leída' : 'No leída' }}</span>
                                <span class="text-xs font-medium text-slate-500 dark:text-zinc-400">{{ \App\Support\AlertasFeed::tiempoCorto($aviso['fecha']) }} · {{ $aviso['fecha']?->format('Y-m-d H:i') ?? 'Sin fecha' }}</span>
                            </div>

                            <button type="button" wire:click="abrirDetalle('{{ $aviso['id'] }}')" data-noti-detalle="{{ $aviso['id'] }}" class="mt-1.5 w-full text-left">
                                <span class="block text-sm font-bold leading-snug text-slate-900 group-hover:text-teal-700 dark:text-white dark:group-hover:text-teal-300">{{ $aviso['titulo'] }}</span>
                                <span class="mt-0.5 block text-sm leading-relaxed text-slate-600 dark:text-zinc-400">{{ $aviso['detalle'] }}</span>
                            </button>

                            @if(! empty($aviso['mensaje']))
                                <blockquote class="mt-2 rounded-r-xl border-l-[3px] border-amber-400 bg-amber-50 px-3 py-2 text-sm italic leading-relaxed text-slate-700 dark:border-amber-600 dark:bg-amber-900/15 dark:text-zinc-300">“{{ $aviso['mensaje'] }}”</blockquote>
                            @endif

                            <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-dashed border-slate-200 pt-3 dark:border-zinc-700">
                                <button type="button" wire:click="abrirDetalle('{{ $aviso['id'] }}')" data-noti-detalle="{{ $aviso['id'] }}" class="theme-button theme-button-secondary !min-h-0 !px-3 !py-1.5 text-xs">Ver detalle</button>
                                <a href="{{ $aviso['destino_url'] ?? '#' }}" data-noti-ir="{{ $aviso['id'] }}" wire:click.prevent="irAlApartado('{{ $aviso['id'] }}')" class="theme-button theme-button-primary !min-h-0 !px-3 !py-1.5 text-xs">{{ $aviso['destino_etiqueta'] ?? 'Ir al apartado' }}</a>
                                @if(! $aviso['leida'])
                                    <button type="button" wire:click="marcarLeida('{{ $aviso['id'] }}')" class="ml-auto text-xs font-bold text-slate-500 hover:text-teal-700 dark:text-zinc-400 dark:hover:text-teal-300">Marcar leída</button>
                                @else
                                    <button type="button" wire:click="marcarNoLeida('{{ $aviso['id'] }}')" class="ml-auto text-xs font-bold text-slate-500 hover:text-amber-700 dark:text-zinc-400 dark:hover:text-amber-300">Desmarcar</button>
                                @endif
                            </div>

                            @if($aviso['tipo'] === 'traspasos' && isset($aviso['traspaso_id']))
                                <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-zinc-400">Gestión del traspaso T-{{ $aviso['traspaso_id'] }}</p>
                                    <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <form method="POST" action="{{ route('traspasos.aceptar', $aviso['traspaso_id']) }}">
                                            @csrf
                                            <button type="submit" class="theme-button theme-button-primary !w-full sm:!w-auto">Aceptar traspaso</button>
                                        </form>
                                        <form method="POST" action="{{ route('traspasos.rechazar', $aviso['traspaso_id']) }}" class="flex flex-1 gap-2 sm:justify-end">
                                            @csrf
                                            <input type="text" name="motivo_respuesta" maxlength="1000" placeholder="Motivo del rechazo (opcional)" class="theme-input flex-1 text-sm" aria-label="Motivo del rechazo">
                                            <button type="submit" class="theme-button theme-button-secondary shrink-0">Rechazar</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex shrink-0 flex-col items-end justify-between">
                            @if(! $aviso['leida'])
                                <span class="h-2.5 w-2.5 rounded-full bg-teal-600 shadow shadow-teal-600/30"></span>
                            @else
                                <span class="h-2.5 w-2.5"></span>
                            @endif
                            <details class="noti-menu">
                                <summary aria-label="Opciones de la notificación">•••</summary>
                                <div class="noti-menu-lista hidden">
                                    <button type="button" data-noti-detalle="{{ $aviso['id'] }}" wire:click="abrirDetalle('{{ $aviso['id'] }}')">Ver detalle</button>
                                    <a href="{{ $aviso['destino_url'] ?? '#' }}" data-noti-ir="{{ $aviso['id'] }}">{{ $aviso['destino_etiqueta'] ?? 'Ir al apartado' }}</a>
                                </div>
                            </details>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-zinc-800 dark:text-zinc-400">{{ $aviso['prioridad'] }}</span>
                            <span class="hidden">alertas/leer</span>
                        </div>
                    </article>
                @empty
                    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                        <span class="grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-teal-50 text-teal-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-teal-300">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        </span>
                        <p class="mt-4 text-sm font-black text-slate-800 dark:text-white">Sin notificaciones en esta sección</p>
                        <p class="mt-1 max-w-sm text-sm leading-relaxed text-slate-500 dark:text-zinc-400">No hay avisos para el filtro seleccionado. Prueba con otro filtro, borra la búsqueda o cambia de sucursal.</p>
                        <button type="button" wire:click="setFiltro('todas')" wire:click="$set('busqueda','')" class="theme-button theme-button-secondary mt-4">Ver todas</button>
                    </div>
                @endforelse
            </div>
            </div>
        </section>

        {{-- Modal de detalle Livewire --}}
        @if($mostrandoDetalle)
            <div class="fixed inset-0 z-[80] flex items-center justify-center p-4" wire:keydown.escape="cerrarDetalle">
                <button type="button" wire:click="cerrarDetalle" class="absolute inset-0 bg-slate-900/55 backdrop-blur-sm" aria-label="Cerrar detalle"></button>
                <div class="relative flex max-h-[86vh] w-full max-w-xl flex-col overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                    @if(empty($detalle))
                        <div class="p-8 text-center">
                            <p class="text-sm font-semibold text-slate-600 dark:text-zinc-300">Cargando detalle…</p>
                            <button type="button" wire:click="cerrarDetalle" class="theme-button theme-button-secondary mt-4">Cerrar</button>
                        </div>
                    @else
                        @php
                            $detalleGrupo = $detalle['grupo'] ?? $detalle['tipo'] ?? 'general';
                            $grupoLabel = match($detalleGrupo) { 'stock'=>'Stock','caducidad'=>'Caducidad','traspasos'=>'Traspaso','ventas'=>'Ventas', default => ucfirst($detalleGrupo) };
                        @endphp
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-teal-50 to-white px-5 py-4 dark:border-zinc-800 dark:from-zinc-800 dark:to-zinc-900">
                            <span class="grid h-11 w-11 place-items-center rounded-2xl text-base font-black text-white shadow @if(($detalle['prioridad'] ?? 'media')==='alta') bg-gradient-to-br from-red-500 to-red-700 @elseif(($detalle['prioridad'] ?? 'media')==='media') bg-gradient-to-br from-amber-500 to-orange-600 @else bg-gradient-to-br from-teal-600 to-teal-700 @endif">{{ $detalle['avatar'] ?? '•' }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-black uppercase tracking-[0.14em] text-teal-700 dark:text-teal-300">{{ $grupoLabel }}</p>
                                <h2 class="truncate text-base font-black text-slate-900 dark:text-white">{{ $detalle['titulo'] }}</h2>
                            </div>
                            <span class="shrink-0 rounded-full border px-2.5 py-1 text-xs font-black @if(! ($detalle['leida'] ?? false)) border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300 @else border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 @endif">{{ ($detalle['leida'] ?? false) ? 'Leída' : 'No leída' }}</span>
                            <button type="button" wire:click="cerrarDetalle" class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400" aria-label="Cerrar">✕</button>
                        </div>

                        <div class="space-y-4 overflow-y-auto px-5 py-5">
                            <p class="text-sm leading-relaxed text-slate-700 dark:text-zinc-300">{{ $detalle['detalle'] }}</p>
                            @if(! empty($detalle['mensaje']))
                                <blockquote class="rounded-xl border-l-4 border-amber-400 bg-amber-50 px-4 py-3 text-sm italic leading-relaxed dark:bg-amber-900/15">“{{ $detalle['mensaje'] }}”</blockquote>
                            @endif
                            <dl class="grid grid-cols-3 gap-2">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                                    <dt class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-zinc-400">Estado</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ ($detalle['leida'] ?? false) ? 'Leída' : 'No leída' }}</dd>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                                    <dt class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-zinc-400">Recibida</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $detalle['tiempo'] ?? $detalle['tiempo_corto'] ?? 'reciente' }}</dd>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                                    <dt class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-zinc-400">Fecha</dt>
                                    <dd class="mt-1 text-sm font-bold text-slate-900 dark:text-white">{{ $detalle['fecha_texto'] ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="flex flex-wrap gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-800/50">
                            <a href="{{ $detalle['destino_url'] ?? '#' }}" wire:click.prevent="irAlApartado('{{ $detalle['id'] }}')" class="theme-button theme-button-primary flex-1 justify-center">{{ $detalle['destino_etiqueta'] ?? 'Ir al apartado' }}</a>
                            <button type="button" wire:click="toggleLeidaDetalle" class="theme-button theme-button-secondary flex-1 justify-center">{{ ($detalle['leida'] ?? false) ? 'Desmarcar (no leída)' : 'Marcar como leída' }}</button>
                            <button type="button" wire:click="cerrarDetalle" class="theme-button theme-button-secondary">Cerrar</button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notificacion-ir', (event) => {
                const url = event.url ?? event[0]?.url;
                if (url && url !== '#') window.location.href = url;
            });
        });
    </script>
</div>
