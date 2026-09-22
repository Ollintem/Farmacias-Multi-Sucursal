<x-layouts::app :title="__('Permisos: ' . $usuario->nombre)">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Permisos</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-800 dark:text-white">{{ $usuario->nombre }} {{ $usuario->apellido }}</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">{{ $usuario->rol?->tipo_rol ?? 'Sin rol' }} &middot; {{ $usuario->email }}</p>
                </div>

                <div class="theme-tools">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Volver</a>
                    <a href="{{ route('usuarios.edit', $usuario) }}" class="theme-button theme-button-primary">Editar usuario</a>
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

            <form action="{{ route('usuarios.updatePermisos', $usuario) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="module-table">
                    <div class="flex flex-col gap-2 p-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800 dark:text-white">Permisos por modulo</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Marca que puede hacer el usuario en cada modulo.</p>
                        </div>
                        <button type="button" id="permisos-toggle-all" class="theme-button theme-button-secondary">Activar todo</button>
                    </div>

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

                    <div class="overflow-x-auto">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Modulo</th>
                                    @foreach($columnasPermiso as $etiquetaPermiso)
                                        <th class="text-center">{{ $etiquetaPermiso }}</th>
                                    @endforeach
                                    <th class="text-center">Todos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modulos as $modulo)
                                    @php
                                        $activosModulo = $permisosSeleccionadosPorModulo[$modulo->id] ?? [];
                                        $todosActivosFila = count(array_filter($activosModulo)) === count($columnasPermiso);
                                    @endphp
                                    <tr data-permisos-row="{{ $modulo->id }}">
                                        <td>
                                            <div class="font-medium text-slate-800 dark:text-white">{{ $modulo->nombre_modulo }}</div>
                                        </td>
                                        @foreach($columnasPermiso as $clavePermiso => $etiquetaPermiso)
                                            <td class="text-center">
                                                <input type="checkbox" name="permisos[{{ $modulo->id }}][{{ $clavePermiso }}]" value="1" @checked(! empty($activosModulo[$clavePermiso])) data-permiso-checkbox>
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <input type="checkbox" data-permisos-row-toggle title="Marcar todos los permisos de {{ $modulo->nombre_modulo }}" @checked($todosActivosFila)>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar permisos</button>
                </div>
            </form>
        </div>
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
                if (!toggleAll) return;
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
                boxes.forEach((box) => { box.checked = activate; });
                document.querySelectorAll('[data-permisos-row]').forEach(syncRowToggle);
                event.target.textContent = activate ? 'Desactivar todo' : 'Activar todo';
            });
        })();
    </script>
</x-layouts::app>
