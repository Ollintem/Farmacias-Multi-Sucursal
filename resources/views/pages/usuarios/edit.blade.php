<x-layouts::app :title="__(($modo ?? 'editar') === 'ver' ? 'Ver usuario' : 'Editar usuario')">
    @php
        $soloLectura = ($modo ?? 'editar') === 'ver';
    @endphp
    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Gestión de personal</p>
                    <h1 class="mt-2 text-3xl font-bold">{{ $soloLectura ? 'Ver usuario' : 'Editar usuario' }}: {{ $usuario->nombre }} {{ $usuario->apellido }}</h1>
                    @if($soloLectura)
                        <p class="mt-2 text-sm text-slate-500">Consulta de solo lectura con los permisos activados del usuario.</p>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Volver</a>
                    @if($soloLectura)
                        <a href="{{ route('usuarios.edit', $usuario) }}" class="theme-button theme-button-primary">Editar permisos</a>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600">
                    {{ session('success') }}
                </div>
            @endif

            @if($soloLectura)
                <div class="theme-card">
                    <h2 class="text-lg font-semibold">Permisos activados</h2>
                    <p class="mt-1 text-sm text-slate-500">Resumen de lo que puede hacer {{ $usuario->nombre }} en cada módulo.</p>

                    @forelse($permisosAgrupados ?? [] as $nombreModulo => $tiposPermiso)
                        <div class="mt-4 flex flex-col gap-2 border-t border-slate-200 pt-4 first:border-t-0 first:pt-0 md:flex-row md:items-center md:justify-between">
                            <span class="text-sm font-medium">{{ $nombreModulo }}</span>
                            <span class="flex flex-wrap gap-2">
                                @foreach($tiposPermiso as $tipoPermiso)
                                    <span class="theme-badge">{{ $tipoPermiso }}</span>
                                @endforeach
                            </span>
                        </div>
                    @empty
                        <p class="mt-4 text-sm text-slate-500">Este usuario no tiene permisos activados.</p>
                    @endforelse
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
                            <input name="nombre" value="{{ old('nombre', $usuario->nombre) }}" class="theme-input" required @disabled($soloLectura)>
                            @error('nombre')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Apellido</label>
                            <input name="apellido" value="{{ old('apellido', $usuario->apellido) }}" class="theme-input" required @disabled($soloLectura)>
                            @error('apellido')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Nombre de usuario</label>
                            <input name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}" class="theme-input" required @disabled($soloLectura)>
                            @error('nombre_usuario')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Email</label>
                            <input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="theme-input" required @disabled($soloLectura)>
                            @error('email')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Rol</label>
                            <select name="id_rol" class="theme-input" required @disabled($soloLectura)>
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
                            <select name="id_sucursal" class="theme-input" @disabled($soloLectura)>
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

                        @if(! $soloLectura)
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
                        @endif
                    </div>

                    <div class="mt-6 flex items-center">
                        <input type="hidden" name="es_activo" value="0">
                        <input type="checkbox" name="es_activo" value="1" {{ old('es_activo', $usuario->es_activo) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500" @disabled($soloLectura)>
                        <label class="ml-2 text-sm">Usuario activo</label>
                    </div>
                </div>

                <div class="theme-card mt-6">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Permisos por módulo</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $soloLectura ? 'Permisos que tiene activos el usuario en cada módulo.' : 'Marca de forma granular qué puede hacer el usuario en cada módulo.' }}</p>
                        </div>
                        @if(! $soloLectura)
                            <button type="button" id="permisos-toggle-all" class="theme-button theme-button-secondary">Activar todo</button>
                        @endif
                    </div>
                    @error('permisos')
                        <span class="mt-2 block text-sm text-red-500">{{ $message }}</span>
                    @enderror

                    @php
                        $columnasPermiso = ['ver' => 'Ver', 'crear' => 'Crear', 'editar' => 'Editar', 'borrar' => 'Borrar'];
                        $permisosFormulario = old('permisos');
                        $permisosActivosSeguros = $permisosActivos ?? [];
                        $permisosSeleccionadosPorModulo = [];

                        foreach ($modulos as $moduloClave) {
                            $permisosSeleccionadosPorModulo[$moduloClave->id] = [];
                            foreach ($columnasPermiso as $clavePermiso => $etiquetaPermiso) {
                                if ($permisosFormulario !== null) {
                                    $permisosSeleccionadosPorModulo[$moduloClave->id][$clavePermiso] = ! empty($permisosFormulario[$moduloClave->id][$clavePermiso] ?? null);
                                } else {
                                    $permisosSeleccionadosPorModulo[$moduloClave->id][$clavePermiso] = ! empty($permisosActivosSeguros[$moduloClave->id][$clavePermiso] ?? null);
                                }
                            }
                        }
                    @endphp

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Módulo</th>
                                    @foreach($columnasPermiso as $etiquetaPermiso)
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ $etiquetaPermiso }}</th>
                                    @endforeach
                                    @if(! $soloLectura)
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Todos</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($modulos as $modulo)
                                    @php
                                        $activosModulo = $permisosSeleccionadosPorModulo[$modulo->id] ?? [];
                                        $todosActivosFila = count(array_filter($activosModulo)) === count($columnasPermiso);
                                    @endphp
                                    <tr class="bg-transparent hover:bg-emerald-50/50" data-permisos-row="{{ $modulo->id }}">
                                        <td class="px-4 py-3 text-sm font-medium">{{ $modulo->nombre_modulo }}</td>
                                        @foreach($columnasPermiso as $clavePermiso => $etiquetaPermiso)
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" name="permisos[{{ $modulo->id }}][{{ $clavePermiso }}]" value="1" @checked(! empty($activosModulo[$clavePermiso])) class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500" data-permiso-checkbox @disabled($soloLectura)>
                                            </td>
                                        @endforeach
                                        @if(! $soloLectura)
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500" data-permisos-row-toggle title="Marcar todos los permisos de {{ $modulo->nombre_modulo }}" @checked($todosActivosFila)>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                    @if($soloLectura)
                        <a href="{{ route('usuarios.edit', $usuario) }}" class="theme-button theme-button-primary">Editar permisos</a>
                    @else
                        <button type="submit" class="theme-button theme-button-primary">Guardar cambios</button>
                    @endif
                </div>
            </form>
        </div>

        <script>
            (() => {
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
