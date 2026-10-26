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
                    class="rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-600 dark:text-red-400"
                >
                    {{ session('error') }}
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

                        <div x-data="permisosNuevoUsuario()" x-init="init()">
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Nombre</label>
                                    <input name="nombre" value="{{ old('nombre') }}" class="theme-input" required>
                                    @error('nombre')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Apellido</label>
                                    <input name="apellido" value="{{ old('apellido') }}" class="theme-input" required>
                                    @error('apellido')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="theme-input" required>
                                    @error('email')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Rol</label>
                                    <select name="id_rol" x-model="rol" @change="aplicarRol(); if(rol) abierto = true" class="theme-input" required>
                                        <option value="">Selecciona un rol</option>
                                        @foreach($roles as $rolOption)
                                            <option value="{{ $rolOption->id }}" @selected(old('id_rol') == $rolOption->id)>{{ $rolOption->tipo_rol }}</option>
                                        @endforeach
                                    </select>
                                    @error('id_rol')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                    <div x-show="rol !== ''" x-cloak class="mt-2 flex items-center gap-2">
                                        <span class="text-xs text-slate-500 dark:text-zinc-400" x-text="resumenPermisos()"></span>
                                        <button type="button" @click="abierto = true" class="text-xs font-semibold text-[#0c9f9c] underline hover:text-[#0a8582]">Ver / editar permisos</button>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Sucursal</label>
                                    <x-option-pick name="id_sucursal" label="Sucursales" placeholder="Selecciona una sucursal" :options="$sucursales->pluck('nombre_sucursal', 'id')" :value="$selectedSucursalId ?? ''" />
                                    @error('id_sucursal')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Contrasena</label>
                                    <input type="password" name="password" class="theme-input" minlength="8" required>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Usa al menos 8 caracteres.</p>
                                    @error('password')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Confirmar contrasena</label>
                                    <input type="password" name="password_confirmation" class="theme-input" required>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Escribela exactamente igual.</p>
                                    @error('password_confirmation')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Ventana flotante de permisos --}}
                            <div x-show="abierto" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;" @keydown.escape.window="abierto = false">
                                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="abierto = false"></div>
                                <div class="relative flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900" @click.stop>
                                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-zinc-800">
                                        <div>
                                            <h3 class="text-sm font-bold text-slate-800 dark:text-white">Permisos para <span x-text="nombreRol()"></span></h3>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Se guardan en <code>permisos_activados</code> y puedes cambiarlos después en “Permisos”.</p>
                                        </div>
                                        <button type="button" @click="abierto = false" class="rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-zinc-800">
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
                                                                    <input type="checkbox" name="permisos[{{ $modulo->id }}][{{ $accion }}]" value="1" :checked="tiene('{{ $modulo->id }}','{{ $accion }}')" @change="estado['{{ $modulo->id }}']['{{ $accion }}'] = $el.checked" class="h-4 w-4 rounded border-slate-300 text-[#0c9f9c] focus:ring-[#0c9f9c]">
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

                        <div class="mt-6 flex items-center">
                            <input type="hidden" name="es_activo" value="0">
                            <input type="checkbox" name="es_activo" value="1" {{ old('es_activo', true) ? 'checked' : '' }}>
                            <label class="ml-2 text-sm">Usuario activo</label>
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
                abierto: @json((string) old('id_rol', '') !== ''),
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
                        this.abierto = true;
                    }
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
                    return Object.values(this.estado).every(f => f.ver && f.crear && f.editar && f.borrar);
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
