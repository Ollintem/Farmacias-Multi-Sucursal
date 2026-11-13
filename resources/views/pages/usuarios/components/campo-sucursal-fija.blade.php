<div>
    <label for="id_sucursal" class="mb-2 block text-sm font-medium">Sucursal</label>
    <input id="id_sucursal" type="hidden" name="id_sucursal" value="{{ $idSucursal }}">
    <p class="theme-input flex items-center font-semibold" aria-readonly="true">{{ $nombreSucursal }}</p>
    <p class="mt-1 text-xs theme-subtle">Es la sucursal del usuario y solo se asigna al crearlo.</p>
    @error('id_sucursal')
        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
    @enderror
</div>
