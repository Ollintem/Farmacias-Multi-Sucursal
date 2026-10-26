<div wire:poll.30s="cargar" class="flex items-center gap-2">
    @if($esAlertas)
        <span class="hidden xl:inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
            {{ $total }} avisos
        </span>
        <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-bold {{ $noLeidas > 0 ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300' : 'border-slate-200 bg-white text-slate-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400' }}">
            <span class="h-1.5 w-1.5 rounded-full {{ $noLeidas > 0 ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
            {{ $noLeidas }} no leídas
        </span>
        <span class="hidden sm:inline-flex items-center gap-1 rounded-full border border-teal-100 bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-700 dark:border-teal-800 dark:bg-teal-900/20 dark:text-teal-300">
            ⇄ {{ $traspasos }} traspasos
        </span>
        <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-bold {{ $caducidad > 0 ? 'border-red-200 bg-red-50 text-red-600 dark:border-red-800 dark:bg-red-900/15 dark:text-red-300' : 'border-slate-200 bg-white text-slate-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400' }}">
            ● {{ $caducidad }} caducidad
        </span>
    @endif
</div>
