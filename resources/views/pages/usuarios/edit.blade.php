<x-layouts::app :title="__('Editar usuario')">
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
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Gestión de personal</p>
                    <h1 class="mt-2 text-3xl font-bold">Editar usuario: {{ $usuario->nombre }} {{ $usuario->apellido }}</h1>
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

            @if(session('success'))
                <div class="rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="theme-card">
                    <h2 class="text-lg font-semibold">Datos del usuario</h2>
                    <p class="mt-1 text-sm text-slate-500">Actualiza la información general de la cuenta.</p>

                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Nombre</label>
                            <input name="nombre" value="{{ old('nombre', $usuario->nombre) }}" class="theme-input" required>
                            @error('nombre')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Apellido</label>
                            <input name="apellido" value="{{ old('apellido', $usuario->apellido) }}" class="theme-input" required>
                            @error('apellido')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Nombre de usuario</label>
                            <input name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}" class="theme-input" required>
                            @error('nombre_usuario')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Email</label>
                            <input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="theme-input" required>
                            @error('email')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
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
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
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
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Nueva contraseña</label>
                            <input type="password" name="password" class="theme-input" minlength="8" aria-describedby="password-help" autocomplete="new-password">
                            <p id="password-help" class="mt-1 text-xs text-slate-500">Déjala en blanco para mantener la actual. Si la cambias, usa al menos 8 caracteres.</p>
                            @error('password')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Confirmar nueva contraseña</label>
                            <input type="password" name="password_confirmation" class="theme-input" autocomplete="new-password">
                            <p class="mt-1 text-xs text-slate-500">Solo es obligatoria si escribes una nueva contraseña.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center">
                        <input type="hidden" name="es_activo" value="0">
                        <input type="checkbox" name="es_activo" value="1" {{ old('es_activo', $usuario->es_activo) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500">
                        <label class="ml-2 text-sm">Usuario activo</label>
                    </div>
                </div>

                <div class="theme-card mt-6">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Permisos por módulo</h2>
                            <p class="mt-1 text-sm text-slate-500">Marca de forma granular qué puede hacer el usuario en cada módulo.</p>
                        </div>
                        <button type="button" id="permisos-toggle-all" class="theme-button theme-button-secondary">Activar todo</button>
                    </div>
                    @error('permisos')
                        <span class="mt-2 block text-sm text-red-500">{{ $message }}</span>
                    @enderror

                    @php
                        $permisosFormulario = old('permisos');
                        $permisosActivosSeguros = $permisosActivos ?? [];
                        $permisosSeleccionadosPorModulo = [];

                        if ($permisosFormulario !== null) {
                            foreach ($modulos as $moduloClave) {
                                $permisosSeleccionadosPorModulo[$moduloClave->id] = array_map(
                                    'intval',
                                    (array) ($permisosFormulario[$moduloClave->id] ?? [])
                                );
                            }
                        } else {
                            foreach ($modulos as $moduloClave) {
                                $permisosSeleccionadosPorModulo[$moduloClave->id] = [];
                                foreach ($permisos as $permisoClave) {
                                    if (! empty($permisosActivosSeguros[$moduloClave->id.'-'.$permisoClave->id])) {
                                        $permisosSeleccionadosPorModulo[$moduloClave->id][] = (int) $permisoClave->id;
                                    }
                                }
                            }
                        }
                    @endphp

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Módulo</th>
                                    @foreach($permisos as $permiso)
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ $permiso->tipo_permiso }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Todos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($modulos as $modulo)
                                    @php
                                        $activosModulo = $permisosSeleccionadosPorModulo[$modulo->id] ?? [];
                                        $todosActivosFila = count($permisos) > 0 && count($activosModulo) === count($permisos);
                                    @endphp
                                    <tr class="bg-transparent hover:bg-emerald-50/50" data-permisos-row="{{ $modulo->id }}">
                                        <td class="px-4 py-3 text-sm font-medium">{{ $modulo->nombre_modulo }}</td>
                                        @foreach($permisos as $permiso)
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" name="permisos[{{ $modulo->id }}][]" value="{{ $permiso->id }}" @checked(in_array((int) $permiso->id, $activosModulo, true)) class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500" data-permiso-checkbox>
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500" data-permisos-row-toggle title="Marcar todos los permisos de {{ $modulo->nombre_modulo }}" @checked($todosActivosFila)>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar cambios</button>
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

                const syncRowToggle = (row) => {
                    const boxes = [...row.querySelectorAll('[data-permiso-checkbox]')];
                    const rowToggle = row.querySelector('[data-permisos-row-toggle]');
                    if (rowToggle) {
                        rowToggle.checked = boxes.length > 0 && boxes.every((box) => box.checked);
                    }
                };

                const syncToggleAllLabel = () => {
                    const toggleAll = document.getElementById('permisos-toggle-all');
                    if (!toggleAll) {
                        return;
                    }
                    const boxes = [...document.querySelectorAll('[data-permiso-checkbox]')];
                    const activate = boxes.some((box) => !box.checked);
                    toggleAll.textContent = activate ? 'Activar todo' : 'Desactivar todo';
                };

                document.querySelectorAll('[data-permisos-row]').forEach((row) => {
                    syncRowToggle(row);

                    row.querySelector('[data-permisos-row-toggle]')?.addEventListener('change', (event) => {
                        row.querySelectorAll('[data-permiso-checkbox]').forEach((box) => {
                            box.checked = event.target.checked;
                        });
                        syncToggleAllLabel();
                    });

                    row.querySelectorAll('[data-permiso-checkbox]').forEach((box) => {
                        box.addEventListener('change', () => {
                            syncRowToggle(row);
                            syncToggleAllLabel();
                        });
                    });
                });

                syncToggleAllLabel();

                document.getElementById('permisos-toggle-all')?.addEventListener('click', (event) => {
                    const boxes = [...document.querySelectorAll('[data-permiso-checkbox]')];
                    const activate = boxes.some((box) => !box.checked);
                    boxes.forEach((box) => {
                        box.checked = activate;
                    });
                    document.querySelectorAll('[data-permisos-row]').forEach(syncRowToggle);
                    event.target.textContent = activate ? 'Desactivar todo' : 'Activar todo';
                });
            })();
        </script>
    </div>
</x-layouts::app>
