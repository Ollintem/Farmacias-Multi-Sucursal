<x-layouts::app :title="__('Lotes y caducidades')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
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

            {{-- Encabezado homologado: hero verde + KPIs blancos (las tarjetas filtran) --}}
            <section class="module-card noti-hero" aria-label="Encabezado de lotes y caducidades">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Trazabilidad · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    <h1 class="mt-2">Lotes y caducidades</h1>
                    <dl class="noti-resumen noti-kpis">
                        <a href="{{ $enlaceTarjeta('todos') }}" class="noti-kpi" @if($filtroEstado === 'todos') aria-current="true" @endif>
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg> Total de lotes</dt><dd>{{ $totalLotes }}</dd>
                        </a>

                        <a href="{{ $enlaceTarjeta('vigentes') }}" class="noti-kpi" @if($filtroEstado === 'vigentes') aria-current="true" @endif>
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Vigentes</dt><dd>{{ $vigentes }}</dd>
                        </a>

                        <a href="{{ $enlaceTarjeta('por-caducar') }}" @class(['noti-kpi', 'noti-kpi-alerta' => $porCaducar > 0]) @if($filtroEstado === 'por-caducar') aria-current="true" @endif>
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Por caducar</dt><dd>{{ $porCaducar }}</dd>
                        </a>

                        <a href="{{ $enlaceTarjeta('caducados') }}" @class(['noti-kpi', 'noti-kpi-alerta' => $caducados > 0]) @if($filtroEstado === 'caducados') aria-current="true" @endif>
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg> Caducados</dt><dd>{{ $caducados }}</dd>
                        </a>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('lotes.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-primary">+ Registrar nuevo lote</a>
                    </div>
                </div>
            </section>

            @include('pages.inventario.tabs', ['seccion' => 'lotes', 'selectedSucursal' => $selectedSucursal])

            <section class="module-card noti-listado" aria-label="Listado de lotes"
                 x-data="{
                     ...buscadorTabla(),
                     merma: {
                         abierto: {{ old('lote_id') !== null && $errors->any() ? 'true' : 'false' }},
                         lote: @js(old('lote_id', '')),
                         folio: @js(old('folio', '')),
                         restante: @js((int) old('restante', 0)),
                         porCaja: @js((int) old('por_caja', 0)),
                         presentacion: @js(old('presentacion', 'caja')),
                         unidades: @js(old('cantidad', '')),
                         cajas: @js(old('cantidad_cajas', 0)),
                         sueltas: @js(old('cantidad_sueltas', 0)),
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
                         this.merma.porCaja = Number(datos.porCaja);
                         this.merma.presentacion = datos.presentacion;
                         this.merma.unidades = '';
                         this.merma.cajas = 0;
                         this.merma.sueltas = 0;
                         this.merma.abierto = true;
                     },
                     abrirAnular(datos) {
                         this.anular.folio = datos.folio;
                         this.anular.accion = datos.accion;
                         this.anular.abierto = true;
                     },
                     formatoUnidades(unidades, etiqueta) {
                         if (this.merma.porCaja <= 0) {
                             return unidades + ' uds.';
                         }

                         const cajas = Math.floor(unidades / this.merma.porCaja);
                         const sueltas = unidades % this.merma.porCaja;
                         const partes = [];

                         if (cajas > 0) {
                             partes.push(cajas + ' ' + (cajas === 1 ? etiqueta : etiqueta + 's'));
                         }

                         if (sueltas > 0) {
                             partes.push(sueltas + ' ' + (sueltas === 1 ? 'suelta' : 'sueltas'));
                         }

                         return partes.length
                             ? partes.join(' · ') + ' (' + unidades + ' uds.)'
                             : unidades + ' uds.';
                     },
                     get existenciaTexto() {
                         return this.formatoUnidades(this.merma.restante, this.merma.presentacion);
                     },
                     get unidadesAMermear() {
                         if (this.merma.porCaja > 0) {
                             return (Number(this.merma.cajas) || 0) * this.merma.porCaja + (Number(this.merma.sueltas) || 0);
                         }

                         return Number(this.merma.unidades) || 0;
                     },
                     get mermaTexto() {
                         return this.formatoUnidades(this.unidadesAMermear, this.merma.presentacion);
                     },
                     get maxCajas() {
                         return this.merma.porCaja > 0 ? Math.floor(this.merma.restante / this.merma.porCaja) : 0;
                     },
                     get maxSueltas() {
                         return this.merma.porCaja > 0 ? this.merma.restante % this.merma.porCaja : 0;
                     },
                 }"
                 x-effect="filtrar($el)"
                 @keydown.escape.window="merma.abierto = false; anular.abierto = false">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Listado de lotes</h2>
                        <p class="theme-subtle text-sm">{{ $totalLotes }} lote(s) · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    </div>
                </header>
                <div class="p-5 sm:p-6">
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
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                            {{ $lote['cantidad_texto']['unidad'] }}
                                            @if($lote['cantidad_texto']['base'] !== null)
                                                <span class="theme-subtle text-xs">({{ $lote['cantidad_texto']['base'] }})</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                                            @if($lote['restante_texto'] === null)
                                                —
                                            @else
                                                {{ $lote['restante_texto']['unidad'] }}
                                                @if($lote['restante_texto']['base'] !== null)
                                                    <span class="theme-subtle text-xs">({{ $lote['restante_texto']['base'] }})</span>
                                                @endif
                                            @endif
                                        </td>
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
                                                        data-por-caja="{{ $lote['por_caja'] }}"
                                                        data-presentacion="{{ $lote['presentacion'] }}"
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
                                    existencia <span class="font-semibold" x-text="existenciaTexto"></span>
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
                            <input type="hidden" name="por_caja" :value="merma.porCaja">
                            <input type="hidden" name="presentacion" :value="merma.presentacion">
                            <input type="hidden" name="sucursal" value="{{ $selectedSucursal?->id }}">

                            <div class="space-y-4">
                                <div>
                                    <div x-show="merma.porCaja > 0" class="space-y-3">
                                        <div>
                                            <label class="mb-2 block text-sm font-medium">Cajas a dar de baja</label>
                                            <input
                                                type="number"
                                                name="cantidad_cajas"
                                                min="0"
                                                :max="maxCajas"
                                                x-model.number="merma.cajas"
                                                :disabled="merma.porCaja === 0"
                                                value="{{ old('cantidad_cajas', 0) }}"
                                                class="theme-input"
                                            >
                                            <p class="mt-1 text-xs theme-subtle">
                                                Máximo <span x-text="maxCajas"></span> cajas · existencia
                                                <span x-text="existenciaTexto"></span>
                                            </p>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-medium">Unidades sueltas</label>
                                            <input
                                                type="number"
                                                name="cantidad_sueltas"
                                                min="0"
                                                :max="maxSueltas"
                                                x-model.number="merma.sueltas"
                                                :disabled="merma.porCaja === 0"
                                                value="{{ old('cantidad_sueltas', 0) }}"
                                                class="theme-input"
                                            >
                                            <p class="mt-1 text-xs theme-subtle">
                                                Máximo <span x-text="maxSueltas"></span> sueltas (resto de la última caja).
                                            </p>
                                        </div>
                                    </div>

                                    <div x-show="merma.porCaja === 0">
                                        <label class="mb-2 block text-sm font-medium">Cantidad a dar de baja (uds.)</label>
                                        <input
                                            type="number"
                                            name="cantidad"
                                            min="1"
                                            :max="merma.restante"
                                            x-model.number="merma.unidades"
                                            :disabled="merma.porCaja > 0"
                                            :required="merma.porCaja === 0"
                                            value="{{ old('cantidad') }}"
                                            class="theme-input"
                                        >
                                    </div>

                                    <p class="mt-3 text-sm theme-subtle" x-show="unidadesAMermear > 0">
                                        Se dará de baja <span class="font-semibold" x-text="mermaTexto"></span>.
                                    </p>

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
            </section>
        </div>
    </div>

    @include('partials.buscador-cliente')
</x-layouts::app>
