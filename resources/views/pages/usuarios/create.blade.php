<x-layouts::app :title="__('Nuevo usuario')">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Alta de personal</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-800 dark:text-white">Registrar nuevo usuario</h1>
                </div>

                <div class="theme-tools">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Volver</a>
                </div>
            </div>

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

            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf

                <div class="module-table">
                    <div class="p-5">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Datos del usuario</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Completa la informacion para registrar un nuevo usuario.</p>

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
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
                                <label class="mb-2 block text-sm font-medium">Nombre de usuario</label>
                                <input name="nombre_usuario" value="{{ old('nombre_usuario') }}" class="theme-input" required>
                                @error('nombre_usuario')
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
                                <select name="id_rol" class="theme-input" required>
                                    <option value="">Selecciona un rol</option>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id }}" {{ old('id_rol') == $rol->id ? 'selected' : '' }}>
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
                                        <option value="{{ $sucursal->id }}" {{ (old('id_sucursal', $selectedSucursalId ?? '')) == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre_sucursal }}
                                        </option>
                                    @endforeach
                                </select>
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
    </div>
</x-layouts::app>
