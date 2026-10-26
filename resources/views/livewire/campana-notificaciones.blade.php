<div
    wire:init="cargar"
    wire:poll.30s="cargar"
    x-data="{ abierto: @entangle('abierto') }"
    @click.outside="abierto = false; $wire.cerrar()"
    class="noti-campana"
>
    <button
        type="button"
        wire:click="toggle"
        class="noti-campana-btn"
        aria-label="Notificaciones"
        :aria-expanded="abierto.toString()"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        @if($noLeidas > 0)
            <span class="noti-campana-globo">{{ $noLeidas > 99 ? '99+' : $noLeidas }}</span>
        @endif
    </button>

    <div x-show="abierto" x-transition x-cloak class="noti-panel" role="dialog" aria-label="Notificaciones">
        <div class="noti-panel-head">
            <p class="noti-panel-titulo">Notificaciones</p>
            <div class="noti-panel-acciones">
                <button type="button" wire:click="marcarTodasLeidas" class="noti-link" @disabled($noLeidas === 0)>Marcar leídas</button>
                <a href="{{ route('alertas.index', ['sucursal' => session('active_sucursal_id') ?? auth()->user()?->id_sucursal]) }}" class="noti-link noti-link-ver" wire:navigate>Ver todas</a>
            </div>
        </div>

        <div class="noti-panel-lista">
            @forelse($avisos as $aviso)
                <button
                    type="button"
                    @click="$dispatch('abrir-notificacion', { id: '{{ $aviso['id'] }}' }); abierto = false; $wire.cerrar()"
                    class="noti-item noti-item-boton {{ ! $aviso['leida'] ? 'no-leida' : '' }}"
                >
                    <span class="noti-icono prioridad-{{ $aviso['prioridad'] }}">{{ $aviso['icono'] }}</span>
                    <span class="noti-texto">
                        <span class="noti-titulo">{{ $aviso['titulo'] }}</span>
                        <span class="noti-detalle">{{ $aviso['detalle'] }}</span>
                        <span class="noti-tiempo">{{ (! $aviso['leida'] ? '● ' : '') . $aviso['tiempo'] }}</span>
                    </span>
                    @if(! $aviso['leida'])
                        <span class="noti-punto" aria-hidden="true"></span>
                    @endif
                </button>
            @empty
                <p class="noti-vacio">Sin notificaciones. Todo al día.</p>
            @endforelse
        </div>

        @if(count($avisos) > 0)
            <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                <span class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400">{{ count($avisos) }} recientes · {{ $noLeidas }} sin leer</span>
                <a href="{{ route('alertas.index') }}" wire:navigate class="text-xs font-bold text-[#0e9384] hover:underline dark:text-[#5ec8b4]">Centro completo →</a>
            </div>
        @endif
    </div>

    <script>
        // Fallback: si no hay CentroNotificaciones montado (no estamos en /alertas),
        // el evento abrir-notificacion abre el modal clásico.
        document.addEventListener('abrir-notificacion', (e) => {
            const id = e.detail?.id;
            if (!id) return;
            const tieneCentro = document.querySelector('[x-on\\:abrir-notificacion\\.window]');
            if (!tieneCentro && typeof window.abrirDetalleNotificacion === 'function') {
                window.abrirDetalleNotificacion(id);
            }
            if (!tieneCentro && typeof window.abrirDetalleNotificacion !== 'function') {
                window.location.href = @json(route('alertas.index')) + '?abrir=' + encodeURIComponent(id);
            }
        });
    </script>
</div>
