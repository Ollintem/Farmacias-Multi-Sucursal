{{-- Aviso del cierre de turno: se refresca solo cada minuto. --}}
<div wire:poll.60s="verificar">
    @if($aviso)
        <div @class([
            'mb-3 flex flex-wrap items-center justify-between gap-3 rounded-2xl border px-4 py-3 shadow-sm',
            'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10' => $aviso['tono'] === 'amber',
            'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' => $aviso['tono'] === 'red',
        ]) role="status">
            <div class="flex items-center gap-3">
                <span @class([
                    'grid h-9 w-9 shrink-0 place-items-center rounded-xl text-base font-black text-white',
                    'bg-amber-500' => $aviso['tono'] === 'amber',
                    'bg-red-600' => $aviso['tono'] === 'red',
                ]) aria-hidden="true">⏱</span>
                <div>
                    <p @class([
                        'text-sm font-black',
                        'text-amber-800 dark:text-amber-200' => $aviso['tono'] === 'amber',
                        'text-red-800 dark:text-red-200' => $aviso['tono'] === 'red',
                    ])>{{ $aviso['titulo'] }}</p>
                    <p @class([
                        'text-xs font-medium',
                        'text-amber-700 dark:text-amber-300' => $aviso['tono'] === 'amber',
                        'text-red-700 dark:text-red-300' => $aviso['tono'] === 'red',
                    ])>{{ $aviso['detalle'] }}</p>
                </div>
            </div>

            <button
                type="button"
                wire:click="pedirCierre"
                class="theme-button theme-button-warning !min-h-0 !px-4 !py-2 !text-xs shrink-0 whitespace-nowrap"
            >
                Cerrar mi caja
            </button>
        </div>
    @endif
</div>
