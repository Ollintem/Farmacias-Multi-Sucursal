<x-layouts::app :title="__('Lotes y caducidades')">
    <div class="module-page inventario-page" style="min-height:auto !important; display:block !important; padding:0.75rem;">
        <div class="module-page-inner inventario-inner" style="display:block !important; flex:none !important; max-width:none; margin:0; width:100%; gap:0.75rem !important;">
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

            {{-- Encabezado limpio --}}
            <header class="mb-6" aria-label="Encabezado de lotes y caducidades">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Trazabilidad · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Lotes y caducidades</h1>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
  <dl class="grid grid-cols-2 gap-px bg-slate-200 dark:bg-zinc-800 lg:grid-cols-4">
    <a class="relative flex items-center gap-2.5 bg-white px-3 py-2.5 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60" href="{{ $enlaceTarjeta('todos') }}" @if($filtroEstado === 'todos') aria-current="true" @endif>
    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600 ring-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Total de lotes</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $totalLotes }}</p>
        </div><span class="ml-auto grid h-5 w-5 place-items-center rounded-full bg-slate-50 text-slate-400 opacity-60 transition group-hover:opacity-100 dark:bg-zinc-800 dark:text-zinc-500"><svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></span>
</a>
    <a class="relative flex items-center gap-2.5 bg-white px-3 py-2.5 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60" href="{{ $enlaceTarjeta('vigentes') }}" @if($filtroEstado === 'vigentes') aria-current="true" @endif>
    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/20 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Vigentes</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $vigentes }}</p>
        </div><span class="ml-auto grid h-5 w-5 place-items-center rounded-full bg-slate-50 text-slate-400 opacity-60 transition group-hover:opacity-100 dark:bg-zinc-800 dark:text-zinc-500"><svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></span>
</a>
    <a href="{{ $enlaceTarjeta('por-caducar') }}" @class(['relative flex items-center gap-2.5 bg-white px-3 py-2.5 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60', '!border-amber-200 !bg-amber-50 dark:!border-amber-500/30 dark:!bg-amber-500/10' => $porCaducar > 0]) @if($filtroEstado === 'por-caducar') aria-current="true" @endif>
    <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-amber-500"></span><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/20 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Por caducar</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $porCaducar }}</p>
        </div><span class="ml-auto grid h-5 w-5 place-items-center rounded-full bg-slate-50 text-slate-400 opacity-60 transition group-hover:opacity-100 dark:bg-zinc-800 dark:text-zinc-500"><svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></span>
</a>
    <a href="{{ $enlaceTarjeta('caducados') }}" @class(['relative flex items-center gap-2.5 bg-white px-3 py-2.5 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60', '!border-amber-200 !bg-amber-50 dark:!border-amber-500/30 dark:!bg-amber-500/10' => $caducados > 0]) @if($filtroEstado === 'caducados') aria-current="true" @endif>
    <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-amber-500"></span><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/20 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Caducados</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $caducados }}</p>
        </div><span class="ml-auto grid h-5 w-5 place-items-center rounded-full bg-slate-50 text-slate-400 opacity-60 transition group-hover:opacity-100 dark:bg-zinc-800 dark:text-zinc-500"><svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></span>
</a>
  </dl>
</div>
                </div>
            </header>

            <div class="lg:hidden">
                @include('pages.inventario.tabs', ['seccion' => 'lotes', 'selectedSucursal' => $selectedSucursal])
            </div>

            <section class="module-card noti-listado inventario-listado !flex-none" style="flex:none !important; display:block !important;" aria-label="Listado de lotes"
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

                {{-- Toolbar: picker + buscador alineados --}}
                <div class="entradas-toolbar border-b border-slate-100 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-900/40">
                    <form method="GET" action="{{ route('lotes.index') }}" class="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        @if($filtroEstado !== 'todos')
                            <input type="hidden" name="estado" value="{{ $filtroEstado }}">
                        @endif
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            <span class="shrink-0">Sucursal</span>
                            <span class="min-w-[12rem]"><x-option-pick name="sucursal" label="Sucursales" :options="$sucursales->pluck('nombre_sucursal', 'id')" :value="$selectedSucursal?->id" placeholder="Seleccionar sucursal" :autoSubmit="true" /></span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="search" name="buscar" value="{{ old('buscar', $busqueda) }}" placeholder="Buscar folio, proveedor o producto" class="theme-input w-full sm:w-72" aria-label="Buscar lote" @input.debounce.200ms="texto = $event.target.value">
                            <button type="submit" class="theme-button theme-button-secondary shrink-0 whitespace-nowrap !min-h-0 !py-2.5">Buscar</button>
                        </div>
                    </form>
                </div>

                <div class="p-5 sm:p-6">
                <div class="module-table">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Lote</th>
                                    <th class="px-4 py-3 font-semibold">Producto</th>
                                    <th class="px-4 py-3 font-semibold">Proveedor</th>
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
                                                <a href="{{ route('lotes.edit', $lote['id']) }}" title="Editar lote" class="text-slate-500 transition hover:text-emerald-600 dark:text-slate-400">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span class="sr-only">Editar lote</span>
                                                </a>

                                                @if($lote['restante'] !== null && $lote['restante'] > 0)
                                                    <button
                                                        type="button"
                                                        title="Dar de baja"
                                                        class="text-slate-500 transition hover:text-red-600 dark:text-slate-400"
                                                        data-lote="{{ $lote['id'] }}"
                                                        data-folio="{{ $lote['folio'] }}"
                                                        data-restante="{{ $lote['restante'] }}"
                                                        data-por-caja="{{ $lote['por_caja'] }}"
                                                        data-presentacion="{{ $lote['presentacion'] }}"
                                                        x-on:click="abrirMerma($event.currentTarget.dataset)"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        <span class="sr-only">Dar de baja</span>
                                                    </button>
                                                @endif

                                                @if(! $lote['anulado'] && $lote['restante_global'] === 0)
                                                    <button
                                                        type="button"
                                                        title="Anular lote"
                                                        class="text-slate-500 transition hover:text-red-600 dark:text-slate-400"
                                                        data-folio="{{ $lote['folio'] }}"
                                                        data-accion="{{ route('lotes.anular', $lote['id']) }}"
                                                        x-on:click="abrirAnular($event.currentTarget.dataset)"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                        <span class="sr-only">Anular lote</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                    <tr data-vacio @if($lotes->isNotEmpty()) style="display: none" @endif>
                                        <td colspan="9" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
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
