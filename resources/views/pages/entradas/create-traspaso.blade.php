<x-layouts::app :title="__('Nuevo traspaso')">
    <div id="theme-shell">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Almacén</p>
                    <h1 class="mt-2 text-3xl font-bold">Registrar nuevo traspaso</h1>
                    <p class="mt-1 text-sm theme-subtle">Solicita producto a otra sucursal: llegará como alerta para aceptar o rechazar.</p>
                </div>
            </div>

            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('traspasos.store') }}" method="POST" class="theme-card">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Sucursal origen</label>
                        <x-option-pick name="sucursal_a" label="Sucursal origen" placeholder="Selecciona el origen" :options="$sucursales->pluck('nombre_sucursal', 'id')" :value="old('sucursal_a')" />
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
                        <label class="mb-2 block text-sm font-medium">Producto</label>
                        <select name="id_producto" class="theme-input" required>
                            <option value="">Selecciona el producto</option>
                            @foreach($productos ?? [] as $producto)
                                <option value="{{ $producto->id }}" {{ (string) old('id_producto') === (string) $producto->id ? 'selected' : '' }}>
                                    {{ $producto->nombre_producto }} ({{ $producto->stock }} uds. global)
                                </option>
                            @endforeach
                        </select>
                        @error('id_producto')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Cantidad</label>
                        <input type="number" name="cantidad" min="1" max="10000" value="{{ old('cantidad', 1) }}" class="theme-input" required>
                        @error('cantidad')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Mensaje para la sucursal destino</label>
                        <textarea name="mensaje" rows="3" maxlength="1000" placeholder="Ej. Urgente: necesitamos 20 uds. para cubrir la venta del fin de semana." class="theme-input">{{ old('mensaje') }}</textarea>
                        @error('mensaje')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Estado inicial</label>
                        <select name="estado" class="theme-input">
                            <option value="pendiente" {{ old('estado', 'pendiente') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="enviado" {{ old('estado') === 'enviado' ? 'selected' : '' }}>Enviado</option>
                        </select>
                        @error('estado')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('alertas.index', ['sucursal' => old('sucursal_b', $selectedSucursalId), 'filtro' => 'traspasos']) }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Enviar solicitud</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
