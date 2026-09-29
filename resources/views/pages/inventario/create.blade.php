@php
    $esEdicion = isset($producto);
    $entradasPresentaciones = old('presentaciones');
    $presentacionesOrdenadas = $esEdicion
        ? $producto->presentacionesPrecio
            ->sortBy(fn ($presentacion) => $presentacion->id_presentacion === ($presentacionCaja?->id) ? 0 : 1)
            ->values()
        : collect();
    $filaCaja = $presentacionesOrdenadas->firstWhere('id_presentacion', $presentacionCaja?->id);
    $filasIniciales = is_array($entradasPresentaciones)
        ? collect($entradasPresentaciones)->slice(1)->map(fn ($fila, $clave) => [
            'uid' => $clave + 1,
            'id_presentacion' => (string) ($fila['id_presentacion'] ?? ''),
            'unidades' => $fila['unidades'] ?? 2,
            'precio' => $fila['precio'] ?? '',
        ])->values()->all()
        : $presentacionesOrdenadas
            ->reject(fn ($presentacion) => $presentacion->id_presentacion === ($presentacionCaja?->id))
            ->values()
            ->map(fn ($presentacion, $clave) => [
                'uid' => $clave + 1,
                'id_presentacion' => (string) $presentacion->id_presentacion,
                'unidades' => $presentacion->unidades,
                'precio' => $presentacion->precio_presentacion,
            ])
            ->all();
    $catalogoPresentaciones = $presentaciones
        ->reject(fn ($presentacion) => $presentacion->id === ($presentacionCaja?->id))
        ->map(fn ($presentacion) => ['id' => $presentacion->id, 'presentacion' => $presentacion->presentacion])
        ->values();
    // Nota: @json() no admite comas dentro de la expresión (las confunde con
    // sus argumentos y compila sin flags de escape, rompiendo el atributo
    // x-data). Por eso todo lo que va a @json se pre-calcula aquí en
    // variables simples.
    $categoriaInicial = (string) old('id_categoria', $esEdicion ? $producto->id_categoria : '');
    $catalogoCategoriasInicial = $categorias
        ->map(fn ($categoria) => ['id' => $categoria->id, 'nombre' => $categoria->nombre])
        ->values()
        ->all();
    $venderPorUnidadInicial = (bool) old('vender_por_unidad', $esEdicion ? $producto->precio !== null : false);
