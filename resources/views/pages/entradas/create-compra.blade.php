<x-layouts::app :title="__('Nueva compra')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado limpio --}}
            <header class="mb-6" aria-label="Encabezado de nueva compra">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Almacén · Nuevo registro</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Registrar nueva compra</h1>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('entradas-de-almacen.index', ['sucursal' => old('id_sucursal', $selectedSucursalId), 'tipo' => 'compras']) }}" class="theme-button theme-button-secondary">← Volver</a>
                    </div>
                </div>
            </header>

            <section class="module-card noti-listado" aria-label="Datos de la compra">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Datos de la compra</h2>
                        <p class="theme-subtle text-sm">Mercancía adquirida a un proveedor para una sucursal</p>
                    </div>
                </header>
                <div class="p-5 sm:p-6">
            <form action="{{ route('compras.store') }}" method="POST">
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
                        <input type="hidden" name="id_sucursal" value="{{ $selectedSucursalId ?? '' }}">
                        <p class="theme-input flex items-center font-semibold" aria-readonly="true">{{ $sucursales->firstWhere('id', $selectedSucursalId)?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                        <p class="mt-1 text-xs theme-subtle">El destino es tu sucursal activa y solo cambia desde el menú lateral.</p>
                        @error('id_sucursal')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-8" x-data="compraLotes()">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-bold">Lotes de la compra</h2>
                            <p class="mt-1 text-sm theme-subtle">Agrega al menos un lote con su producto, cantidad, caducidad y fecha de entrada. <span x-text="totalUnidades()"></span></p>
                        </div>
                        <button type="button" @click="agregar()" class="theme-button theme-button-secondary">+ Agregar lote</button>
                    </div>

                    @error('lotes')
                        <span class="mt-2 block text-sm text-red-500">{{ $message }}</span>
                    @enderror

                    <div class="mt-4 flex flex-col gap-4">
                        <template x-for="(row, index) in rows" :key="row.key">
                            <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                                <div class="grid gap-3 md:grid-cols-[1fr_150px_170px_170px_44px] md:items-start">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Producto</label>
                                        <select name="lotes[0][id_producto]" :name="`lotes[${index}][id_producto]`" x-model="row.producto" class="theme-input" required>
                                            <option value="">Selecciona un producto</option>
                                            @foreach($productos as $producto)
                                                <option value="{{ $producto->id }}">{{ $producto->nombre_producto }} · {{ $producto->codigo_barras }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Cantidad</label>
                                        <input type="number" min="1" name="lotes[0][cantidad]" :name="`lotes[${index}][cantidad]`" x-model.number="row.cantidad" class="theme-input" required>
                                        <p x-show="presentacion(row.producto)" x-cloak class="mt-1 text-xs theme-subtle" x-text="presentacion(row.producto)"></p>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Fecha de entrada</label>
                                        <input type="date" name="lotes[0][fecha_entrada]" :name="`lotes[${index}][fecha_entrada]`" x-model="row.fecha_entrada" class="theme-input" required>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium">Fecha de caducidad</label>
                                        <input type="date" name="lotes[0][fecha_caducidad]" :name="`lotes[${index}][fecha_caducidad]`" x-model="row.fecha_caducidad" class="theme-input" required>
                                    </div>
                                    <div class="md:pt-9">
                                        <button type="button" @click="eliminar(index)" x-show="rows.length > 1" class="theme-button theme-button-secondary w-11 px-0" aria-label="Quitar lote">×</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('entradas-de-almacen.index', ['sucursal' => old('id_sucursal', $selectedSucursalId), 'tipo' => 'compras']) }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar compra</button>
                </div>
            </form>
                </div>
            </section>
        </div>
    </div>

    <script>
        function compraLotes() {
            const presentaciones = {!! json_encode($presentacionPorProducto) !!};
            const filasIniciales = {!! json_encode(collect(old('lotes', [['id_producto' => '', 'cantidad' => 1, 'fecha_entrada' => now()->toDateString(), 'fecha_caducidad' => '']]))->map(fn ($item) => ['key' => uniqid(), 'producto' => (string) ($item['id_producto'] ?? ''), 'cantidad' => (int) ($item['cantidad'] ?? 1), 'fecha_entrada' => (string) ($item['fecha_entrada'] ?? now()->toDateString()), 'fecha_caducidad' => (string) ($item['fecha_caducidad'] ?? '')])->values()) !!};

            return {
                rows: filasIniciales,
                presentacion(producto) {
                    const info = presentaciones[producto] ?? null;

                    return info ? `${info.nombre} · ${info.unidades} unidad(es)` : '';
                },
                agregar() {
                    this.rows.push({ key: `${Date.now()}-${this.rows.length}`, producto: '', cantidad: 1, fecha_entrada: '{{ now()->toDateString() }}', fecha_caducidad: '' });
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
            window.Alpine.data('compraLotes', compraLotes);
        }

        document.addEventListener('alpine:init', () => {
            window.Alpine.data('compraLotes', compraLotes);
        });
    </script>

</x-layouts::app>
