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
