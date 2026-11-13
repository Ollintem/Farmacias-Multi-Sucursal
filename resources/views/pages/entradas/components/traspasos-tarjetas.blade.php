<div class="grid gap-4 md:grid-cols-2">
    <button type="button" @click="traspasoModal = 'solicitudes'" class="group flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50/70 p-5 text-left shadow-sm transition hover:border-amber-300 hover:bg-amber-50 hover:shadow-md dark:border-amber-500/20 dark:bg-amber-500/10 dark:hover:bg-amber-500/15 text-slate-900 dark:text-white">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/20">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-bold leading-none">Solicitudes de traspaso</p>
            <p class="theme-subtle mt-1 text-xs leading-snug">Lo que otras sucursales solicitan a {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }}.</p>
        </div>
        <span class="grid h-8 min-w-[2rem] place-items-center rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">{{ ($solicitudes ?? collect())->count() }}</span>
        <svg class="h-4 w-4 shrink-0 text-slate-400 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
    </button>

    <button type="button" @click="traspasoModal = 'solicitados'" class="group flex items-center gap-4 rounded-2xl border border-sky-200 bg-sky-50/70 p-5 text-left shadow-sm transition hover:border-sky-300 hover:bg-sky-50 hover:shadow-md dark:border-sky-500/20 dark:bg-sky-500/10 dark:hover:bg-sky-500/15 text-slate-900 dark:text-white">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-700 ring-1 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-500/20">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 7.5H7.5m0 13.5L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-bold leading-none">Traspasos<span> </span>solicitados</p>
            <p class="theme-subtle mt-1 text-xs leading-snug">Lo que {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }} solicitó a otras sucursales.</p>
        </div>
        <span class="grid h-8 min-w-[2rem] place-items-center rounded-xl border border-slate-200 bg-white px-2 text-sm font-black text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">{{ ($solicitados ?? collect())->count() }}</span>
        <svg class="h-4 w-4 shrink-0 text-slate-400 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
    </button>
</div>