@endphp
<x-layouts::app :title="__($esEdicion ? 'Editar producto' : 'Nuevo producto')">
    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Inventario</p>
                    <h1 class="mt-2 text-3xl font-bold">{{ $esEdicion ? 'Editar producto' : 'Agregar producto' }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('inventario.productos', ['sucursal' => $selectedSucursalId]) }}" class="theme-button theme-button-secondary">Volver</a>
                </div>
                <span class="w-fit rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $esEdicion ? 'Editar registro' : 'Nuevo registro' }}</span>
            </div>

            @if(!$presentacionCaja)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">
                    No hay presentación «Caja» registrada en el catálogo. Créala antes de registrar productos.
                </div>
            @endif

            <form
                action="{{ $esEdicion ? route('inventario.update', $producto) : route('inventario.store') }}"
                method="POST"
                class="theme-card"
                x-data="{
                    categoria: @json($categoriaInicial),
                    categoriaAbierta: false,
                    catalogoCategorias: @json($catalogoCategoriasInicial),
                    categoriasExtra: [],
                    categoriaNombre() { const extra = this.categoriasExtra.find((item) => String(item.id) === String(this.categoria)); if (extra) { return extra.nombre; } const encontrada = this.catalogoCategorias.find((item) => String(item.id) === String(this.categoria)); return encontrada ? encontrada.nombre : 'Selecciona una categoría'; },
                    elegirCategoria(id) { this.categoria = String(id); this.categoriaAbierta = false; },
                    venderPorUnidad: @json($venderPorUnidadInicial),
                    presentacionCajaId: {{ $presentacionCaja?->id ?? 'null' }},
                    presentacionCajaNombre: @json($presentacionCaja?->presentacion),
                    catalogoPresentaciones: @json($catalogoPresentaciones),
                    filas: @json($filasIniciales),
                    siguienteUid: {{ count($filasIniciales) + 1 }},
                    agregarFila() { this.filas.push({ uid: this.siguienteUid++, id_presentacion: '', unidades: 2, precio: '' }); },
                    quitarFila(uid) { this.filas = this.filas.filter((fila) => fila.uid !== uid); },
                    modal: null,
                    modalNombre: '',
                    modalError: '',
                    csrf: '{{ csrf_token() }}',
                    urlCategoria: '{{ route('categorias.store') }}',
                    urlPresentacion: '{{ route('presentaciones.store') }}',
                    abrirModal(tipo) { this.modal = tipo; this.modalNombre = ''; this.modalError = ''; },
                    cerrarModal() { this.modal = null; this.modalNombre = ''; this.modalError = ''; },
                    async guardarModal() {
                        this.modalError = '';
                        const esCategoria = this.modal === 'categoria';
                        const respuesta = await fetch(esCategoria ? this.urlCategoria : this.urlPresentacion, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                            body: JSON.stringify(esCategoria ? { nombre: this.modalNombre } : { presentacion: this.modalNombre }),
                        });
                        const datos = await respuesta.json();
                        if (!respuesta.ok) {
                            this.modalError = datos.message ?? 'No se pudo guardar el registro.';
                            return;
                        }
                        if (esCategoria) {
                            this.catalogoCategorias.push({ id: datos.id, nombre: datos.nombre });
                            this.categoriasExtra.push({ id: datos.id, nombre: datos.nombre });
                            this.categoria = String(datos.id);
                        } else {
                            this.catalogoPresentaciones.push({ id: datos.id, presentacion: datos.presentacion });
                        }
                        this.cerrarModal();
                    },
                }"
            >
                @csrf
                @if($esEdicion)
                    @method('PUT')
                @endif

                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Escaneo</p>
                            <p class="mt-1 text-sm text-slate-700">Usa la cámara de la PC o un lector USB para capturar el código directamente en el campo.</p>
                        </div>
                        <button type="button" id="scanner-focus-button" class="theme-button theme-button-secondary whitespace-nowrap">Escanear con cámara</button>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Código de barras o código interno</label>
                        <input id="barcode-input" name="codigo_barras" value="{{ old('codigo_barras', $esEdicion ? $producto->codigo_barras : '') }}" class="theme-input" placeholder="Ej. 7501234567890" autocomplete="off" autocorrect="off" spellcheck="false" inputmode="numeric" required>
                        <p class="mt-1 text-xs text-slate-500">También puedes escribir el código o usar un lector USB.</p>
                        @error('codigo_barras')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Nombre del producto</label>
                        <input name="nombre_producto" value="{{ old('nombre_producto', $esEdicion ? $producto->nombre_producto : '') }}" class="theme-input" required>
                        @error('nombre_producto')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div x-on:click.capture.window="if (!$el.contains($event.target)) categoriaAbierta = false">
                        <label class="mb-2 block text-sm font-medium">Categoría</label>
                        <input type="hidden" name="id_categoria" :value="categoria">
                        <button
                            type="button"
                            @click="categoriaAbierta = !categoriaAbierta"
                            :aria-expanded="categoriaAbierta"
                            aria-haspopup="listbox"
                            class="theme-input flex cursor-pointer items-center justify-between gap-2 text-left"
                        >
                            <span x-text="categoriaNombre()"></span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="categoriaAbierta && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="categoriaAbierta" x-cloak x-transition class="farma-pick-scroll mt-2" role="listbox" aria-label="Categorías">
                            @foreach($categorias as $categoria)
                                <button
                                    type="button"
                                    role="option"
                                    @click="elegirCategoria('{{ $categoria->id }}')"
                                    :aria-selected="String(categoria) === '{{ $categoria->id }}'"
                                    :class="String(categoria) === '{{ $categoria->id }}' ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/25' : 'border-slate-300 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-[#0c9f9c]'"
                                    class="flex min-h-11 w-full cursor-pointer items-center justify-between gap-2 rounded-xl border-[1.5px] px-3 py-2 text-left text-sm font-semibold transition active:scale-[0.99]"
                                >
                                    <span class="truncate">{{ $categoria->nombre }}</span>
                                </button>
                            @endforeach
                            <template x-for="item in categoriasExtra" :key="item.id">
                                <button
                                    type="button"
                                    role="option"
                                    @click="elegirCategoria(item.id)"
                                    :aria-selected="String(categoria) === String(item.id)"
                                    :class="String(categoria) === String(item.id) ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/25' : 'border-slate-300 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-[#0c9f9c]'"
                                    class="flex min-h-11 w-full cursor-pointer items-center justify-between gap-2 rounded-xl border-[1.5px] px-3 py-2 text-left text-sm font-semibold transition active:scale-[0.99]"
                                >
                                    <span class="truncate" x-text="item.nombre"></span>
                                </button>
                            </template>
                            <button
                                type="button"
                                @click="abrirModal('categoria')"
                                class="mt-2 flex min-h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-[1.5px] border-dashed border-emerald-400 px-3 py-2 text-center text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-500/10"
                            >
                                + Agregar nueva categoría
                            </button>
                        </div>
                        @error('id_categoria')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-3 text-sm font-medium">
                            <input type="checkbox" name="es_controlado" value="1" {{ old('es_controlado', $esEdicion ? $producto->es_controlado : false) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500">
                            Producto controlado
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-3 text-sm font-medium">
                            <input type="checkbox" name="vender_por_unidad" value="1" x-model="venderPorUnidad" class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500">
                            Vender por unidad
                        </label>
                    </div>

                    <div x-show="venderPorUnidad" x-cloak>
                        <label class="mb-2 block text-sm font-medium">Precio unitario</label>
                        <input type="number" step="0.01" min="0" name="precio" value="{{ old('precio', $esEdicion ? $producto->precio : '') }}" class="theme-input">
                        @error('precio')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Descripción</label>
                        <textarea name="descripcion" rows="4" class="theme-input" placeholder="Descripción del producto, uso, presentación o observaciones.">{{ old('descripcion', $esEdicion ? $producto->descripcion : '') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <p class="theme-subtle text-xs font-semibold uppercase tracking-[0.2em]">Presentaciones y precios</p>
                        <p class="mt-1 text-xs text-slate-500">La primera presentación siempre es Caja y no se puede cambiar.</p>
                        @error('presentaciones')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2 grid gap-5 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium">Presentación principal</label>
                            <input type="hidden" name="presentaciones[0][id_presentacion]" value="{{ $presentacionCaja?->id }}">
                            <select disabled aria-label="Presentación principal" class="theme-input">
                                <option x-text="presentacionCajaNombre || 'Caja'">Caja</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Unidades por caja</label>
                            <input type="number" min="2" name="presentaciones[0][unidades]" value="{{ old('presentaciones.0.unidades', $filaCaja?->unidades ?? '') }}" class="theme-input" required>
                            @error('presentaciones.0.unidades')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium">Precio por caja</label>
                            <input type="number" step="0.01" min="0" name="presentaciones[0][precio]" value="{{ old('presentaciones.0.precio', $filaCaja?->precio_presentacion ?? '') }}" class="theme-input" required>
                            @error('presentaciones.0.precio')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <template x-for="(fila, indice) in filas" :key="fila.uid">
                        <div class="md:col-span-2 grid gap-5 md:grid-cols-4">
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium">Presentación</label>
                                <select class="theme-input" x-model="fila.id_presentacion" :name="'presentaciones[' + (indice + 1) + '][id_presentacion]'" required>
                                    <option value="">Selecciona una presentación</option>
                                    <template x-for="item in catalogoPresentaciones" :key="item.id">
                                        <option :value="item.id" x-text="item.presentacion"></option>
                                    </template>
                                </select>
                                <button type="button" @click="abrirModal('presentacion')" class="mt-1.5 text-xs font-semibold text-emerald-700 transition hover:text-emerald-900 dark:text-emerald-300 dark:hover:text-emerald-200">+ Agregar nueva presentación</button>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium">Unidades</label>
                                <input type="number" min="2" class="theme-input" x-model="fila.unidades" :name="'presentaciones[' + (indice + 1) + '][unidades]'" required>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium">Precio</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" step="0.01" min="0" class="theme-input" x-model="fila.precio" :name="'presentaciones[' + (indice + 1) + '][precio]'" required>
                                    <button type="button" @click="quitarFila(fila.uid)" title="Quitar presentación" aria-label="Quitar presentación" class="shrink-0 text-slate-400 transition hover:text-red-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="md:col-span-2">
                        <button type="button" @click="agregarFila()" class="theme-button theme-button-secondary whitespace-nowrap">+ Agregar presentación</button>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('inventario.productos', ['sucursal' => $selectedSucursalId]) }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">{{ $esEdicion ? 'Actualizar producto' : 'Guardar producto' }}</button>
                </div>

                <div x-show="modal" x-cloak x-effect="modal ? $nextTick(() => $refs.modalInput?.focus()) : null" class="fixed inset-0 z-50 grid place-items-center bg-slate-950/50 p-4" role="dialog" aria-modal="true">
                    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-zinc-900" @click.outside="cerrarModal()">
                        <h2 class="text-lg font-bold" x-text="modal === 'categoria' ? 'Agregar nueva categoría' : 'Agregar nueva presentación'"></h2>
                        <p class="mt-1 text-sm theme-subtle" x-text="modal === 'categoria' ? 'Se agregará al selector de categorías.' : 'Se agregará al selector de presentaciones.'"></p>
                        <div @keydown.enter.prevent="guardarModal()" class="mt-4">
                            <label class="mb-2 block text-sm font-medium" x-text="modal === 'categoria' ? 'Nombre de la categoría' : 'Nombre de la presentación'"></label>
                            <input x-ref="modalInput" x-model="modalNombre" class="theme-input" :maxlength="modal === 'categoria' ? 60 : 20" required>
                            <p x-show="modalError" x-cloak x-text="modalError" class="mt-1 block text-sm text-red-500"></p>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="cerrarModal()" class="theme-button theme-button-secondary">Cancelar</button>
                                <button type="button" @click="guardarModal()" class="theme-button theme-button-primary">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</x-layouts::app>
