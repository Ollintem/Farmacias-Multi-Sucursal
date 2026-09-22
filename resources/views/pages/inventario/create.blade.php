<x-layouts::app :title="__('Nuevo producto')">
    <div id="theme-shell">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Inventario</p>
                    <h1 class="mt-2 text-3xl font-bold">Agregar producto</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('inventario.index', ['sucursal' => $selectedSucursalId]) }}" class="theme-button theme-button-secondary">Volver</a>
                </div>
            </div>

            <form action="{{ route('inventario.store') }}" method="POST" class="theme-card">
                @csrf

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
                        <label class="mb-2 block text-sm font-medium">Sucursal</label>
                        <select name="sucursal" class="theme-input" required>
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
                        <label class="mb-2 block text-sm font-medium">Código de barras o código interno</label>
                        <input id="barcode-input" name="codigo_barras" value="{{ old('codigo_barras') }}" class="theme-input" placeholder="Ej. 7501234567890" autocomplete="off" autocorrect="off" spellcheck="false" inputmode="numeric" required>
                        <p class="mt-1 text-xs text-slate-500">También puedes escribir el código o usar un lector USB.</p>
                        @error('codigo_barras')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Nombre del producto</label>
                        <input name="nombre_producto" value="{{ old('nombre_producto') }}" class="theme-input" required>
                        @error('nombre_producto')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Stock inicial</label>
                        <input type="number" min="0" name="stock" value="{{ old('stock', 0) }}" class="theme-input" required>
                        @error('stock')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Precio unitario</label>
                        <input type="number" step="0.01" min="0" name="precio" value="{{ old('precio') }}" class="theme-input" required>
                        @error('precio')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Lote</label>
                        <select name="id_lote" class="theme-input" required>
                            <option value="">Selecciona un lote</option>
                            @foreach($lotes as $lote)
                                <option value="{{ $lote->id }}" {{ old('id_lote') == $lote->id ? 'selected' : '' }}>
                                    {{ $lote->folio }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_lote')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Presentación</label>
                        <select name="id_presentacion" class="theme-input" required>
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

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Descripción</label>
                        <textarea name="descripcion" rows="4" class="theme-input" placeholder="Descripción del producto, uso, presentación o observaciones.">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-3 text-sm font-medium">
                            <input type="checkbox" name="es_controlado" value="1" {{ old('es_controlado', false) ? 'checked' : '' }}>
                            Producto controlado
                        </label>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('inventario.index', ['sucursal' => $selectedSucursalId]) }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar producto</button>
                </div>
            </form>
        </div>
    </div>

</x-layouts::app>
