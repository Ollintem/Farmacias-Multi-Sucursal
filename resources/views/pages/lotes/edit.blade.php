<x-layouts::app :title="__('Editar lote')">
    <div id="theme-shell">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Trazabilidad</p>
                    <h1 class="mt-2 text-3xl font-bold">Editar lote {{ $lote->folio }}</h1>
                </div>
            </div>

            <form action="{{ route('lotes.update', $lote) }}" method="POST" class="theme-card">
                @csrf
                @method('PUT')

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Folio del lote</label>
                        <input value="{{ $lote->folio }}" class="theme-input" disabled>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Producto</label>
                        <input value="{{ $lote->producto?->nombre_producto ?? 'Producto sin nombre' }}" class="theme-input" disabled>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Fecha de entrega</label>
                        <input value="{{ $lote->entregado_en?->format('Y-m-d') }}" class="theme-input" disabled>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Fecha de caducidad</label>
                        <input type="date" name="fecha_caducidad" value="{{ old('fecha_caducidad', $lote->fecha_caducidad?->format('Y-m-d')) }}" class="theme-input" required>
                        @error('fecha_caducidad')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Proveedor</label>
                        <select name="id_proveedor" class="theme-input">
                            <option value="">Sin proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" @selected((string) old('id_proveedor', $lote->id_proveedor ?? '') === (string) $proveedor->id)>{{ $proveedor->nombre_proveedor }}</option>
                            @endforeach
                        </select>
                        @if($lote->id_pedido !== null)
                            <p class="mt-1 text-xs text-slate-500">Ligado al pedido #{{ $lote->id_pedido }}; puedes ajustarlo.</p>
                        @endif
                        @error('id_proveedor')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Cantidad inicial (histórica)</label>
                        <input value="{{ $lote->stock_lote }} uds." class="theme-input" disabled>
                    </div>

                    <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-300">
                        <span class="font-semibold">Existencia actual:</span> {{ $restanteGlobal }} uds. en todas las sucursales.
                        La cantidad inicial y el restante no se editan aquí: cambian con ventas, traspasos y mermas.
                        @if($lote->anulado_en !== null)
                            <span class="mt-1 block font-semibold text-red-600 dark:text-red-400">Lote anulado el {{ $lote->anulado_en->format('Y-m-d') }}.</span>
                        @endif
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('lotes.index') }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
