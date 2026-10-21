<x-layouts::app :title="__('Usuarios')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado homologado: hero verde + KPIs blancos --}}
            <section class="module-card noti-hero" aria-label="Encabezado de usuarios">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Personal · Gestión de accesos</p>
                    <h1 class="mt-2">Usuarios</h1>
                    <dl class="noti-resumen noti-kpis">
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg> Usuarios</dt><dd>{{ $totalUsuarios }}</dd>
                        </div>
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> Roles</dt><dd>{{ $roles->count() }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="button" x-on:click="$dispatch('open-modal-rol')" class="theme-button theme-button-secondary">Nuevo rol</button>
                        <a href="{{ route('roles.permisos') }}" class="theme-button theme-button-secondary">Permisos por rol</a>
                        <a href="{{ route('usuarios.create') }}" class="theme-button theme-button-primary">Nuevo usuario</a>
                    </div>
                </div>
            </section>

            @if(session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-400"
                >
                    {{ session('success') }}
                </div>
            @endif

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

            <div x-data="{
                rolSeleccionado: null,
                total: {{ $totalUsuarios }},
                modalRol: false,
                modalEditarRol: false,
                editarRolId: null,
                editarRolNombre: '',
                editarRolDescripcion: '',
                filtrar(id) {
                    this.rolSeleccionado = this.rolSeleccionado === id ? null : id;
                },
                abrirEditarRol(id, nombre, descripcion) {
                    this.editarRolId = id;
                    this.editarRolNombre = nombre;
                    this.editarRolDescripcion = descripcion;
                    this.modalEditarRol = true;
                }
            }" @open-modal-rol.window="modalRol = true" @keydown.escape.window="modalRol = false; modalEditarRol = false">

                {{-- Modal Nuevo Rol --}}
                <div
                    x-show="modalRol"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="display:none;"
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="modalRol = false"></div>

                    <div
                        class="relative w-full max-w-lg rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-2xl backdrop-blur-sm dark:border-[#1e3a5f]/60 dark:bg-[#0b1322]/95"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        x-on:click.stop
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-lg font-bold">Nuevo rol</h2>
                            <button type="button" x-on:click="modalRol = false" class="opacity-50 hover:opacity-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form action="{{ route('roles.store') }}" method="POST">
                            @csrf

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Nombre del rol</label>
                                    <input
                                        name="tipo_rol"
                                        value="{{ old('tipo_rol') }}"
                                        class="theme-input"
                                        maxlength="50"
                                        required
                                    >
                                    @error('tipo_rol')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Descripcion <span class="opacity-50 font-normal">(opcional)</span></label>
                                    <input
                                        name="descripcion"
                                        value="{{ old('descripcion') }}"
                                        class="theme-input"
                                        maxlength="255"
                                    >
                                    @error('descripcion')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" x-on:click="modalRol = false" class="theme-button theme-button-secondary">Cancelar</button>
                                <button type="submit" class="theme-button theme-button-primary">Guardar rol</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Modal Editar Rol --}}
                <div
                    x-show="modalEditarRol"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="display:none;"
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="modalEditarRol = false"></div>

                    <div
                        class="relative w-full max-w-lg rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-2xl backdrop-blur-sm dark:border-[#1e3a5f]/60 dark:bg-[#0b1322]/95"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        x-on:click.stop
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-lg font-bold">Editar rol</h2>
                            <button type="button" x-on:click="modalEditarRol = false" class="opacity-50 hover:opacity-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form :action="'{{ url('/roles') }}/' + editarRolId" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Nombre del rol</label>
                                    <input
                                        name="tipo_rol"
                                        x-model="editarRolNombre"
                                        class="theme-input"
                                        maxlength="50"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Descripcion <span class="opacity-50 font-normal">(opcional)</span></label>
                                    <input
                                        name="descripcion"
                                        x-model="editarRolDescripcion"
                                        class="theme-input"
                                        maxlength="255"
                                    >
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" x-on:click="modalEditarRol = false" class="theme-button theme-button-secondary">Cancelar</button>
                                <button type="submit" class="theme-button theme-button-primary">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="roles-cards-scroll">
                    <div
                        @click="filtrar(null)"
                        role="button"
                        tabindex="0"
                        @keydown.enter="filtrar(null)"
                        class="role-card"
                        :class="rolSeleccionado === null ? 'active' : ''"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <span class="role-card-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <span class="role-card-title">Todos</span>
                        </div>
                        <div class="role-card-count" x-text="total"></div>
                        <div class="role-card-sub">usuarios</div>
                    </div>

                    @foreach($roles as $rol)
                        <div
                            @click="filtrar({{ $rol->id }})"
                            role="button"
                            tabindex="0"
                            @keydown.enter="filtrar({{ $rol->id }})"
                            class="role-card"
                            :class="rolSeleccionado === {{ $rol->id }} ? 'active' : ''"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <span class="role-card-icon role-card-icon-teal">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </span>
                                <span class="role-card-title">{{ $rol->tipo_rol }}</span>
                            </div>
                            <div class="role-card-count">{{ $rol->usuarios_count }}</div>
                            <div class="role-card-sub">usuarios</div>

                            @if($rol->tipo_rol !== 'SuperAdmin')
                            <div class="role-card-actions">
                                <button
                                    type="button"
                                    title="Editar rol"
                                    x-on:click.stop="abrirEditarRol({{ $rol->id }}, '{{ $rol->tipo_rol }}', '{{ $rol->descripcion ?? '' }}')"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form
                                    action="{{ route('roles.destroy', $rol) }}"
                                    method="POST"
                                    onsubmit="return confirm('¿Eliminar el rol {{ $rol->tipo_rol }}? Se perderan los usuarios asignados.');"
                                    style="margin:0; padding:0;"
                                    x-on:click.stop
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        title="Eliminar rol"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <section class="module-card noti-listado" aria-label="Listado de usuarios">
                    <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                        <div>
                            <h2>Listado de usuarios</h2>
                            <p class="theme-subtle text-sm">{{ $totalUsuarios }} usuario(s) · filtre por rol con las tarjetas</p>
                        </div>
                    </header>
                    <div>
                    <div class="module-table !rounded-none !border-0 !shadow-none">
                    <div class="overflow-x-auto">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Sucursal</th>
                                    <th>Estado</th>
                                    <th colspan="2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usuarios as $usuario)
                                    <tr
                                        x-show="rolSeleccionado === null || rolSeleccionado === {{ $usuario->id_rol ?? 'null' }}"
                                    >
                                        <td>
                                            <div class="font-medium text-slate-800 dark:text-white">{{ $usuario->nombre }} {{ $usuario->apellido }}</div>
                                        </td>
                                        <td class="text-slate-500 dark:text-zinc-400">{{ $usuario->email }}</td>
                                        <td>
                                            <span class="inline-flex items-center rounded-full bg-[#0c9f9c]/10 px-2.5 py-0.5 text-xs font-semibold text-[#0c9f9c]">{{ $usuario->rol?->tipo_rol ?? 'Sin rol' }}</span>
                                        </td>
                                        <td class="text-slate-500 dark:text-zinc-400">{{ $usuario->sucursal?->nombre_sucursal ?? 'Sin sucursal' }}</td>
                                        <td>
                                            @if($usuario->es_activo)
                                                <span class="inline-flex rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">Activo</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-red-500/15 px-2.5 py-1 text-xs font-semibold text-red-600 dark:text-red-400">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('usuarios.permisos', $usuario) }}" class="text-slate-400 hover:text-[#0c9f9c] dark:hover:text-[#0c9f9c] transition" title="Permisos">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                </a>
                                                <a href="{{ route('usuarios.edit', $usuario) }}" class="text-[#0c9f9c] hover:text-[#0a8582] transition" title="Editar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                                @if($usuario->id === auth()->id() || $usuario->rol?->tipo_rol === 'SuperAdmin')
                                                    <span class="text-slate-300 dark:text-zinc-600 text-xs">Protegido</span>
                                                @else
                                                <form action="{{ route('usuarios.delete', $usuario) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');" style="margin:0; padding:0;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-600 transition" title="Eliminar" style="background:none; border:none; cursor:pointer; padding:0;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-10 text-slate-500 dark:text-zinc-400">No hay usuarios registrados aún.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-layouts::app>