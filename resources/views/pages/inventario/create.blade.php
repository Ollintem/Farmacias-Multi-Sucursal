@php
    $producto = $producto ?? null;
    $esEdicion = $producto !== null;
    $valorVolver = route('inventario.productos', ['sucursal' => $selectedSucursalId]);
@endphp

<x-layouts::app :title="$esEdicion ? __('Editar producto') : __('Nuevo producto')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado homologado: hero verde --}}
            <section class="module-card noti-hero" aria-label="Encabezado de producto">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Catálogo · {{ $esEdicion ? 'Edición' : 'Nuevo registro' }}</p>
                    <h1 class="mt-2">{{ $esEdicion ? 'Editar producto' : 'Agregar producto' }}</h1>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ $valorVolver }}" class="theme-button theme-button-secondary">← Volver</a>
                    </div>
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

            @php
                $faltanCatalogos = $sucursales->isEmpty() || $presentaciones->isEmpty() || $categorias->isEmpty();

                if ($esEdicion) {
                    $filasBase = $producto->presentacionesPrecio
                        ->map(fn ($presentacion) => [
                            'id_presentacion' => $presentacion->id_presentacion,
                            'unidades' => $presentacion->unidades,
                            'precio' => $presentacion->precio_presentacion,
                            'es_principal' => (int) $presentacion->es_principal,
                            'error' => '',
                        ])
                        ->values()
                        ->all();
                } else {
                    $filasBase = [[
                        'id_presentacion' => $presentacionCaja?->id ?? '',
                        'unidades' => '',
                        'precio' => '',
                        'es_principal' => 1,
                        'error' => '',
                    ]];
                }

                $filasOld = old('presentaciones');
                $filasIniciales = is_array($filasOld) && $filasOld !== [] ? $filasOld : $filasBase;
                $filasIniciales = collect($filasIniciales)
                    ->map(fn ($fila) => array_merge(['id_presentacion' => '', 'unidades' => '', 'precio' => '', 'es_principal' => 0, 'error' => ''], is_array($fila) ? $fila : []))
                    ->values()
                    ->all();

                $presentacionUnidad = $presentaciones->first(
                    fn ($presentacion) => mb_strtolower((string) $presentacion->presentacion) === 'unidad'
                );

                // Se resalta la presentación marcada internamente como
                // principal. En altas nuevas la caja viene marcada por
                // defecto y, si no hay marca, se usa la caja como respaldo.
                $indicePrincipalInicial = null;

                foreach ($filasIniciales as $indice => $fila) {
                    if (! empty($fila['es_principal'])) {
                        $indicePrincipalInicial = $indice;
                        break;
                    }
                }

                if ($indicePrincipalInicial === null && $presentacionCaja !== null) {
                    foreach ($filasIniciales as $indice => $fila) {
                        if ((string) $fila['id_presentacion'] === (string) $presentacionCaja->id) {
                            $indicePrincipalInicial = $indice;
                            break;
                        }
                    }
                }

                $indicePrincipalInicial ??= 0;

                $soloUnidadInicial = $presentacionUnidad !== null
                    && count($filasIniciales) === 1
                    && (string) $filasIniciales[0]['id_presentacion'] === (string) $presentacionUnidad->id;
            @endphp

            @if($faltanCatalogos)
                <div class="rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm text-amber-600 dark:text-amber-400">
                    <p class="font-bold">Faltan catálogos para registrar productos:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @if($sucursales->isEmpty())
                            <li>No hay sucursales registradas.</li>
                        @endif
                        @if($categorias->isEmpty())
                            <li>No hay categorías registradas.</li>
                        @endif
                        @if($presentaciones->isEmpty())
                            <li>No hay presentaciones registradas. Es obligatoria al menos una presentación principal: créala desde «+ Agregar nueva presentación» y vuelve a cargar la p&aacute;gina.</li>
                        @endif
                    </ul>
                </div>
            @endif

            @php
                $erroresPresentaciones = collect($errors->getMessages())
                    ->filter(fn ($msgs, $key) => str_starts_with($key, 'presentaciones'))
                    ->flatten();
            @endphp

            @if($erroresPresentaciones->isNotEmpty())
                <div class="rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-600 dark:text-red-400">
                    <ul class="list-disc pl-5">
                        @foreach($erroresPresentaciones as $mensajeError)
                            <li>{{ $mensajeError }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="module-card noti-listado" aria-label="Datos del producto">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Datos del producto</h2>
                        <p class="theme-subtle text-sm">{{ $esEdicion ? 'Actualice la información del producto' : 'Complete la información del nuevo producto' }}</p>
                    </div>
                </header>
                <div class="p-5 sm:p-6">
            <form
                action="{{ $esEdicion ? route('inventario.update', $producto) : route('inventario.store') }}"
                method="POST"
                x-data="formularioProducto()"
                x-ref="formulario"
                x-on:submit.prevent="validarFormulario()"
                x-on:keydown.escape.window="dropdownAbierto = null"
                x-on:click.capture.window="if (dropdownAbierto !== null && !$event.target.closest?.('[data-dropdown]')) { dropdownAbierto = null; }"
                x-effect="seguirDesplegable()"
            >
                @csrf
                @if($esEdicion)
                    @method('PUT')
                @endif

                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Escaneo</p>
                            <p class="mt-1 text-sm text-slate-700 dark:text-slate-300">Puedes usar la cámara de la PC, un lector USB o escribirlo manualmente.</p>
                        </div>
                        <button type="button" id="scanner-focus-button" class="theme-button theme-button-secondary whitespace-nowrap">Escanear con cámara</button>
                    </div>
                </div>

                <div class="module-table">
                    <div class="p-5">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Datos del producto</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">{{ $esEdicion ? 'Actualiza la informacion de este producto.' : 'Completa la informacion para registrar un nuevo producto.' }}</p>

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium">Código de barras</label>
                                <input id="barcode-input" name="codigo_barras" value="{{ old('codigo_barras', $producto?->codigo_barras) }}" class="theme-input" placeholder="Ej. 7501234567890" autocomplete="off" autocorrect="off" spellcheck="false" required>
                                @error('codigo_barras')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Nombre del producto</label>
                                <input name="nombre_producto" value="{{ old('nombre_producto', $producto?->nombre_producto) }}" class="theme-input" placeholder="Ej. Paracetamol 500mg" required>
                                @error('nombre_producto')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Categoría</label>
                                <div class="relative" data-dropdown="categoria">
                                    <input type="hidden" name="id_categoria" :value="categoriaSeleccionada">

                                    <button
                                        type="button"
                                        class="theme-input flex cursor-pointer items-center justify-between gap-2 text-left"
                                        x-on:click="dropdownAbierto = dropdownAbierto === 'categoria' ? null : 'categoria'"
                                        x-bind:aria-expanded="dropdownAbierto === 'categoria'"
                                        aria-haspopup="listbox"
                                    >
                                        <span x-bind:class="categoriaSeleccionada === '' ? 'opacity-60' : 'font-semibold'" x-text="nombreCategoria"></span>
                                        <svg
                                            class="h-4 w-4 shrink-0 text-slate-400 transition-transform"
                                            x-bind:class="dropdownAbierto === 'categoria' && 'rotate-180'"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="dropdownAbierto === 'categoria'" x-cloak x-transition.opacity class="fixed z-20 rounded-xl bg-white p-2 shadow-xl dark:border dark:border-zinc-700 dark:bg-zinc-900">
                                        <div class="farma-pick-scroll" role="listbox" aria-label="Categorías">
                                            <template x-for="categoria in categorias" :key="categoria.id">
                                                <button
                                                    type="button"
                                                    role="option"
                                                    x-on:click="seleccionarCategoria(categoria.id)"
                                                    x-bind:aria-selected="String(categoriaSeleccionada) === String(categoria.id)"
                                                    x-bind:class="String(categoriaSeleccionada) === String(categoria.id) ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/25 dark:border-[#6ee7b7] dark:from-[#34d399] dark:to-[#0e9384] dark:text-[#052e2b]' : 'border-slate-300 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-[#0c9f9c] dark:hover:bg-emerald-500/15 dark:hover:text-emerald-100'"
                                                    class="flex min-h-11 w-full cursor-pointer items-center justify-between gap-2 rounded-xl border-[1.5px] px-3 py-2 text-left text-sm font-semibold transition active:scale-[0.99]"
                                                >
                                                    <span class="truncate" x-text="categoria.nombre"></span>
                                                    <svg
                                                        x-show="String(categoriaSeleccionada) === String(categoria.id)"
                                                        x-cloak
                                                        class="h-4 w-4 shrink-0"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            </template>
                                        </div>
                                        <button
                                            type="button"
                                            x-on:click="abrirModal('categoria')"
                                            class="mt-2 flex min-h-11 w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl border-[1.5px] border-dashed border-[#0e9384] px-3 py-2 text-sm font-bold text-[#0b6e68] transition hover:bg-emerald-50 active:scale-[0.99] dark:text-emerald-300 dark:hover:bg-emerald-500/10"
                                        >
                                            <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-[#0e9384] text-xs font-bold leading-none text-white">+</span>
                                            Agregar nueva categoría
                                        </button>
                                    </div>
                                </div>
                                @error('id_categoria')
                                    <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                @enderror
                                <span x-show="errorCategoria !== ''" x-text="errorCategoria" x-cloak class="mt-1 block text-xs text-red-500"></span>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium">Descripción <span class="opacity-50 font-normal">(opcional)</span></label>
                                <textarea name="descripcion" rows="2" class="theme-input" placeholder="Ej. agrega una breve descripción de este producto">{{ old('descripcion', $producto?->descripcion) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 p-5 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Presentaciones</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Define las presentaciones del producto con sus unidades y precio.</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <button
                                    type="button"
                                    x-on:click="confirmarQuitarPresentaciones = true"
                                    x-bind:disabled="!presentacionUnidadId"
                                    x-bind:aria-disabled="!presentacionUnidadId"
                                    class="theme-button theme-button-secondary"
                                >
                                    Quitar presentaciones
                                </button>
                                <button
                                    type="button"
                                    x-on:click="agregarFila()"
                                    class="theme-button theme-button-secondary"
                                >
                                    + Agregar presentación
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-col gap-4">
                            <template x-for="(fila, indice) in filas" :key="indice">
                                <div
                                    class="rounded-xl border p-4 transition-colors"
                                    x-bind:class="principalIndex === indice
                                        ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-500/40 dark:border-emerald-400 dark:bg-emerald-500/10 dark:ring-emerald-400/40'
                                        : 'border-slate-200 dark:border-zinc-700'"
                                >
                                    <div class="grid gap-4 md:grid-cols-3">
                                        <div>
                                            <label class="mb-2 block text-sm font-medium">Presentación</label>

                                            <input
                                                type="hidden"
                                                :name="'presentaciones[' + indice + '][es_principal]'"
                                                :value="principalIndex === indice ? 1 : 0"
                                            >

                                            <div class="relative" :data-dropdown="'presentacion-' + indice">
                                                <input
                                                    type="hidden"
                                                    :name="'presentaciones[' + indice + '][id_presentacion]'"
                                                    :value="fila.id_presentacion"
                                                >

                                                <button
                                                    type="button"
                                                    class="theme-input flex items-center justify-between gap-2 text-left"
                                                    x-bind:class="bloquearPresentacion(indice) ? 'cursor-not-allowed opacity-70' : 'cursor-pointer'"
                                                    x-bind:disabled="bloquearPresentacion(indice)"
                                                    x-on:click="dropdownAbierto = dropdownAbierto === ('presentacion-' + indice) ? null : ('presentacion-' + indice)"
                                                    x-bind:aria-expanded="dropdownAbierto === ('presentacion-' + indice)"
                                                    x-bind:aria-disabled="bloquearPresentacion(indice)"
                                                    aria-haspopup="listbox"
                                                >
                                                    <span x-bind:class="fila.id_presentacion === '' ? 'opacity-60' : 'font-semibold'" x-text="nombrePresentacion(fila.id_presentacion)"></span>
                                                    <svg
                                                        class="h-4 w-4 shrink-0 text-slate-400 transition-transform"
                                                        x-bind:class="dropdownAbierto === ('presentacion-' + indice) && 'rotate-180'"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                        aria-hidden="true"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>

                                                <div x-show="dropdownAbierto === ('presentacion-' + indice)" x-cloak x-transition.opacity class="fixed z-20 rounded-xl bg-white p-2 shadow-xl dark:border dark:border-zinc-700 dark:bg-zinc-900">
                                                    <div class="farma-pick-scroll" role="listbox" aria-label="Presentaciones">
                                                        <template x-for="presentacion in catalogoPresentaciones" :key="presentacion.id">
                                                            <button
                                                                type="button"
                                                                role="option"
                                                                x-on:click="seleccionarPresentacion(indice, presentacion.id)"
                                                                x-bind:aria-selected="String(fila.id_presentacion) === String(presentacion.id)"
                                                                x-bind:class="String(fila.id_presentacion) === String(presentacion.id) ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/25 dark:border-[#6ee7b7] dark:from-[#34d399] dark:to-[#0e9384] dark:text-[#052e2b]' : 'border-slate-300 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-[#0c9f9c] dark:hover:bg-emerald-500/15 dark:hover:text-emerald-100'"
                                                                class="flex min-h-11 w-full cursor-pointer items-center justify-between gap-2 rounded-xl border-[1.5px] px-3 py-2 text-left text-sm font-semibold transition active:scale-[0.99]"
                                                            >
                                                                <span class="truncate" x-text="presentacion.presentacion"></span>
                                                                <svg
                                                                    x-show="String(fila.id_presentacion) === String(presentacion.id)"
                                                                    x-cloak
                                                                    class="h-4 w-4 shrink-0"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    viewBox="0 0 24 24"
                                                                >
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                            </button>
                                                        </template>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        x-on:click="abrirModal('presentacion', indice)"
                                                        class="mt-2 flex min-h-11 w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl border-[1.5px] border-dashed border-[#0e9384] px-3 py-2 text-sm font-bold text-[#0b6e68] transition hover:bg-emerald-50 active:scale-[0.99] dark:text-emerald-300 dark:hover:bg-emerald-500/10"
                                                    >
                                                        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-[#0e9384] text-xs font-bold leading-none text-white">+</span>
                                                        Agregar nueva presentación
                                                    </button>
                                                </div>
                                            </div>

                                            <span
                                                x-show="fila.error"
                                                x-text="fila.error"
                                                x-cloak
                                                class="mt-1 block text-xs text-red-500"
                                            ></span>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-medium">Unidades en esta presentación <span class="text-red-500">*</span></label>
                                            <input
                                                type="number"
                                                min="1"
                                                :readonly="unidadesBloqueadas(indice)"
                                                :name="'presentaciones[' + indice + '][unidades]'"
                                                x-model="fila.unidades"
                                                class="theme-input"
                                                x-bind:class="unidadesBloqueadas(indice) ? 'cursor-not-allowed opacity-70' : ''"
                                                placeholder="Ej. 10"
                                                required
                                            >
                                            <span x-show="esUnidad(fila.id_presentacion)" x-cloak class="mt-1 block text-xs text-slate-400">La unidad siempre vale 1.</span>
                                            <span x-show="presentacionesQuitadas" x-cloak class="mt-1 block text-xs text-slate-400">Las unidades quedan bloqueadas al quitar las presentaciones.</span>
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-sm font-medium">Precio de esta presentación <span class="text-red-500">*</span></label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                :name="'presentaciones[' + indice + '][precio]'"
                                                x-model="fila.precio"
                                                class="theme-input"
                                                placeholder="0.00"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <div class="mt-3 flex justify-end">
                                        <button
                                            type="button"
                                            x-show="filas.length > 1 && !esFilaPrincipal(indice)"
                                            x-on:click="quitarFila(indice)"
                                            class="text-xs font-medium text-red-500 transition hover:text-red-600"
                                        >
                                            Eliminar presentación
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4 rounded-xl border border-sky-500/25 bg-sky-500/10 px-4 py-3 text-sm text-sky-700 dark:text-sky-300">
                            La venta y distribución del producto se realizará según la presentación seleccionada.
                        </div>
                    </div>

                    <div class="border-t border-slate-200 p-5 dark:border-zinc-700">
                        <p class="text-sm text-slate-500 dark:text-zinc-400">Marca esta opción si el producto requiere receta o control especial.</p>

                        <label class="mt-4 inline-flex items-center gap-3 text-sm font-medium">
                            <input type="checkbox" name="es_controlado" value="1" {{ old('es_controlado', $producto?->es_controlado ?? false) ? 'checked' : '' }}>
                            Producto controlado
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ $valorVolver }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button
                        type="submit"
                        class="theme-button theme-button-primary"
                        x-bind:disabled="filas.length === 0"
                        x-bind:aria-disabled="filas.length === 0"
                    >{{ $esEdicion ? 'Guardar cambios' : 'Guardar producto' }}</button>
                </div>

                {{-- Modal: confirmar quitar todas las presentaciones --}}
                <div
                    x-show="confirmarQuitarPresentaciones"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="display:none;"
                    x-cloak
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="confirmarQuitarPresentaciones = false"></div>

                    <div
                        class="relative w-full max-w-md rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-2xl backdrop-blur-sm dark:border-[#1e3a5f]/60 dark:bg-[#0b1322]/95"
                        x-on:click.stop
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-lg font-bold">Quitar presentaciones</h2>
                            <button type="button" x-on:click="confirmarQuitarPresentaciones = false" class="opacity-50 transition hover:opacity-100">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Al quitar las presentaciones, la venta y distribución de este producto será por unidades. La presentación principal quedará como «Unidad».
                        </p>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" x-on:click="confirmarQuitarPresentaciones = false" class="theme-button theme-button-secondary">Cancelar</button>
                            <button type="button" x-on:click="quitarTodasPresentaciones()" class="theme-button theme-button-primary">Sí, quitar</button>
                        </div>
                    </div>
                </div>

                {{-- Modal: advertencia al agregar presentaciones después de quitarlas --}}
                <div
                    x-show="confirmarAgregarPresentacion"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="display:none;"
                    x-cloak
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="confirmarAgregarPresentacion = false"></div>

                    <div
                        class="relative w-full max-w-md rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-2xl backdrop-blur-sm dark:border-[#1e3a5f]/60 dark:bg-[#0b1322]/95"
                        x-on:click.stop
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-lg font-bold">Advertencia</h2>
                            <button type="button" x-on:click="confirmarAgregarPresentacion = false" class="opacity-50 transition hover:opacity-100">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Al agregar un presentación la unidad se dejará de distribuir por unidades individuales. Lo hará por cajas y otras presentaciones derivadas
                        </p>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" x-on:click="confirmarAgregarPresentacion = false" class="theme-button theme-button-secondary">Cancelar</button>
                            <button type="button" x-on:click="continuarAgregarPresentacion()" class="theme-button theme-button-primary">Continuar</button>
                        </div>
                    </div>
                </div>

                {{-- Modal: nueva categoría / nueva presentación --}}
                <div
                    x-show="modalAbierto"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="display:none;"
                    x-cloak
                >
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" x-on:click="cerrarModal()"></div>

                    <div
                        class="relative w-full max-w-lg rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-2xl backdrop-blur-sm dark:border-[#1e3a5f]/60 dark:bg-[#0b1322]/95"
                        x-on:click.stop
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-lg font-bold" x-text="modalModo === 'categoria' ? 'Nueva categoría' : 'Nueva presentación'"></h2>
                            <button type="button" x-on:click="cerrarModal()" class="opacity-50 transition hover:opacity-100">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium" x-text="modalModo === 'categoria' ? 'Nombre de la categoría' : 'Nombre de la presentación'"></label>
                            <input
                                x-model="modalNombre"
                                x-ref="modalInput"
                                x-on:keydown.enter.prevent="guardarModal()"
                                class="theme-input"
                                maxlength="60"
                                placeholder="Ej. Dermatológicos"
                            >
                            <span x-show="modalError" x-text="modalError" x-cloak class="mt-1 block text-xs text-red-500"></span>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" x-on:click="cerrarModal()" class="theme-button theme-button-secondary">Cancelar</button>
                            <button
                                type="button"
                                x-on:click="guardarModal()"
                                x-bind:disabled="guardando || modalNombre.trim() === ''"
                                class="theme-button theme-button-primary"
                                x-text="guardando ? 'Guardando...' : 'Guardar'"
                            ></button>
                        </div>
                    </div>
                </div>
            </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        function formularioProducto() {
            return {
                categoriaSeleccionada: @json((string) old('id_categoria', $producto?->id_categoria ?? '')),
                dropdownAbierto: null,
                errorCategoria: '',
                categorias: @json($categorias->map(fn ($categoria) => ['id' => $categoria->id, 'nombre' => $categoria->nombre])->values(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
                filas: @json($filasIniciales),
                principalIndex: {{ (int) $indicePrincipalInicial }},
                presentacionCajaId: @json($presentacionCaja?->id),
                presentacionUnidadId: @json($presentacionUnidad?->id),
                catalogoPresentaciones: @json($presentaciones->map(fn ($p) => ['id' => $p->id, 'presentacion' => $p->presentacion])->values()),
                modalAbierto: false,
                modalModo: 'categoria',
                modalNombre: '',
                modalError: '',
                guardando: false,
                indiceFilaOrigen: null,
                confirmarQuitarPresentaciones: false,
                confirmarAgregarPresentacion: false,
                presentacionesQuitadas: @json($soloUnidadInicial),

                agregarFila() {
                    if (this.presentacionesQuitadas) {
                        this.confirmarAgregarPresentacion = true;
                        this.dropdownAbierto = null;

                        return;
                    }

                    this.filas.push({ id_presentacion: '', unidades: '', precio: '', es_principal: 0, error: '' });
                },

                continuarAgregarPresentacion() {
                    const precioUnidad = this.filas[0] ? this.filas[0].precio : '';

                    this.confirmarAgregarPresentacion = false;
                    this.presentacionesQuitadas = false;
                    this.filas = [{
                        id_presentacion: this.presentacionCajaId ? String(this.presentacionCajaId) : '',
                        unidades: '',
                        precio: precioUnidad,
                        es_principal: 1,
                        error: '',
                    }];
                    this.principalIndex = 0;
                    this.dropdownAbierto = null;
                    this.filas.push({ id_presentacion: '', unidades: '', precio: '', es_principal: 0, error: '' });
                },

                quitarFila(indice) {
                    if (this.esFilaPrincipal(indice)) {
                        return;
                    }

                    this.filas.splice(indice, 1);
                    this.dropdownAbierto = null;

                    if (this.principalIndex > indice) {
                        this.principalIndex--;
                    }
                },

                esFilaPrincipal(indice) {
                    return this.principalIndex === indice;
                },

                bloquearPresentacion(indice) {
                    const fila = this.filas[indice];

                    if (!fila) {
                        return false;
                    }

                    if (this.presentacionesQuitadas) {
                        return true;
                    }

                    return this.esFilaPrincipal(indice) && fila.id_presentacion !== '' && fila.id_presentacion !== null;
                },

                unidadesBloqueadas(indice) {
                    const fila = this.filas[indice];

                    return this.presentacionesQuitadas || this.esUnidad(fila ? fila.id_presentacion : '');
                },

                quitarTodasPresentaciones() {
                    const filaUnidad = this.filas.find(
                        (fila) => String(fila.id_presentacion) === String(this.presentacionUnidadId)
                    );

                    this.filas = [{
                        id_presentacion: this.presentacionUnidadId ? String(this.presentacionUnidadId) : '',
                        unidades: 1,
                        precio: filaUnidad ? filaUnidad.precio : (this.filas[0] ? this.filas[0].precio : ''),
                        es_principal: 1,
                        error: '',
                    }];
                    this.principalIndex = 0;
                    this.dropdownAbierto = null;
                    this.presentacionesQuitadas = true;
                    this.confirmarQuitarPresentaciones = false;
                },

                seguirDesplegable() {
                    const raiz = this.$refs.formulario;
                    const id = this.dropdownAbierto;

                    if (raiz._seguidor) {
                        window.removeEventListener('scroll', raiz._seguidor, { capture: true });
                        window.removeEventListener('resize', raiz._seguidor);
                        raiz._seguidor = null;
                    }

                    if (id === null) {
                        return;
                    }

                    this.$nextTick(() => {
                        if (this.dropdownAbierto !== id) {
                            return;
                        }

                        if (raiz._seguidor) {
                            window.removeEventListener('scroll', raiz._seguidor, { capture: true });
                            window.removeEventListener('resize', raiz._seguidor);
                        }

                        this.ubicarDesplegable(id);
                        raiz._seguidor = () => this.ubicarDesplegable(id);
                        window.addEventListener('scroll', raiz._seguidor, { passive: true, capture: true });
                        window.addEventListener('resize', raiz._seguidor);
                    });
                },

                ubicarDesplegable(id) {
                    const contenedor = this.$refs.formulario.querySelector('[data-dropdown="' + id + '"]');

                    if (!contenedor) {
                        return;
                    }

                    const gatillo = contenedor.querySelector('button');
                    const panel = contenedor.lastElementChild;

                    if (!gatillo || !panel) {
                        return;
                    }

                    const rect = gatillo.getBoundingClientRect();
                    const alto = panel.offsetHeight;

                    panel.style.left = rect.left + 'px';
                    panel.style.width = rect.width + 'px';
                    panel.style.top = (window.innerHeight - rect.bottom < alto + 8 && rect.top > alto + 8)
                        ? (rect.top - 8 - alto) + 'px'
                        : (rect.bottom + 8) + 'px';
                },

                get nombreCategoria() {
                    const categoria = this.categorias.find(
                        (item) => String(item.id) === String(this.categoriaSeleccionada)
                    );

                    return categoria ? categoria.nombre : 'Selecciona una categoría';
                },

                nombrePresentacion(valor) {
                    if (valor === '' || valor === null || valor === undefined) {
                        return 'Selecciona una presentación';
                    }

                    const presentacion = this.catalogoPresentaciones.find((item) => String(item.id) === String(valor));

                    return presentacion ? presentacion.presentacion : 'Selecciona una presentación';
                },

                seleccionarCategoria(valor) {
                    this.dropdownAbierto = null;
                    this.categoriaSeleccionada = String(valor);
                    this.errorCategoria = '';
                },

                seleccionarPresentacion(indice, valor) {
                    this.dropdownAbierto = null;
                    this.filas[indice].id_presentacion = String(valor);

                    if (this.esUnidad(valor)) {
                        this.filas[indice].unidades = 1;
                    } else if (Number(this.filas[indice].unidades) <= 1) {
                        this.filas[indice].unidades = '';
                    }

                    this.validarDuplicado(indice, String(valor));
                },

                esUnidad(valor) {
                    const presentacion = this.catalogoPresentaciones.find((item) => String(item.id) === String(valor));

                    return presentacion ? presentacion.presentacion.toLowerCase() === 'unidad' : false;
                },

                validarFormulario() {
                    this.errorCategoria = this.categoriaSeleccionada === '' ? 'Selecciona una categoría' : '';

                    let faltanPresentaciones = false;
                    this.filas.forEach((fila) => {
                        if (fila.id_presentacion === '') {
                            fila.error = 'Selecciona una presentación';
                            faltanPresentaciones = true;
                        }
                    });

                    if (this.errorCategoria !== '' || faltanPresentaciones) {
                        return;
                    }

                    this.$refs.formulario.submit();
                },

                validarDuplicado(indice, valor) {
                    const fila = this.filas[indice];
                    fila.error = '';

                    if (valor === '') {
                        return;
                    }

                    const duplicada = this.filas.some((otra, i) =>
                        i !== indice && otra.id_presentacion !== '' && String(otra.id_presentacion) === String(valor)
                    );

                    if (duplicada) {
                        fila.error = 'Esta presentación ya está elegida';
                        this.$nextTick(() => {
                            fila.id_presentacion = '';
                        });
                    }
                },

                abrirModal(modo, indice = null) {
                    this.modalModo = modo;
                    this.indiceFilaOrigen = indice;
                    this.modalNombre = '';
                    this.modalError = '';
                    this.modalAbierto = true;
                    this.$nextTick(() => this.$refs.modalInput?.focus());
                },

                cerrarModal() {
                    this.modalAbierto = false;
                    this.modalError = '';
                    this.indiceFilaOrigen = null;
                },

                async guardarModal() {
                    if (this.guardando || this.modalNombre.trim() === '') {
                        return;
                    }

                    this.guardando = true;
                    this.modalError = '';

                    const esCategoria = this.modalModo === 'categoria';
                    const url = esCategoria ? '{{ route('categorias.store') }}' : '{{ route('presentaciones.store') }}';
                    const campo = esCategoria ? 'nombre' : 'presentacion';

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            },
                            body: JSON.stringify({ [campo]: this.modalNombre.trim() }),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            const mensajes = payload.errors ? Object.values(payload.errors).flat() : ['No se pudo guardar.'];
                            this.modalError = mensajes[0];
                            return;
                        }

                        if (esCategoria) {
                            this.categorias.push({ id: payload.id, nombre: payload.nombre });
                            this.categoriaSeleccionada = String(payload.id);
                            this.errorCategoria = '';
                        } else {
                            this.catalogoPresentaciones.push({ id: payload.id, presentacion: payload.presentacion });

                            if (this.indiceFilaOrigen !== null && this.filas[this.indiceFilaOrigen]) {
                                this.filas[this.indiceFilaOrigen].id_presentacion = String(payload.id);
                                this.filas[this.indiceFilaOrigen].error = '';

                                if (String(payload.presentacion).toLowerCase() === 'unidad') {
                                    this.filas[this.indiceFilaOrigen].unidades = 1;
                                }
                            }
                        }

                        this.cerrarModal();
                    } catch (e) {
                        this.modalError = 'Error de conexión. Intenta de nuevo.';
                    } finally {
                        this.guardando = false;
                    }
                },
            };
        }
    </script>
</x-layouts::app>
