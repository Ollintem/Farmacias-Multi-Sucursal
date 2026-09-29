<x-layouts::app :title="__('Nuevo traspaso')">
    <div id="theme-shell">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Almacén</p>
                    <h1 class="mt-2 text-3xl font-bold">Registrar nuevo traspaso</h1>
                    <p class="mt-1 text-sm theme-subtle">Movimiento de mercancía entre dos sucursales distintas.</p>
                </div>
            </div>

            <form action="{{ route('traspasos.store') }}" method="POST" class="theme-card">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Sucursal origen</label>
                        <x-option-pick name="sucursal_a" label="Sucursal origen" placeholder="Selecciona el origen" :options="$sucursales->pluck('nombre_sucursal', 'id')" />
                        @error('sucursal_a')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Sucursal destino</label>
                        <x-option-pick name="sucursal_b" label="Sucursal destino" placeholder="Selecciona el destino" :options="$sucursales->pluck('nombre_sucursal', 'id')" :value="old('sucursal_b', $selectedSucursalId ?? '')" />
                        @error('sucursal_b')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Estado inicial</label>
                        <select name="estado" class="theme-input">
                            <option value="pendiente" {{ old('estado', 'pendiente') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="enviado" {{ old('estado') === 'enviado' ? 'selected' : '' }}>Enviado</option>
                            <option value="recibido" {{ old('estado') === 'recibido' ? 'selected' : '' }}>Recibido</option>
                            <option value="cancelado" {{ old('estado') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                        @error('estado')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('entradas-de-almacen.index', ['sucursal' => old('sucursal_b', $selectedSucursalId), 'tipo' => 'traspasos']) }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Guardar traspaso</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
