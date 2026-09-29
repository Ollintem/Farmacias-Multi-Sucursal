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

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('entradas-de-almacen.index', ['sucursal' => old('id_sucursal', $selectedSucursalId), 'tipo' => 'pedidos']) }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar pedido</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
