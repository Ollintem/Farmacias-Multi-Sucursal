<x-layouts::app :title="__('Permisos: ' . $usuario->nombre)">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado limpio --}}
            <header class="mb-6" aria-label="Encabezado de permisos">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Permisos · {{ $usuario->rol?->tipo_rol ?? 'Sin rol' }} · {{ $usuario->email }}</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $usuario->nombre }} {{ $usuario->apellido }}</h1>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">← Volver</a>
                        <a href="{{ route('usuarios.edit', $usuario) }}" class="theme-button theme-button-primary">Editar usuario</a>
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

            <section class="module-card noti-listado" aria-label="Permisos por módulo">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Permisos por módulo</h2>
                        <p class="theme-subtle text-sm">Marque qué puede hacer el usuario en cada módulo</p>
                    </div>
                    <div class="noti-cabecera-lado">
                        <button type="button" id="permisos-toggle-all" class="noti-desplegar">Activar todo</button>
                    </div>
                </header>
                <div class="p-5">
            <form action="{{ route('usuarios.updatePermisos', $usuario) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="module-table !rounded-none !border-0 !shadow-none">

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
            </section>
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
