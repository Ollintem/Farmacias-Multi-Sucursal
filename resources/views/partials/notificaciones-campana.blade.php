{{-- Campana de notificaciones estilo Facebook: globo con conteo, panel con listado único y polling. --}}
@php
    $puedeVerNotis = auth()->check() && (auth()->user()?->puedeVerModulo('Alertas') ?? false);
    $sucursalNotiId = (int) (session('active_sucursal_id') ?? auth()->user()?->id_sucursal ?? 0);
    $noLeidasInicial = ($puedeVerNotis && $sucursalNotiId > 0) ? \App\Support\AlertasFeed::noLeidasCount($sucursalNotiId, auth()->id()) : 0;
@endphp

@if($puedeVerNotis && $sucursalNotiId > 0)
<div
    x-data="campanaNotificaciones({{ $noLeidasInicial }})"
    x-init="init()"
    @click.outside="abierto = false"
    class="noti-campana"
>
    <button
        type="button"
        @click="toggle()"
        class="noti-campana-btn"
        aria-label="Notificaciones"
        :aria-expanded="abierto"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        <span x-show="noLeidas > 0" x-text="noLeidas > 99 ? '99+' : noLeidas" class="noti-campana-globo" x-cloak></span>
    </button>

    <div x-show="abierto" x-transition x-cloak class="noti-panel" role="dialog" aria-label="Notificaciones">
        <div class="noti-panel-head">
            <p class="noti-panel-titulo">Notificaciones</p>
            <div class="noti-panel-acciones">
                <button type="button" @click="marcarLeidas()" class="noti-link" :disabled="noLeidas === 0">Marcar leídas</button>
                <a href="{{ route('alertas.index', ['sucursal' => $sucursalNotiId]) }}" class="noti-link noti-link-ver">Ver todas</a>
            </div>
        </div>

        <div class="noti-panel-lista">
            <template x-if="avisos.length === 0">
                <p class="noti-vacio">Sin notificaciones. Todo al día.</p>
            </template>

            <template x-for="aviso in avisos" :key="aviso.id">
                <button
                    type="button"
                    class="noti-item noti-item-boton"
                    :class="{ 'no-leida': !aviso.leida }"
                    :data-noti-detalle="aviso.id"
                    @click="abierto = false"
                >
                    <span class="noti-icono" :class="'prioridad-' + aviso.prioridad" x-text="aviso.icono"></span>
                    <span class="noti-texto">
                        <span class="noti-titulo" x-text="aviso.titulo"></span>
                        <span class="noti-detalle" x-text="aviso.detalle"></span>
                        <span class="noti-tiempo" x-text="(aviso.leida ? '' : '● ') + aviso.tiempo"></span>
                    </span>
                    <span x-show="!aviso.leida" class="noti-punto" aria-hidden="true"></span>
                </button>
            </template>
        </div>
    </div>
</div>

<script>
function campanaNotificaciones(inicial) {
    return {
        abierto: false,
        noLeidas: inicial || 0,
        avisos: [],
        timer: null,
        init() {
            this.cargar();
            this.timer = setInterval(() => this.cargar(), 30000);
            document.addEventListener('notificacion:actualizada', (evento) => {
                const { id, leida, no_leidas } = evento.detail || {};
                this.avisos = this.avisos.map((aviso) => (aviso.id === id ? { ...aviso, leida } : aviso));
                if (typeof no_leidas === 'number') {
                    this.noLeidas = no_leidas;
                }
            });
        },
        toggle() {
            this.abierto = !this.abierto;
            if (this.abierto) {
                this.cargar();
            }
        },
        async cargar() {
            try {
                const res = await fetch("{{ route('alertas.feed') }}", { headers: { 'Accept': 'application/json' } });
                if (!res.ok) {
                    return;
                }
                const data = await res.json();
                this.noLeidas = data.no_leidas || 0;
                this.avisos = data.avisos || [];
            } catch (e) {
                /* sin conexión: se conserva el conteo inicial */
            }
        },
        async marcarLeidas() {
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                await fetch("{{ route('alertas.leidas') }}", {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({}),
                });
            } catch (e) {
                /* aunque falle la red, limpiamos el globo local */
            }
            this.noLeidas = 0;
            this.avisos = this.avisos.map((aviso) => ({ ...aviso, leida: true }));
        },
    };
}
</script>
@endif
