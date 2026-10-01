<x-layouts::app :title="__('Nuevo pedido')">
    <div id="theme-shell">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Almacén</p>
                    <h1 class="mt-2 text-3xl font-bold">Registrar nuevo pedido</h1>
                    <p class="mt-1 text-sm theme-subtle">Solicitud de mercancía a un proveedor para una sucursal.</p>
                </div>
            </div>

            <form action="{{ route('pedidos.store') }}" method="POST" class="theme-card">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Proveedor</label>
                        <x-option-pick name="id_proveedor" label="Proveedores" placeholder="Selecciona un proveedor" :options="$proveedores->pluck('nombre_proveedor', 'id')" />
                        @error('id_proveedor')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Sucursal destino</label>
                        <x-option-pick name="id_sucursal" label="Sucursales" placeholder="Selecciona una sucursal" :options="$sucursales->pluck('nombre_sucursal', 'id')" :value="old('id_sucursal', $selectedSucursalId ?? '')" />
                        @error('id_sucursal')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Estado inicial</label>
                        <select name="estado" class="theme-input">
                            <option value="pendiente" {{ old('estado', 'pendiente') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="recibido" {{ old('estado') === 'recibido' ? 'selected' : '' }}>Recibido</option>
                            <option value="cancelado" {{ old('estado') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                        @error('estado')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Fecha de entrega (opcional)</label>
                        <input type="date" name="entregado_en" value="{{ old('entregado_en') }}" class="theme-input">
                        @error('entregado_en')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-8" x-data="pedidoProductos()">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-bold">Productos del pedido</h2>
                            <p class="mt-1 text-sm theme-subtle">Agrega al menos un producto con su cantidad solicitada. <span x-text="totalUnidades()"></span></p>
                        </div>
                        <button type="button" @click="agregar()" class="theme-button theme-button-secondary">+ Agregar producto</button>
                    </div>

                    @error('productos')
                        <span class="mt-2 block text-sm text-red-500">{{ $message }}</span>
                    @enderror

                    <div class="mt-4 flex flex-col gap-3">
                        <template x-for="(row, index) in rows" :key="row.key">
                            <div class="grid gap-3 md:grid-cols-[1fr_160px_44px] md:items-start">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Producto</label>
                                    <select :name="`productos[${index}][id]`" x-model="row.producto" class="theme-input" required>
                                        <option value="">Selecciona un producto</option>
                                        @foreach($productos as $producto)
                                            <option value="{{ $producto->id }}">{{ $producto->nombre_producto }} · {{ $producto->codigo_barras }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Cantidad</label>
                                    <input type="number" min="1" :name="`productos[${index}][cantidad]`" x-model.number="row.cantidad" class="theme-input" required>
                                </div>
                                <div class="md:pt-9">
                                    <button type="button" @click="eliminar(index)" x-show="rows.length > 1" class="theme-button theme-button-secondary w-11 px-0" aria-label="Quitar producto">×</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('entradas-de-almacen.index', ['sucursal' => old('id_sucursal', $selectedSucursalId), 'tipo' => 'pedidos']) }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar pedido</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function pedidoProductos() {
            return {
                rows: {!! json_encode(collect(old('productos', [['id' => '', 'cantidad' => 1]]))->map(fn ($item) => ['key' => uniqid(), 'producto' => (string) ($item['id'] ?? ''), 'cantidad' => (int) ($item['cantidad'] ?? 1)])->values()) !!},
                agregar() {
                    this.rows.push({ key: `${Date.now()}-${this.rows.length}`, producto: '', cantidad: 1 });
                },
                eliminar(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },
                totalUnidades() {
                    const total = this.rows.reduce((sum, row) => sum + (Number(row.cantidad) || 0), 0);

                    return this.rows.length === 0 ? '' : `${total} ${total === 1 ? 'unidad' : 'unidades'} en total.`;
                },
            };
        }

        if (window.Alpine) {
            window.Alpine.data('pedidoProductos', pedidoProductos);
        }

        document.addEventListener('alpine:init', () => {
            window.Alpine.data('pedidoProductos', pedidoProductos);
        });
    </script>

</x-layouts::app>
