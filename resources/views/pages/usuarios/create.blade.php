<x-layouts::app :title="__('Nuevo usuario')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado limpio --}}
            <header class="mb-6" aria-label="Encabezado de usuario">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Alta de personal · Nuevo registro</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Registrar nuevo usuario</h1>
                </div>
            </header>

            @if(session('error'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mb-4 rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-600 dark:text-red-400"
                >
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300" role="alert">
                    <p class="font-semibold">Revisa la información capturada:</p>
                    <ul class="mt-1 list-disc space-y-1 pl-5 text-xs">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="module-card noti-listado" aria-label="Datos del usuario">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Datos del usuario</h2>
                        <p class="theme-subtle text-sm">Completa la información para registrar un nuevo usuario</p>
                    </div>
                </header>
                <div class="p-5">
                    <form action="{{ route('usuarios.store') }}" method="POST">
                        @csrf

                        <div class="module-table">
                            <div class="p-5">
                                <div
                                    x-data="permisosNuevoUsuario()"
                                    x-init="init()"
                                    @option-pick-changed="sincronizarRol($event)"
                                >
                                    <div class="grid gap-5 md:grid-cols-2">
                                        <div>
                                            <label for="nombre" class="mb-2 block text-sm font-medium">Nombre</label>
                                            <input id="nombre" name="nombre" type="text" value="{{ old('nombre') }}" class="theme-input" maxlength="100" autocomplete="given-name" required>
                                            @error('nombre')
                                                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="apellido" class="mb-2 block text-sm font-medium">Apellido</label>
                                            <input id="apellido" name="apellido" type="text" value="{{ old('apellido') }}" class="theme-input" maxlength="100" autocomplete="family-name" required>
                                            @error('apellido')
                                                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="email" class="mb-2 block text-sm font-medium">Correo electrónico</label>
                                            <input id="email" type="email" name="email" value="{{ old('email') }}" class="theme-input" maxlength="255" autocomplete="email" required>
                                            @error('email')
                                                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="id_rol" class="mb-2 block text-sm font-medium">Rol</label>
                                            <x-option-pick
                                                id="id_rol"
                                                name="id_rol"
                                                label="Roles"
                                                placeholder="Selecciona un rol"
                                                :options="$roles->pluck('tipo_rol', 'id')"
                                                :value="old('id_rol', '')"
                                            />
                                            @error('id_rol')
                                                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                            @enderror
                                            <div x-show="rol !== ''" x-cloak class="mt-2 flex flex-wrap items-center gap-2">
                                                <span class="text-xs text-slate-500 dark:text-zinc-400" x-text="resumenPermisos()"></span>
                                                <button type="button" @click="abierto = true" class="text-xs font-semibold text-[#0c9f9c] underline hover:text-[#0a8582]">Ver / editar permisos</button>
                                            </div>
                                        </div>

                                        <div>
                                            <label for="id_sucursal" class="mb-2 block text-sm font-medium">Sucursal</label>
                                            <x-option-pick
                                                id="id_sucursal"
                                                name="id_sucursal"
                                                label="Sucursales"
                                                placeholder="Selecciona una sucursal"
                                                :options="$sucursales->pluck('nombre_sucursal', 'id')"
                                                :value="$selectedSucursalId ?? ''"
                                            />
                                            @error('id_sucursal')
                                                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="password" class="mb-2 block text-sm font-medium">Contraseña</label>
                                            <input id="password" type="password" name="password" class="theme-input" minlength="8" autocomplete="new-password" required>
                                            <p id="password-help" class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Usa al menos 8 caracteres.</p>
                                            @error('password')
                                                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="password_confirmation" class="mb-2 block text-sm font-medium">Confirmar contraseña</label>
                                            <input id="password_confirmation" type="password" name="password_confirmation" class="theme-input" minlength="8" autocomplete="new-password" required>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Escríbela exactamente igual.</p>
                                            @error('password_confirmation')
                                                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Ventana flotante de permisos --}}
                                    <div
                                        x-show="abierto"
                                        x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                        style="display:none;"
                                        role="dialog"
                                        aria-modal="true"
                                        aria-labelledby="permisos-modal-titulo"
                                        @keydown.escape.window="abierto = false"
                                    >
                                        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="abierto = false"></div>
                                        <div class="relative flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900" @click.stop>
                                            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-zinc-800">
                                                <div>
                                                    <h3 id="permisos-modal-titulo" class="text-sm font-bold text-slate-800 dark:text-white">Permisos para <span x-text="nombreRol()"></span></h3>
                                                    <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Se guardan en <code>permisos_activados</code> y puedes cambiarlos después en “Permisos”.</p>
                                                </div>
                                                <button type="button" @click="abierto = false" class="rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-zinc-800" aria-label="Cerrar ventana de permisos">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <div class="flex items-center justify-between gap-2 bg-slate-50 px-5 py-2.5 dark:bg-zinc-800/50">
                                                <span class="text-xs font-medium text-slate-600 dark:text-zinc-300" x-text="resumenPermisos()"></span>
                                                <button type="button" @click="toggleTodos()" class="text-xs font-semibold text-[#0c9f9c] hover:text-[#0a8582]" x-text="todosActivos() ? 'Desactivar todo' : 'Activar todo'"></button>
                                            </div>
                                            <div class="overflow-auto p-5">
                                                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-zinc-700">
                                                    <table class="w-full text-sm">
                                                        <thead class="bg-slate-50 dark:bg-zinc-800">
                                                            <tr class="text-left text-xs font-semibold text-slate-600 dark:text-zinc-300">
                                                                <th class="px-3 py-2.5">Módulo</th>
                                                                <th class="w-[72px] px-2 py-2.5 text-center">Ver</th>
                                                                <th class="w-[72px] px-2 py-2.5 text-center">Crear</th>
                                                                <th class="w-[72px] px-2 py-2.5 text-center">Editar</th>
                                                                <th class="w-[72px] px-2 py-2.5 text-center">Borrar</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-700">
                                                            @foreach($modulos as $modulo)
                                                                <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40">
                                                                    <td class="px-3 py-2.5 font-medium text-slate-700 dark:text-zinc-200">{{ $modulo->nombre_modulo }}</td>
                                                                    @foreach(['ver','crear','editar','borrar'] as $accion)
                                                                        <td class="px-2 py-2.5 text-center">
                                                                            <input
                                                                                type="checkbox"
                                                                                name="permisos[{{ $modulo->id }}][{{ $accion }}]"
                                                                                value="1"
                                                                                aria-label="{{ $modulo->nombre_modulo }}: {{ $accion }}"
                                                                                :checked="tiene('{{ $modulo->id }}','{{ $accion }}')"
                                                                                @change="estado['{{ $modulo->id }}']['{{ $accion }}'] = $el.checked"
                                                                                class="h-4 w-4 rounded border-slate-300 text-[#0c9f9c] focus:ring-[#0c9f9c]"
                                                                            >
                                                                        </td>
                                                                    @endforeach
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                                                <button type="button" @click="abierto = false" class="theme-button theme-button-secondary !py-2 !text-sm">Listo</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 flex items-center gap-2">
                                    <input type="hidden" name="es_activo" value="0">
                                    <input id="es_activo" type="checkbox" name="es_activo" value="1" @checked(old('es_activo', true)) class="h-4 w-4 rounded border-slate-300 text-[#0c9f9c] focus:ring-[#0c9f9c]">
                                    <label for="es_activo" class="text-sm">Usuario activo</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                            <button type="submit" class="theme-button theme-button-primary">Guardar usuario</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

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
</x-layouts::app>
