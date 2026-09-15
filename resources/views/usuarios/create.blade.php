<x-layouts::app :title="__('Nuevo usuario')">
    <div id="theme-shell" class="theme-light">
        <style>
            #theme-shell {
                width: 100%;
                min-height: 100%;
                transition: all 0.25s ease;
            }

            .theme-light {
                background: linear-gradient(180deg, #f4f9f3 0%, #edf3ef 100%);
                color: #0f172a;
            }

            .theme-dark {
                background: linear-gradient(180deg, #111827 0%, #0b1220 100%);
                color: #f8fafc;
            }

            .theme-shell-inner {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                width: 100%;
                border-radius: 1.5rem;
                padding: 1.5rem;
                transition: all 0.25s ease;
            }

            .theme-light .theme-shell-inner {
                background: rgba(255, 255, 255, 0.55);
                border: 1px solid #d8e5d8;
                box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
            }

            .theme-dark .theme-shell-inner {
                background: rgba(15, 23, 42, 0.78);
                border: 1px solid rgba(148, 163, 184, 0.25);
                box-shadow: 0 12px 30px rgba(2, 6, 23, 0.45);
            }

            .theme-card {
                border-radius: 1.25rem;
                border: 1px solid transparent;
                padding: 1.5rem;
                transition: all 0.25s ease;
            }

            .theme-light .theme-card {
                background: rgba(255, 255, 255, 0.9);
                border-color: #dfeae0;
                color: #0f172a;
            }

            .theme-dark .theme-card {
                background: rgba(15, 23, 42, 0.72);
                border-color: rgba(148, 163, 184, 0.25);
                color: #f8fafc;
            }

            .theme-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.9rem;
                padding: 0.7rem 1rem;
                font-weight: 600;
                transition: all 0.2s ease;
                border: 1px solid transparent;
                text-decoration: none;
            }

            .theme-light .theme-button-primary {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                color: #052e16;
            }

            .theme-dark .theme-button-primary {
                background: linear-gradient(135deg, #34d399, #10b981);
                color: #06241a;
            }

            .theme-light .theme-button-secondary {
                background: rgba(255, 255, 255, 0.8);
                border-color: #d8e5d8;
                color: #0f172a;
            }

            .theme-dark .theme-button-secondary {
                background: rgba(15, 23, 42, 0.8);
                border-color: rgba(148, 163, 184, 0.25);
                color: #f8fafc;
            }

            .theme-input {
                width: 100%;
                border-radius: 0.9rem;
                border: 1px solid #dfeae0;
                background: rgba(255, 255, 255, 0.85);
                color: #0f172a;
                padding: 0.7rem 0.9rem;
                outline: none;
                transition: all 0.2s ease;
            }

            .theme-dark .theme-input {
                background: rgba(15, 23, 42, 0.7);
                border-color: rgba(148, 163, 184, 0.25);
                color: #f8fafc;
            }

            .theme-input:focus {
                border-color: #34d399;
                box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.15);
            }

            .theme-switch {
                display: inline-flex;
                align-items: center;
                gap: 0.75rem;
                border: none;
                background: transparent;
                cursor: pointer;
                color: inherit;
            }

            .theme-switch-track {
                position: relative;
                display: inline-flex;
                width: 3.1rem;
                height: 1.8rem;
                border-radius: 9999px;
                background: rgba(148, 163, 184, 0.4);
                transition: all 0.2s ease;
                padding: 0.2rem;
            }

            .theme-switch-thumb {
                position: absolute;
                top: 0.2rem;
                left: 0.2rem;
                width: 1.4rem;
                height: 1.4rem;
                border-radius: 9999px;
                background: white;
                box-shadow: 0 2px 10px rgba(15, 23, 42, 0.18);
                transition: transform 0.2s ease;
            }

            .theme-dark .theme-switch-thumb {
                transform: translateX(1.3rem);
                background: #d1fae5;
            }

            .theme-switch-text {
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }
        </style>

        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Alta de personal</p>
                    <h1 class="mt-2 text-3xl font-bold">Registrar nuevo usuario</h1>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" id="theme-toggle" class="theme-switch" aria-label="Cambiar tema">
                        <span class="theme-switch-track">
                            <span class="theme-switch-thumb"></span>
                        </span>
                        <span class="theme-switch-text">Claro</span>
                    </button>

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

        <script>
            (() => {
                const shell = document.getElementById('theme-shell');
                const toggle = document.getElementById('theme-toggle');
                const label = toggle?.querySelector('.theme-switch-text');

                const applyTheme = (darkMode) => {
                    shell.classList.toggle('theme-dark', darkMode);
                    shell.classList.toggle('theme-light', !darkMode);
                    if (label) {
                        label.textContent = darkMode ? 'Oscuro' : 'Claro';
                    }
                };

                const savedTheme = localStorage.getItem('farmacia-theme');
                applyTheme(savedTheme === 'dark');

                toggle?.addEventListener('click', () => {
                    const isDark = !shell.classList.contains('theme-dark');
                    localStorage.setItem('farmacia-theme', isDark ? 'dark' : 'light');
                    applyTheme(isDark);
                });
            })();
        </script>
    </div>
</x-layouts::app>
