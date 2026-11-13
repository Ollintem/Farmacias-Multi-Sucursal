@php
    $rolConPermisos = $conPermisos ?? false;
    $rolId = $id ?? 'id_rol';
@endphp

<div>
    <label for="{{ $rolId }}" class="mb-2 block text-sm font-medium">Rol</label>
    <x-option-pick
        :id="$rolId"
        name="id_rol"
        label="Roles"
        placeholder="Selecciona un rol"
        :options="$roles->pluck('tipo_rol', 'id')"
        :value="$value ?? ''"
    />
    @error('id_rol')
        <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
    @enderror

    @if($rolConPermisos)
        <div x-show="rol !== ''" x-cloak class="mt-2 flex flex-wrap items-center gap-2">
            <span class="text-xs text-slate-500 dark:text-zinc-400" x-text="resumenPermisos()"></span>
            <button type="button" @click="abierto = true" class="text-xs font-semibold text-[#0c9f9c] underline hover:text-[#0a8582]">Ver / editar permisos</button>
        </div>
    @endif
</div>
