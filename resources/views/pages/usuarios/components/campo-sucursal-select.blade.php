<div>
    <label for="id_sucursal" class="mb-2 block text-sm font-medium">Sucursal</label>
    <x-option-pick
        id="id_sucursal"
        name="id_sucursal"
        label="Sucursales"
        placeholder="Selecciona una sucursal"
        :options="$sucursales->pluck('nombre_sucursal', 'id')"
        :value="$value ?? ''"
    />
    @error('id_sucursal')
        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
    @enderror
</div>
