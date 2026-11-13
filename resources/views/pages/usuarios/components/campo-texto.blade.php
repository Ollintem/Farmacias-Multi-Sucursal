@php
    $campoNombre = $name;
    $campoId = $id ?? $name;
    $campoTipo = $type ?? 'text';
    $campoValor = $value ?? '';
    $campoEtiqueta = $label ?? ucfirst($campoNombre);
    $campoRequerido = $required ?? false;
    $campoMaxlength = $maxlength ?? null;
    $campoAutocomplete = $autocomplete ?? null;
@endphp

<div>
    <label for="{{ $campoId }}" class="mb-2 block text-sm font-medium">{{ $campoEtiqueta }}</label>
    <input
        id="{{ $campoId }}"
        name="{{ $campoNombre }}"
        type="{{ $campoTipo }}"
        value="{{ $campoValor }}"
        class="theme-input"
        @if($campoMaxlength !== null) maxlength="{{ $campoMaxlength }}" @endif
        @if($campoAutocomplete !== null) autocomplete="{{ $campoAutocomplete }}" @endif
        @if($campoRequerido) required @endif
    >
    @error($campoNombre)
        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
    @enderror
</div>
