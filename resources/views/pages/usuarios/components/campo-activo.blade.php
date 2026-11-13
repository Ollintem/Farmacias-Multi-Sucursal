@php
    $activoMarcado = $checked ?? true;
@endphp

<div class="mt-6 flex items-center gap-2">
    <input type="hidden" name="es_activo" value="0">
    <input id="es_activo" type="checkbox" name="es_activo" value="1" @checked($activoMarcado) class="h-4 w-4 rounded border-slate-300 text-[#0c9f9c] focus:ring-[#0c9f9c]">
    <label for="es_activo" class="text-sm">Usuario activo</label>
</div>
