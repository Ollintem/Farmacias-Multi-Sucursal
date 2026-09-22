<x-layouts::app :title="__('Editar usuario: ' . $usuario->nombre . ' ' . $usuario->apellido)">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Gestion de personal</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-800 dark:text-white">Editar usuario: {{ $usuario->nombre }} {{ $usuario->apellido }}</h1>
                </div>

                <div class="theme-tools">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Volver</a>
                    <a href="{{ route('usuarios.permisos', $usuario) }}" class="theme-button theme-button-secondary">Permisos</a>
                </div>
            </div>

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

            <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="module-table">
                    <div class="p-5">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Datos del usuario</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Actualiza la informacion general de la cuenta.</p>

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
                                <label class="mb-2 block text-sm font-medium">Nombre de usuario</label>
                                <input name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}" class="theme-input" required>
                                @error('nombre_usuario')
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
                                <select name="id_rol" class="theme-input" required>
                                    <option value="">Selecciona un rol</option>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id }}" {{ (int) old('id_rol', $usuario->id_rol) === $rol->id ? 'selected' : '' }}>
                                            {{ $rol->tipo_rol }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_rol')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Sucursal</label>
                                <select name="id_sucursal" class="theme-input">
                                    <option value="">Selecciona una sucursal</option>
                                    @foreach($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ (int) old('id_sucursal', $usuario->id_sucursal) === $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre_sucursal }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_sucursal')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Nueva contrasena</label>
                                <input type="password" name="password" class="theme-input" minlength="8" autocomplete="new-password">
                                <p class="mt-1 text-xs text-slate-500">Dejala en blanco para mantener la actual.</p>
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
    </div>
</x-layouts::app>
