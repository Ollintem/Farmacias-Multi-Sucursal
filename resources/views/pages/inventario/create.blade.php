<x-layouts::app :title="__('Nuevo producto')">
    <div id="theme-shell">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <a href="{{ route('inventario.index', ['sucursal' => $selectedSucursalId]) }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition hover:text-emerald-900 dark:text-emerald-300 dark:hover:text-emerald-200">← Volver al inventario</a>
                    <div class="mt-6 flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-600 text-sm font-black text-white">Rx</span>
                        <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-700 dark:text-emerald-300">Inventario</p>
                    </div>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">Agregar producto</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Registra un producto del catálogo y vincúlalo con su sucursal, lote y presentación.</p>
                </div>
                <span class="w-fit rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">Nuevo registro</span>
            </div>

            @if($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    <p class="font-bold">Revisa los siguientes campos:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $faltanCatalogos = $sucursales->isEmpty() || $lotes->isEmpty() || $presentaciones->isEmpty();
            @endphp

            @if($faltanCatalogos)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200" role="alert">
                    <p class="font-bold">Faltan catálogos para registrar productos:</p>
                    <ul class="mt-1 list-disc pl-5">
                        @if($sucursales->isEmpty())
                            <li>
                                No hay sucursales registradas.
                                @if(auth()->user()?->puedeVerModulo('Sucursales'))
                                    <a href="{{ route('sucursales.create') }}" class="font-bold underline underline-offset-2">Registra una sucursal</a>
                                @endif
                            </li>
                        @endif
                        @if($lotes->isEmpty())
                            <li>
                                No hay lotes registrados.
                                @if(auth()->user()?->puedeVerModulo('Lotes y caducidades'))
                                    <a href="{{ route('lotes.create') }}" class="font-bold underline underline-offset-2">Registra un lote</a>
                                @endif
                            </li>
                        @endif
                        @if($presentaciones->isEmpty())
                            <li>No hay presentaciones registradas. Pide a un administrador que las dé de alta en la base de datos.</li>
                        @endif
                    </ul>
                </div>
            @endif

            <div class="module-card p-5 sm:p-8">
                <form action="{{ route('inventario.store') }}" method="POST" class="space-y-8">
                    @csrf

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Escaneo</p>
                                <p class="mt-1 text-sm text-slate-700 dark:text-slate-300">Usa la cámara de la PC o un lector USB para capturar el código directamente en el campo.</p>
                            </div>
                            <button type="button" id="scanner-focus-button" class="theme-button theme-button-secondary whitespace-nowrap">Escanear con cámara</button>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-sky-100 text-xs font-black text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">01</span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Origen del producto</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Sucursal y código con el que se identificará.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="sucursal" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Sucursal</label>
                                <select id="sucursal" name="sucursal" class="theme-input" required>
                                    <option value="">Selecciona una sucursal</option>
                                    @foreach($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ old('sucursal', $selectedSucursalId) == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre_sucursal }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sucursal')
                                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="barcode-input" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Código de barras o código interno</label>
                                <input id="barcode-input" name="codigo_barras" value="{{ old('codigo_barras') }}" class="theme-input" placeholder="Ej. 7501234567890" autocomplete="off" autocorrect="off" spellcheck="false" required>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">También puedes escribir el código o usar un lector USB.</p>
                                @error('codigo_barras')
                                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-100 text-xs font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">02</span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Datos del producto</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Nombre, existencias y precio de venta.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="nombre_producto" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Nombre del producto</label>
                                <input id="nombre_producto" name="nombre_producto" value="{{ old('nombre_producto') }}" class="theme-input" placeholder="Ej. Paracetamol 500 mg" required>
                                @error('nombre_producto')
                                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="descripcion" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="2" class="theme-input" placeholder="Descripción del producto, uso, presentación u observaciones.">{{ old('descripcion') }}</textarea>
                            </div>

                            <div>
                                <label for="stock" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Stock inicial</label>
                                <input id="stock" type="number" min="0" name="stock" value="{{ old('stock', 0) }}" class="theme-input" required>
                                @error('stock')
                                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="precio" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Precio unitario</label>
                                <input id="precio" type="number" step="0.01" min="0" name="precio" value="{{ old('precio') }}" class="theme-input" placeholder="0.00" required>
                                @error('precio')
                                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-100 text-xs font-black text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">03</span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Trazabilidad</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Lote y presentación asociados al producto.</p>
                            </div>
                        </div>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="id_lote" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Lote</label>
                                <select id="id_lote" name="id_lote" class="theme-input" required>
                                    <option value="">Selecciona un lote</option>
                                    @foreach($lotes as $lote)
                                        <option value="{{ $lote->id }}" {{ old('id_lote') == $lote->id ? 'selected' : '' }}>
                                            {{ $lote->folio }} — vence {{ optional($lote->fecha_caducidad)->format('d/m/Y') ?? 's/c' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_lote')
                                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="id_presentacion" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Presentación</label>
                                <select id="id_presentacion" name="id_presentacion" class="theme-input" required>
                                    <option value="">Selecciona una presentación</option>
                                    @foreach($presentaciones as $presentacion)
                                        <option value="{{ $presentacion->id }}" {{ old('id_presentacion') == $presentacion->id ? 'selected' : '' }}>
                                            {{ $presentacion->presentacion }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_presentacion')
                                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-3 text-sm font-medium">
                            <input type="checkbox" name="es_controlado" value="1" {{ old('es_controlado', false) ? 'checked' : '' }}>
                            Producto controlado
                        </label>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
