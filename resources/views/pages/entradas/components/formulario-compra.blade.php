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

        @include('pages.entradas.components.campo-sucursal-fija', [
            'etiqueta' => 'Sucursal destino',
            'campo' => 'id_sucursal',
            'valor' => $selectedSucursalId ?? '',
            'nombreSucursal' => $sucursales->firstWhere('id', $selectedSucursalId)?->nombre_sucursal ?? 'Sin sucursal',
            'ayuda' => 'El destino es tu sucursal activa y solo cambia desde el menú lateral.',
            'errorField' => 'id_sucursal',
        ])
    </div>

    @include('pages.entradas.components.lotes-compra', ['productos' => $productos])

    @include('pages.entradas.components.acciones-formulario', [
        'cancelRoute' => route('entradas-de-almacen.index', ['sucursal' => old('id_sucursal', $selectedSucursalId), 'tipo' => 'compras']),
        'submitLabel' => 'Guardar compra',
    ])
</form>
