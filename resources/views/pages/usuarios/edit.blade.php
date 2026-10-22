<x-layouts::app :title="__('Editar usuario: ' . $usuario->nombre . ' ' . $usuario->apellido)">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado limpio --}}
            <header class="mb-6" aria-label="Encabezado de usuario">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Gestión de personal · Edición</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Editar usuario: {{ $usuario->nombre }} {{ $usuario->apellido }}</h1>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('usuarios.permisos', $usuario) }}" class="theme-button theme-button-secondary">Permisos</a>
                    </div>
                </div>
            </header>

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

            <section class="module-card noti-listado" aria-label="Datos del usuario">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Datos del usuario</h2>
                        <p class="theme-subtle text-sm">Actualice la información general de la cuenta</p>
                    </div>
                </header>
                <div class="p-5">
            <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="module-table !rounded-none !border-0 !shadow-none">
                    <div class="p-5">

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium">Nombre</label>
                                <input name="nombre" value="{{ old('nombre', $usuario->nombre) }}" class="theme-input" required>
                                @error('nombre')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Apellido</label>
                                <input name="apellido" value="{{ old('apellido', $usuario->apellido) }}" class="theme-input" required>
                                @error('apellido')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Email</label>
                                <input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="theme-input" required>
                                @error('email')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Rol</label>
                                <x-option-pick name="id_rol" label="Roles" placeholder="Selecciona un rol" :options="$roles->pluck('tipo_rol', 'id')" :value="$usuario->id_rol" />
                                @error('id_rol')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Sucursal</label>
                                <input type="hidden" name="id_sucursal" value="{{ $usuario->id_sucursal }}">
                                <p class="theme-input flex items-center font-semibold" aria-readonly="true">{{ $sucursales->firstWhere('id', $usuario->id_sucursal)?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                                <p class="mt-1 text-xs theme-subtle">Es la sucursal del usuario y solo se asigna al crearlo.</p>
                                @error('id_sucursal')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Nueva contrasena</label>
                                <input type="password" name="password" class="theme-input" minlength="8" autocomplete="new-password">
                                <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Dejala en blanco para mantener la actual.</p>
                                @error('password')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Confirmar contrasena</label>
                                <input type="password" name="password_confirmation" class="theme-input" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="mt-6 flex items-center">
                            <input type="hidden" name="es_activo" value="0">
                            <input type="checkbox" name="es_activo" value="1" {{ old('es_activo', $usuario->es_activo) ? 'checked' : '' }}>
                            <label class="ml-2 text-sm">Usuario activo</label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar cambios</button>
                </div>
            </form>
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
