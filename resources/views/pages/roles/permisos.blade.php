<x-layouts::app :title="__('Permisos por rol')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado homologado: hero verde --}}
            <section class="module-card noti-hero" aria-label="Encabezado de permisos por rol">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Personal · Permisos por rol</p>
                    <h1 class="mt-2">Permisos por rol</h1>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">← Volver</a>
                    </div>
                </div>
            </section>

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

            <section class="module-card noti-listado" aria-label="Selección de rol">
                <div class="p-5">
                    <form action="{{ route('roles.permisos') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="w-full sm:max-w-xs">
                            <label for="rol-selector" class="mb-2 block text-sm font-medium">Rol</label>
                            <select
                                id="rol-selector"
                                name="rol"
                                class="theme-input"
                                onchange="this.form.submit()"
                                @disabled($roles->isEmpty())
                            >
                                @forelse($roles as $rol)
                                    <option value="{{ $rol->id }}" @selected($selectedRol?->id === $rol->id)>
                                        {{ $rol->tipo_rol }} · {{ $rol->usuarios_count }} usuario(s)
                                    </option>
                                @empty
                                    <option value="">No hay roles registrados</option>
                                @endforelse
                            </select>
                        </div>
                        @if($usuarios->isNotEmpty())
                            <div class="w-full sm:max-w-sm">
                                <label for="usuario-selector" class="mb-2 block text-sm font-medium">Usuario <span class="font-normal opacity-50">(excepciones)</span></label>
                                <select
                                    id="usuario-selector"
                                    name="usuario"
                                    class="theme-input"
                                    onchange="this.form.submit()"
                                >
                                    <option value="">Sin usuario</option>
                                    @foreach($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}" @selected($selectedUsuario?->id === $usuario->id)>
                                            {{ $usuario->nombre }} {{ $usuario->apellido }} · {{ $usuario->email }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <noscript>
                            <button type="submit" class="theme-button theme-button-secondary">Ver permisos</button>
                        </noscript>
                    </form>

                    @if($selectedRol)
                        <p class="mt-3 text-sm text-slate-500 dark:text-zinc-400">
                            Configuración de <span class="font-semibold text-slate-700 dark:text-zinc-200">{{ $selectedRol->tipo_rol }}</span>.
                            Los cambios aplican a todos los usuarios con este rol.
                        </p>
                    @endif
                </div>
            </section>

            <section class="module-card noti-listado" aria-label="Permisos por módulo">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Permisos por módulo</h2>
                        <p class="theme-subtle text-sm">Marque qué puede hacer el rol en cada módulo</p>
                    </div>
                    <div class="noti-cabecera-lado">
                        <button type="button" id="permisos-toggle-all" class="noti-desplegar" @disabled($selectedRol === null || ! $puedeEditar || $selectedRol->tipo_rol === 'SuperAdmin')>Activar todo</button>
                    </div>
                </header>
                <div class="p-5">
                    @if($selectedRol === null)
                        <div class="rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm text-amber-600 dark:text-amber-400">
                            No hay roles registrados para configurar.
                        </div>
                    @elseif($selectedRol->tipo_rol === 'SuperAdmin')
                        <div class="rounded-xl border border-sky-500/25 bg-sky-500/10 px-4 py-3 text-sm text-sky-700 dark:text-sky-300">
                            El rol SuperAdmin siempre tiene acceso total. Sus permisos no se pueden restringir.
                        </div>
                    @elseif($configuracion->isEmpty())
                        <div class="mb-4 rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm text-amber-600 dark:text-amber-400">
                            Este rol aún no tiene permisos guardados. Mientras no guardes una configuración, se mantiene la lógica individual existente de cada usuario.
                        </div>
                    @endif

                    @if(! $puedeEditar && $selectedRol && $selectedRol->tipo_rol !== 'SuperAdmin')
                        <div class="mb-4 rounded-xl border border-slate-500/25 bg-slate-500/10 px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                            Solo lectura: no tienes permiso para editar la configuración de permisos.
                        </div>
                    @endif

                    @if($selectedRol)
                        @php
                            $esSuperAdmin = $selectedRol->tipo_rol === 'SuperAdmin';
                            $soloLectura = $esSuperAdmin || ! $puedeEditar;
                        @endphp

                        <form action="{{ route('roles.permisos.update', $selectedRol) }}" method="POST">
                            @csrf
                            @method('PUT')

                            @if($selectedUsuario)
                                <input type="hidden" name="usuario" value="{{ $selectedUsuario->id }}">
                            @endif

                            @if($errors->has('permisos'))
                                <div class="mb-4 rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-600 dark:text-red-400">
                                    {{ $errors->first('permisos') }}
                                </div>
                            @endif

                            @php
                                $columnasPermiso = ['ver' => 'Ver', 'crear' => 'Crear', 'editar' => 'Editar', 'borrar' => 'Borrar'];
                            @endphp

                            <div class="module-table !rounded-none !border-0 !shadow-none">
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
                                                    $permisosPorAccion = $modulo->permisos->keyBy('accion');
                                                    $todosActivosFila = $esSuperAdmin || collect($columnasPermiso)
                                                        ->keys()
                                                        ->every(function ($accion) use ($permisosPorAccion, $configuracion): bool {
                                                            $permiso = $permisosPorAccion->get($accion);

                                                            return $permiso !== null
                                                                && (bool) ($configuracion->get($permiso->id)?->activo ?? false);
                                                        });
                                                @endphp
                                                <tr data-permisos-row="{{ $modulo->id }}">
                                                    <td>
                                                        <div class="font-medium text-slate-800 dark:text-white">{{ $modulo->nombre_modulo }}</div>
                                                    </td>
                                                    @foreach($columnasPermiso as $accion => $etiquetaPermiso)
                                                        @php
                                                            $permiso = $permisosPorAccion->get($accion);
                                                        @endphp
                                                        <td class="text-center">
                                                            @if($permiso !== null)
                                                                <input
                                                                    type="checkbox"
                                                                    name="permisos[{{ $permiso->id }}]"
                                                                    value="1"
                                                                    @checked($esSuperAdmin || ($configuracion->get($permiso->id)?->activo ?? false))
                                                                    @disabled($soloLectura)
                                                                    data-permiso-checkbox
                                                                >
                                                            @else
                                                                <span class="text-slate-400">—</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td class="text-center">
                                                        <input
                                                            type="checkbox"
                                                            data-permisos-row-toggle
                                                            title="Marcar todos los permisos de {{ $modulo->nombre_modulo }}"
                                                            @checked($todosActivosFila)
                                                            @disabled($soloLectura)
                                                        >
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                                <button
                                    type="submit"
                                    class="theme-button theme-button-primary"
                                    @disabled($soloLectura)
                                >Guardar permisos</button>
                            </div>
                        </form>
                    @endif
                </div>
            </section>

            @if($selectedUsuario)
                <section class="module-card noti-listado" aria-label="Excepciones individuales del usuario">
                    <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                        <div>
                            <h2>Excepciones de {{ $selectedUsuario->nombre }} {{ $selectedUsuario->apellido }}</h2>
                            <p class="theme-subtle text-sm">Solo se muestran los permisos registrados individualmente en <code>permisos_activados</code>.</p>
                        </div>
                    </header>
                    <div class="p-5">
                        @php
                            $columnasExcepcion = ['ver' => 'Ver', 'crear' => 'Crear', 'editar' => 'Editar', 'borrar' => 'Borrar'];
                            $modulosConExcepcion = $modulos->filter(fn ($modulo) => $excepciones->has($modulo->id));
                        @endphp

                        @if($modulosConExcepcion->isEmpty())
                            <div class="rounded-xl border border-slate-500/25 bg-slate-500/10 px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                Este usuario no tiene excepciones individuales registradas.
                            </div>
                        @else
                            <div class="module-table !rounded-none !border-0 !shadow-none">
                                <div class="overflow-x-auto">
                                    <table class="users-table">
                                        <thead>
                                            <tr>
                                                <th>Modulo</th>
                                                @foreach($columnasExcepcion as $etiquetaPermiso)
                                                    <th class="text-center">{{ $etiquetaPermiso }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modulosConExcepcion as $modulo)
                                                @php
                                                    $excepcion = $excepciones->get($modulo->id);
                                                @endphp
                                                <tr
                                                    data-excepcion-usuario="{{ $selectedUsuario->id }}"
                                                    data-excepcion-modulo="{{ $modulo->id }}"
                                                >
                                                    <td>
                                                        <div class="font-medium text-slate-800 dark:text-white">{{ $modulo->nombre_modulo }}</div>
                                                    </td>
                                                    @foreach($columnasExcepcion as $accion => $etiquetaPermiso)
                                                        @php
                                                            $campoExcepcion = 'puede_'.$accion;
                                                        @endphp
                                                        <td class="text-center">
                                                            <input
                                                                type="checkbox"
                                                                data-excepcion-checkbox
                                                                @checked((bool) $excepcion->{$campoExcepcion})
                                                                disabled
                                                            >
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif
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
