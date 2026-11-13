<script>
    function permisosNuevoUsuario() {
        return {
            rol: @json((string) old('id_rol', '')),
            mapa: @json($permisosPorRol),
            nombresRol: @json($roles->pluck('tipo_rol','id')),
            oldPermisos: @json(old('permisos', [])),
            estado: {},
            abierto: false,
            init() {
                @foreach($modulos as $modulo)
                    this.estado['{{ $modulo->id }}'] = {ver:false, crear:false, editar:false, borrar:false};
                @endforeach
                this.aplicarRol();
                if (this.oldPermisos && Object.keys(this.oldPermisos).length) {
                    for (const [mid, flags] of Object.entries(this.oldPermisos)) {
                        if (!this.estado[mid]) this.estado[mid] = {ver:false, crear:false, editar:false, borrar:false};
                        for (const k of ['ver','crear','editar','borrar']) {
                            this.estado[mid][k] = !!flags[k];
                        }
                    }
                }
            },
            sincronizarRol(evento) {
                if (evento.detail.name !== 'id_rol') {
                    return;
                }

                this.rol = String(evento.detail.value ?? '');
                this.aplicarRol();
                this.abierto = this.rol !== '';
            },
            aplicarRol() {
                if (!this.rol || !this.mapa[this.rol]) {
                    for (const mid of Object.keys(this.estado)) {
                        this.estado[mid] = {ver:false, crear:false, editar:false, borrar:false};
                    }
                    return;
                }
                const porRol = this.mapa[this.rol] || {};
                for (const mid of Object.keys(this.estado)) {
                    const flags = porRol[mid] || {ver:false, crear:false, editar:false, borrar:false};
                    this.estado[mid] = {ver: !!flags.ver, crear: !!flags.crear, editar: !!flags.editar, borrar: !!flags.borrar};
                }
            },
            tiene(mid, acc) {
                return !!(this.estado[mid] && this.estado[mid][acc]);
            },
            nombreRol() {
                return this.nombresRol[this.rol] || 'Rol';
            },
            resumenPermisos() {
                let total = 0;
                for (const flags of Object.values(this.estado)) {
                    for (const v of Object.values(flags)) if (v) total++;
                }
                return total ? total + ' permisos activos' : 'Sin permisos';
            },
            todosActivos() {
                const ids = Object.keys(this.estado);

                return ids.length > 0 && ids.every((mid) => {
                    const flags = this.estado[mid];

                    return flags.ver && flags.crear && flags.editar && flags.borrar;
                });
            },
            toggleTodos() {
                const act = !this.todosActivos();
                for (const mid of Object.keys(this.estado)) {
                    this.estado[mid] = {ver: act, crear: act, editar: act, borrar: act};
                }
            }
        }
    }
    if (window.Alpine) {
        window.Alpine.data('permisosNuevoUsuario', permisosNuevoUsuario);
    }
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('permisosNuevoUsuario', permisosNuevoUsuario);
    });
</script>
