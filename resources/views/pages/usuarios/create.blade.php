<x-layouts::app :title="__('Nuevo usuario')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado homologado: hero verde --}}
            <section class="module-card noti-hero" aria-label="Encabezado de usuario">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Alta de personal · Nuevo registro</p>
                    <h1 class="mt-2">Registrar nuevo usuario</h1>
                </div>
            </section>

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
                                <x-option-pick name="id_rol" label="Roles" placeholder="Selecciona un rol" :options="$roles->pluck('tipo_rol', 'id')" />
                                @error('id_rol')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
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
</x-layouts::app>
