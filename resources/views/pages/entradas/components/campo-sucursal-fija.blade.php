@php
    $campoError = $errorField ?? null;
@endphp

<div>
    <label class="mb-2 block text-sm font-medium">{{ $etiqueta }}</label>
    <input type="hidden" name="{{ $campo }}" value="{{ $valor }}">
    <p class="theme-input flex items-center font-semibold" aria-readonly="true">{{ $nombreSucursal }}</p>
    <p class="mt-1 text-xs theme-subtle">{{ $ayuda }}</p>
    @if($campoError)
        @error($campoError)
            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
        @enderror
    @endif
</div>
