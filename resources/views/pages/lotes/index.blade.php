<x-layouts::app :title="__('Lotes y caducidades')">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Trazabilidad</p>
                    <h1 class="mt-2 text-3xl font-bold">Lotes y caducidades</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('lotes.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-primary">+ Registrar nuevo lote</a>
                </div>
            </div>

            @include('pages.inventario.tabs', ['seccion' => 'lotes', 'selectedSucursal' => $selectedSucursal])

            @php
                // Parámetros que toda tarjeta conserva al cambiar de filtro.
                $filtrosBase = array_filter([
                    'sucursal' => $selectedSucursal?->id,
                    'buscar' => $busqueda !== '' ? $busqueda : null,
                ]);

                // Clic en la tarjeta activa → vuelve a "Total de lotes".
                $enlaceTarjeta = function (string $clave) use ($filtrosBase, $filtroEstado) {
                    if ($clave === 'todos' || $filtroEstado === $clave) {
                        return route('lotes.index', $filtrosBase);
                    }

                    return route('lotes.index', array_merge($filtrosBase, ['estado' => $clave]));
                };
            @endphp

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <a href="{{ $enlaceTarjeta('todos') }}" @class(['module-stat module-stat-filtro', 'module-stat-filtro-activo' => $filtroEstado === 'todos']) @if($filtroEstado === 'todos') aria-current="true" @endif>
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Total de lotes</span>
                        <span class="stat-pill info">{{ $totalLotes }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $totalLotes }}</p>
                    <p class="mt-1 text-xs theme-subtle">Todos los lotes de la sucursal</p>
                </a>

                <a href="{{ $enlaceTarjeta('vigentes') }}" @class(['module-stat module-stat-filtro', 'module-stat-filtro-activo' => $filtroEstado === 'vigentes']) @if($filtroEstado === 'vigentes') aria-current="true" @endif>
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Vigentes</span>
                        <span class="stat-pill positive">OK</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $vigentes }}</p>
                    <p class="mt-1 text-xs theme-subtle">Sin riesgo de caducidad</p>
                </a>

                <a href="{{ $enlaceTarjeta('por-caducar') }}" @class(['module-stat module-stat-filtro', 'module-stat-filtro-activo' => $filtroEstado === 'por-caducar']) @if($filtroEstado === 'por-caducar') aria-current="true" @endif>
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Por caducar</span>
                        <span class="stat-pill warning">{{ $porCaducar }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $porCaducar }}</p>
                    <p class="mt-1 text-xs theme-subtle">Menos de 90 días o sin fecha</p>
                </a>

                <a href="{{ $enlaceTarjeta('caducados') }}" @class(['module-stat module-stat-filtro', 'module-stat-filtro-activo' => $filtroEstado === 'caducados']) @if($filtroEstado === 'caducados') aria-current="true" @endif>
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Caducados</span>
                        <span class="stat-pill danger">{{ $caducados }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $caducados }}</p>
                    <p class="mt-1 text-xs theme-subtle">Caducados y anulados</p>
                </a>
            </div>

            <div class="module-card p-5 sm:p-6"
                 x-data="{
                     ...buscadorTabla(),
                     merma: {
                         abierto: {{ old('lote_id') !== null && $errors->any() ? 'true' : 'false' }},
                         lote: @js(old('lote_id', '')),
                         folio: @js(old('folio', '')),
                         restante: @js((int) old('restante', 0)),
                     },
                     anular: {
                         abierto: false,
                         folio: '',
                         accion: '',
                     },
                     abrirMerma(datos) {
                         this.merma.lote = datos.lote;
                         this.merma.folio = datos.folio;
                         this.merma.restante = Number(datos.restante);
                         this.merma.abierto = true;
                     },
                     abrirAnular(datos) {
                         this.anular.folio = datos.folio;
                         this.anular.accion = datos.accion;
                         this.anular.abierto = true;
                     },
                 }"
                 x-effect="filtrar($el)"
                 @keydown.escape.window="merma.abierto = false; anular.abierto = false">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</h2>
                    </div>

                    <form method="GET" action="{{ route('lotes.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        @if($filtroEstado !== 'todos')
                            <input type="hidden" name="estado" value="{{ $filtroEstado }}">
                        @endif
                        <input type="search" name="buscar" value="{{ old('buscar', $busqueda) }}" placeholder="Buscar folio, proveedor o producto" class="theme-input w-full sm:w-72" aria-label="Buscar lote" @input.debounce.200ms="texto = $event.target.value">
                        <button type="submit" class="theme-button theme-button-secondary whitespace-nowrap">Buscar</button>
                    </form>
                </div>

                <div class="module-table mt-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Lote</th>
                                    <th class="px-4 py-3 font-semibold">Producto</th>
                                    <th class="px-4 py-3 font-semibold">Proveedor</th>
                                    <th class="px-4 py-3 font-semibold">Sucursal</th>
                                    <th class="px-4 py-3 font-semibold">Cantidad inicial</th>
                                    <th class="px-4 py-3 font-semibold">Restante</th>
                                    <th class="px-4 py-3 font-semibold">Entrada</th>
                                    <th class="px-4 py-3 font-semibold">Caducidad</th>
                                    <th class="px-4 py-3 font-semibold">Estado</th>
                                    <th class="px-4 py-3 font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lotes as $lote)
                                    <tr class="border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5" data-buscar="{{ $lote['folio'] }} {{ $lote['producto'] }} {{ $lote['marca'] }}">
                                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $lote['folio'] }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-slate-800 dark:text-slate-100">{{ $lote['producto'] }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $lote['marca'] }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $lote['sucursal'] }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $lote['cantidad'] }} uds.</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $lote['restante'] === null ? '—' : $lote['restante'].' uds.' }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $lote['fecha_entrada'] }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $lote['fecha_caducidad'] }}</td>
                                        <td class="px-4 py-3">
                                            <span class="status-badge {{ $lote['estado_class'] }}">{{ $lote['estado'] }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <a href="{{ route('lotes.edit', $lote['id']) }}" class="theme-button theme-button-secondary whitespace-nowrap">Editar</a>

                                                @if($lote['restante'] !== null && $lote['restante'] > 0)
                                                    <button
                                                        type="button"
                                                        class="theme-button theme-button-secondary whitespace-nowrap"
                                                        data-lote="{{ $lote['id'] }}"
                                                        data-folio="{{ $lote['folio'] }}"
                                                        data-restante="{{ $lote['restante'] }}"
                                                        x-on:click="abrirMerma($event.currentTarget.dataset)"
                                                    >Dar de baja</button>
                                                @endif

                                                @if(! $lote['anulado'] && $lote['restante_global'] === 0)
                                                    <button
                                                        type="button"
                                                        class="theme-button theme-button-secondary whitespace-nowrap"
                                                        data-folio="{{ $lote['folio'] }}"
                                                        data-accion="{{ route('lotes.anular', $lote['id']) }}"
                                                        x-on:click="abrirAnular($event.currentTarget.dataset)"
                                                    >Anular</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                    <tr data-vacio @if($lotes->isNotEmpty()) style="display: none" @endif>
                                        <td colspan="10" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                                            No se encontraron lotes para los filtros actuales.
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Modal Dar de baja --}}
                <div
                    x-show="merma.abierto"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="display:none;"
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="merma.abierto = false"></div>

                    <div
                        class="relative w-full max-w-lg rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-2xl backdrop-blur-sm dark:border-[#1e3a5f]/60 dark:bg-[#0b1322]/95"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        x-on:click.stop
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold">Dar de baja</h2>
                                <p class="mt-1 text-sm theme-subtle">
                                    Lote <span class="font-semibold" x-text="merma.folio"></span> ·
                                    existencia <span class="font-semibold" x-text="merma.restante"></span> uds.
                                </p>
                            </div>
                            <button type="button" x-on:click="merma.abierto = false" class="opacity-50 transition hover:opacity-100" aria-label="Cerrar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form action="{{ route('lotes.merma') }}" method="POST">
                            @csrf
                            <input type="hidden" name="lote_id" :value="merma.lote">
                            <input type="hidden" name="folio" :value="merma.folio">
                            <input type="hidden" name="restante" :value="merma.restante">
                            <input type="hidden" name="sucursal" value="{{ $selectedSucursal?->id }}">

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Cantidad a dar de baja (uds.)</label>
                                    <input
                                        type="number"
                                        name="cantidad"
                                        min="1"
                                        :max="merma.restante"
                                        value="{{ old('cantidad') }}"
                                        class="theme-input"
                                        required
                                    >
                                    @error('cantidad')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Motivo</label>
                                    <select name="motivo" class="theme-input" required>
                                        <option value="">Selecciona un motivo</option>
                                        <option value="caducado" @selected(old('motivo') === 'caducado')>Caducado</option>
                                        <option value="danado" @selected(old('motivo') === 'danado')>Dañado</option>
                                        <option value="otro" @selected(old('motivo') === 'otro')>Otro</option>
                                    </select>
                                    @error('motivo')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Nota <span class="font-normal opacity-50">(obligatoria si el motivo es «Otro»)</span></label>
                                    <textarea name="nota" maxlength="500" rows="2" class="theme-input">{{ old('nota') }}</textarea>
                                    @error('nota')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>

                                @error('lote_id')
                                    <span class="block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                                @error('sucursal')
                                    <span class="block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" x-on:click="merma.abierto = false" class="theme-button theme-button-secondary">Cancelar</button>
                                <button type="submit" class="theme-button theme-button-primary">Dar de baja</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Modal Anular lote --}}
                <div
                    x-show="anular.abierto"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="display:none;"
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="anular.abierto = false"></div>

                    <div
                        class="relative w-full max-w-lg rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-2xl backdrop-blur-sm dark:border-[#1e3a5f]/60 dark:bg-[#0b1322]/95"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        x-on:click.stop
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold">Anular lote</h2>
                                <p class="mt-1 text-sm theme-subtle">
                                    Lote <span class="font-semibold" x-text="anular.folio"></span> ·
                                    sin existencias en todas las sucursales.
                                </p>
                            </div>
                            <button type="button" x-on:click="anular.abierto = false" class="opacity-50 transition hover:opacity-100" aria-label="Cerrar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            El lote quedará marcado como <span class="font-semibold">Anulado</span> y contará en la
                            tarjeta Caducados. Se conserva su historial de entradas, ventas y mermas;
                            esta acción no se puede deshacer.
                        </p>

                        <form :action="anular.accion" method="POST">
                            @csrf

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" x-on:click="anular.abierto = false" class="theme-button theme-button-secondary">Cancelar</button>
                                <button type="submit" class="theme-button bg-red-600 text-white transition hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700">Anular</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.buscador-cliente')
</x-layouts::app>
