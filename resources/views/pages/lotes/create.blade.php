<x-layouts::app :title="__('Registrar lote')">
    <div id="theme-shell">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Trazabilidad</p>
                    <h1 class="mt-2 text-3xl font-bold">Registrar nuevo lote</h1>
                </div>
            </div>

@php
    $mapaPedidos = $pedidos->mapWithKeys(fn ($pedido) => [
        $pedido->id => [
            'proveedor_id' => $pedido->id_proveedor,
            'proveedor' => $pedido->proveedor?->nombre_proveedor ?? 'Sin proveedor',
            'sucursal_id' => $pedido->id_sucursal,
            'sucursal' => $pedido->sucursal?->nombre_sucursal ?? 'Sin sucursal',
        ],
    ])->all();
@endphp

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

                    <div
                        class="md:col-span-2 grid gap-5 md:grid-cols-3"
                        x-data="lotePedido()"
                    >
                        <div>
                            <label class="mb-2 block text-sm font-medium">Pedido de compra (opcional)</label>
                            <select name="id_pedido" class="theme-input" x-model="pedido" @change="aplicarPedido()">
                                <option value="">Sin pedido (registro directo)</option>
                                @foreach($pedidos as $pedido)
                                    <option value="{{ $pedido->id }}">
                                        #{{ $pedido->id }} · {{ $pedido->proveedor?->nombre_proveedor ?? 'Sin proveedor' }} · {{ $pedido->sucursal?->nombre_sucursal ?? 'Sin sucursal' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_pedido')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Proveedor (opcional)</label>
                            <select name="id_proveedor" class="theme-input" x-model="proveedor">
                                <option value="">Selecciona un proveedor</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre_proveedor }}</option>
                                @endforeach
                            </select>
                            <p x-show="pedidoInfo" x-cloak class="mt-1 text-xs text-slate-500">Se toma del pedido elegido; puedes ajustarlo.</p>
                            @error('id_proveedor')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium">Sucursal</label>
                            <select name="sucursal" class="theme-input" x-model="sucursal" required>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_sucursal }}</option>
                                @endforeach
                            </select>
                            <p x-show="pedidoInfo" x-cloak class="mt-1 text-xs text-slate-500">Se toma del pedido elegido; puedes ajustarla.</p>
                            @error('sucursal')
                                <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <p x-show="pedidoInfo" x-cloak class="rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-slate-700 md:col-span-3 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-slate-200">
                            El proveedor y la sucursal vienen del pedido <span class="font-bold" x-text="'#' + pedido"></span>
                            (<span x-text="pedidoInfo?.proveedor"></span> · <span x-text="pedidoInfo?.sucursal"></span>).
                        </p>
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
                        x-data="loteProducto()"
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

    <script>
        function lotePedido() {
            return {
                pedido: {!! json_encode((string) old('id_pedido', '')) !!},
                proveedor: {!! json_encode((string) old('id_proveedor', '')) !!},
                sucursal: {!! json_encode((string) old('sucursal', $selectedSucursalId ?? '')) !!},
                mapa: {!! json_encode($mapaPedidos) !!},
                get pedidoInfo() { return this.mapa[this.pedido] ?? null; },
                aplicarPedido() {
                    const info = this.pedidoInfo;
                    if (info) {
                        this.proveedor = info.proveedor_id !== null ? String(info.proveedor_id) : '';
                        this.sucursal = String(info.sucursal_id);
                    }
                },
            };
        }

        function loteProducto() {
            return {
                producto: {!! json_encode((string) old('id_producto', '')) !!},
                presentacion: {!! json_encode((string) old('id_presentacion', '')) !!},
                mapa: {!! json_encode($presentacionesPorProducto) !!},
                get presentaciones() { return this.mapa[this.producto] ?? []; },
            };
        }

        if (window.Alpine) {
            window.Alpine.data('lotePedido', lotePedido);
            window.Alpine.data('loteProducto', loteProducto);
        }

        document.addEventListener('alpine:init', () => {
            window.Alpine.data('lotePedido', lotePedido);
            window.Alpine.data('loteProducto', loteProducto);
        });
    </script>

</x-layouts::app>
