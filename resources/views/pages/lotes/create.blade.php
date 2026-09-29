<x-layouts::app :title="__('Registrar lote')">
    <div id="theme-shell">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Trazabilidad</p>
                    <h1 class="mt-2 text-3xl font-bold">Registrar nuevo lote</h1>
                </div>
            </div>

            <form action="{{ route('lotes.store') }}" method="POST" class="theme-card">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Folio del lote</label>
                        <input name="folio" value="{{ old('folio') }}" class="theme-input" placeholder="LOT-001" required>
                        @error('folio')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Pedido de compra (opcional)</label>
                        <select name="id_pedido" class="theme-input">
                            <option value="">Sin pedido (registro directo)</option>
                            @foreach($pedidos as $pedido)
                                <option value="{{ $pedido->id }}" {{ old('id_pedido') == $pedido->id ? 'selected' : '' }}>
                                    #{{ $pedido->id }} · {{ $pedido->proveedor?->nombre_proveedor ?? 'Sin proveedor' }} · {{ $pedido->sucursal?->nombre_sucursal ?? 'Sin sucursal' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_pedido')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Proveedor (referencia, opcional)</label>
                        <x-option-pick name="id_proveedor" label="Proveedores" placeholder="Selecciona un proveedor" :options="$proveedores->pluck('nombre_proveedor', 'id')" />
                        @error('id_proveedor')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Sucursal</label>
                        <x-option-pick name="sucursal" label="Sucursales" placeholder="Selecciona una sucursal" :options="$sucursales->pluck('nombre_sucursal', 'id')" :value="$selectedSucursalId ?? ''" />
                        @error('sucursal')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Fecha de entrega</label>
                        <input type="date" name="entregado_en" value="{{ old('entregado_en') }}" class="theme-input" required>
                        @error('entregado_en')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Fecha de caducidad</label>
                        <input type="date" name="fecha_caducidad" value="{{ old('fecha_caducidad') }}" class="theme-input" required>
                        @error('fecha_caducidad')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div
                        class="md:col-span-2 grid gap-5 md:grid-cols-2"
                        x-data="{
                            producto: '{{ old('id_producto', '') }}',
                            presentacion: '{{ old('id_presentacion', '') }}',
                            mapa: @json($presentacionesPorProducto),
                            get presentaciones() { return this.mapa[this.producto] ?? []; },
                        }"
                    >
                        <div>
                            <label class="mb-2 block text-sm font-medium">Producto</label>
                            <select name="id_producto" class="theme-input" x-model="producto" @change="presentacion = ''" required>
                                <option value="">Selecciona un producto</option>
                                @foreach($productos as $producto)
                                    <option value="{{ $producto->id }}">
                                        {{ $producto->nombre_producto }} · {{ $producto->codigo_barras }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_producto')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Presentación</label>
                            <select name="id_presentacion" class="theme-input" x-model="presentacion" :disabled="presentaciones.length === 0" required>
                                <option value="">Selecciona una presentación</option>
                                <template x-for="item in presentaciones" :key="item.id">
                                    <option :value="item.id" x-text="`${item.nombre} · ${item.unidades} uds.`"></option>
                                </template>
                            </select>
                            @error('id_presentacion')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Cantidad del lote</label>
                        <input type="number" min="1" name="stock" value="{{ old('stock', 1) }}" class="theme-input" required>
                        @error('stock')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('lotes.index', ['sucursal' => old('sucursal', $selectedSucursalId)]) }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar lote</button>
                </div>
            </form>
        </div>
    </div>

</x-layouts::app>
