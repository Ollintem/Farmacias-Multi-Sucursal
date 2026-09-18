<x-layouts::app :title="__('Nuevo usuario')">
    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Alta de personal</p>
                    <h1 class="mt-2 text-3xl font-bold">Registrar nuevo usuario</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('roles.index') }}" class="theme-button theme-button-secondary">Gestionar roles</a>
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Volver</a>
                </div>
            </div>

            <form action="{{ route('usuarios.store') }}" method="POST" class="theme-card">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Nombre</label>
                        <input name="nombre" value="{{ old('nombre') }}" class="theme-input" required>
                        @error('nombre')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Apellido</label>
                        <input name="apellido" value="{{ old('apellido') }}" class="theme-input" required>
                        @error('apellido')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Nombre de usuario</label>
                        <input name="nombre_usuario" value="{{ old('nombre_usuario') }}" class="theme-input" required>
                        @error('nombre_usuario')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="theme-input" required>
                        @error('email')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label class="block text-sm font-medium">Rol</label>
                            <a href="{{ route('roles.create') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-800">+ Nuevo rol</a>
                        </div>
                        <select name="id_rol" class="theme-input" required>
                            <option value="">Selecciona un rol</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" {{ old('id_rol') == $rol->id ? 'selected' : '' }}>
                                    {{ $rol->tipo_rol }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_rol')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Sucursal</label>
                        <select name="id_sucursal" class="theme-input">
                            <option value="">Selecciona una sucursal</option>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}" {{ old('id_sucursal') == $sucursal->id ? 'selected' : '' }}>
                                    {{ $sucursal->nombre_sucursal }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_sucursal')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Contraseña</label>
                        <input type="password" name="password" class="theme-input" minlength="8" aria-describedby="password-help" required>
                        <p id="password-help" class="mt-1 text-xs text-slate-500">Usa al menos 8 caracteres. Puedes combinar letras, números y símbolos.</p>
                        @error('password')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" class="theme-input" required>
                        <p class="mt-1 text-xs text-slate-500">Vuelve a escribir exactamente la misma contraseña.</p>
                        @error('password_confirmation')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center">
                    <input type="checkbox" name="es_activo" value="1" {{ old('es_activo', true) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500">
                    <label class="ml-2 text-sm">Usuario activo</label>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar usuario</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
