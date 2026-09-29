@php
    $producto = $producto ?? null;
    $esEdicion = $producto !== null;
    $venderPorUnidad = old('vender_por_unidad', $esEdicion && $producto->precio !== null);
    $valorPrecio = old('precio', $esEdicion ? $producto->precio : '');
    $valorVolver = route('inventario.productos', ['sucursal' => $selectedSucursalId]);
@endphp

<x-layouts::app :title="$esEdicion ? __('Editar producto') : __('Nuevo producto')">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Catálogo</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-800 dark:text-white">{{ $esEdicion ? 'Editar producto' : 'Agregar producto' }}</h1>
                </div>

                <div class="theme-tools">
                    <a href="{{ $valorVolver }}" class="theme-button theme-button-secondary">Volver</a>
                </div>
            </div>

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
                $faltanCatalogos = $sucursales->isEmpty() || $presentaciones->isEmpty() || $categorias->isEmpty() || $presentacionCaja === null;
                $filasBase = [['id_presentacion' => $presentacionCaja?->id ?? '', 'unidades' => '', 'precio' => '']];

                if ($esEdicion) {
                    $filasDelProducto = $producto->presentacionesPrecio
                        ->map(fn ($presentacion) => [
                            'id_presentacion' => $presentacion->id_presentacion,
                            'unidades' => $presentacion->unidades,
                            'precio' => $presentacion->precio_presentacion,
                            'error' => '',
                        ])
                        ->sortBy(fn ($fila) => $fila['id_presentacion'] == $presentacionCaja?->id ? 0 : 1)
                        ->values()
                        ->all();

                    if ($filasDelProducto !== []) {
                        $filasBase = $filasDelProducto;
                    }
                }

                $filasOld = old('presentaciones');
                $filasIniciales = is_array($filasOld) && $filasOld !== [] ? $filasOld : $filasBase;
                $filasIniciales = collect($filasIniciales)
                    ->map(fn ($fila) => array_merge(['id_presentacion' => '', 'unidades' => '', 'precio' => '', 'error' => ''], is_array($fila) ? $fila : []))
                    ->values()
                    ->all();

                if ($filasIniciales === []) {
                    $filasIniciales = $filasBase;
                }

                $filasIniciales[0]['id_presentacion'] = $presentacionCaja?->id ?? '';
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
                            <li>No hay presentaciones registradas.</li>
                        @endif
                        @if($presentacionCaja === null)
                            <li>No hay presentación «Caja» registrada. Es obligatoria para control de caja: créala desde «+ Agregar nueva presentación» y vuelve a cargar la p&aacute;gina.</li>
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

            <form
                action="{{ $esEdicion ? route('inventario.update', $producto) : route('inventario.store') }}"
                method="POST"
                x-data="formularioProducto()"
                x-ref="formulario"
                x-on:submit.prevent="validarFormulario()"
                x-on:keydown.escape.window="dropdownAbierto = null"
                x-on:click.capture.window="if (dropdownAbierto !== null && !$event.target.closest?.('[data-dropdown]')) { dropdownAbierto = null; }"
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
                                <div data-dropdown>
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

                                    <div x-show="dropdownAbierto === 'categoria'" x-cloak x-transition>
                                        <div class="farma-pick-scroll mt-2" role="listbox" aria-label="Categorías">
                                            <template x-for="categoria in categorias" :key="categoria.id">
                                                <button
                                                    type="button"
                                                    role="option"
                                                    x-on:click="seleccionarCategoria(categoria.id)"
                                                    x-bind:aria-selected="String(categoriaSeleccionada) === String(categoria.id)"
                                                    x-bind:class="String(categoriaSeleccionada) === String(categoria.id) ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/25' : 'border-slate-300 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-[#0c9f9c]'"
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

                            <div>
                                <label class="mb-2 block text-sm font-medium">Precio unitario</label>
                                <label class="flex items-center gap-3 text-sm">
                                    <input type="checkbox" name="vender_por_unidad" value="1" x-model="venderPorUnidad">
                                    <span>Vender por unidad</span>
                                </label>
                                <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Ej. pastilla o ampolleta. Si lo deshabilitas, solo se venderá por presentación.</p>

                                <div x-show="venderPorUnidad" x-cloak>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="precio"
                                        value="{{ $valorPrecio }}"
                                        class="theme-input mt-2"
                                        placeholder="0.00"
                                        :disabled="!venderPorUnidad"
                                        :required="venderPorUnidad"
                                    >
                                    @error('precio')
                                        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 p-5 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Presentaciones</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Define las presentaciones del producto con sus unidades y precio.</p>
                            </div>
                            <button
                                type="button"
                                x-on:click="agregarFila()"
                                class="theme-button theme-button-secondary"
                            >
                                + Agregar presentación
                            </button>
                        </div>

                        <div class="mt-4 flex flex-col gap-4">
                            <template x-for="(fila, indice) in filas" :key="indice">
                                <div class="rounded-xl border border-slate-200 p-4 dark:border-zinc-700">
                                    <div class="grid gap-4 md:grid-cols-3">
                                        <div>
                                            <label class="mb-2 block text-sm font-medium">
                                                Presentación
                                                <span x-show="indice === 0" class="font-normal text-slate-400">(principal)</span>
                                            </label>

                                            <template x-if="indice === 0">
                                                <div class="flex items-center gap-2">
                                                    <select class="theme-input cursor-not-allowed appearance-none" disabled aria-label="Presentación principal">
                                                        <option selected x-text="presentacionCajaNombre || 'Caja'"></option>
                                                    </select>
                                                    <input
                                                        type="hidden"
                                                        :name="'presentaciones[' + indice + '][id_presentacion]'"
                                                        :value="fila.id_presentacion"
                                                    >
                                                </div>
                                            </template>

                                            <template x-if="indice > 0">
                                                <div data-dropdown>
                                                    <input
                                                        type="hidden"
                                                        :name="'presentaciones[' + indice + '][id_presentacion]'"
                                                        :value="fila.id_presentacion"
                                                    >

                                                    <button
                                                        type="button"
                                                        class="theme-input flex cursor-pointer items-center justify-between gap-2 text-left"
                                                        x-on:click="dropdownAbierto = dropdownAbierto === ('presentacion-' + indice) ? null : ('presentacion-' + indice)"
                                                        x-bind:aria-expanded="dropdownAbierto === ('presentacion-' + indice)"
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

                                                    <div x-show="dropdownAbierto === ('presentacion-' + indice)" x-cloak x-transition>
                                                        <div class="farma-pick-scroll mt-2" role="listbox" aria-label="Presentaciones">
                                                            <template x-for="presentacion in catalogoPresentaciones" :key="presentacion.id">
                                                                <button
                                                                    type="button"
                                                                    role="option"
                                                                    x-on:click="seleccionarPresentacion(indice, presentacion.id)"
                                                                    x-bind:aria-selected="String(fila.id_presentacion) === String(presentacion.id)"
                                                                    x-bind:class="String(fila.id_presentacion) === String(presentacion.id) ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/25' : 'border-slate-300 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-[#0c9f9c]'"
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
                                            </template>

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
                                                min="2"
                                                :name="'presentaciones[' + indice + '][unidades]'"
                                                x-model="fila.unidades"
                                                class="theme-input"
                                                placeholder="Ej. 10"
                                                required
                                            >
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
                                            x-show="filas.length > 1 && indice > 0"
                                            x-on:click="filas.splice(indice, 1); dropdownAbierto = null"
                                            class="text-xs font-medium text-red-500 transition hover:text-red-600"
                                        >
                                            Eliminar presentación
                                        </button>
                                    </div>
                                </div>
                            </template>
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
                        x-bind:disabled="!presentacionCajaId"
                        x-bind:aria-disabled="!presentacionCajaId"
                    >{{ $esEdicion ? 'Guardar cambios' : 'Guardar producto' }}</button>
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
    </div>

    <script>
        function formularioProducto() {
            return {
                categoriaSeleccionada: @json((string) old('id_categoria', $producto?->id_categoria ?? '')),
                dropdownAbierto: null,
                errorCategoria: '',
                categorias: @json($categorias->map(fn ($categoria) => ['id' => $categoria->id, 'nombre' => $categoria->nombre])->values(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
                venderPorUnidad: {{ $venderPorUnidad ? 'true' : 'false' }},
                filas: @json($filasIniciales),
                presentacionCajaId: @json($presentacionCaja?->id),
                presentacionCajaNombre: @json($presentacionCaja?->presentacion),
                catalogoPresentaciones: @json($presentaciones->reject(fn ($p) => strtolower($p->presentacion) === 'caja')->map(fn ($p) => ['id' => $p->id, 'presentacion' => $p->presentacion])->values()),
                modalAbierto: false,
                modalModo: 'categoria',
                modalNombre: '',
                modalError: '',
                guardando: false,
                indiceFilaOrigen: null,

                agregarFila() {
                    this.filas.push({ id_presentacion: '', unidades: '', precio: '', error: '' });
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
                    this.validarDuplicado(indice, String(valor));
                },

                validarFormulario() {
                    this.errorCategoria = this.categoriaSeleccionada === '' ? 'Selecciona una categoría' : '';

                    let faltanPresentaciones = false;
                    this.filas.forEach((fila, indice) => {
                        if (indice === 0) {
                            return;
                        }
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
                        } else if (String(payload.presentacion).toLowerCase() === 'caja' && !this.presentacionCajaId) {
                            this.presentacionCajaId = payload.id;
                            this.presentacionCajaNombre = payload.presentacion;
                            if (this.filas[0]) {
                                this.filas[0].id_presentacion = String(payload.id);
                                this.filas[0].error = '';
                            }
                        } else {
                            this.catalogoPresentaciones.push({ id: payload.id, presentacion: payload.presentacion });
                            if (this.indiceFilaOrigen !== null && this.filas[this.indiceFilaOrigen]) {
                                this.filas[this.indiceFilaOrigen].id_presentacion = String(payload.id);
                                this.filas[this.indiceFilaOrigen].error = '';
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
