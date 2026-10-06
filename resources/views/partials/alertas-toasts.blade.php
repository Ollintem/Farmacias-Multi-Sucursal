{{-- Avisos flash globales: solo éxito/error, abajo a la derecha, se cierran solos. --}}
@php
    $hayFlash = (session('success') || session('error')) && ! request()->routeIs('alertas.*');
@endphp

@if($hayFlash)
    <div class="fixed right-4 bottom-4 z-[90] flex w-80 max-w-[calc(100vw-2rem)] flex-col gap-2" role="status" aria-live="polite">
        @if(session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 8000)" x-show="show" x-transition class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-white px-4 py-3 shadow-xl dark:border-emerald-500/40 dark:bg-[#22353f]">
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                <p class="min-w-0 flex-1 text-sm font-medium">{{ session('success') }}</p>
                <button type="button" @click="show = false" class="shrink-0 font-bold text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-200" aria-label="Cerrar aviso">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 12000)" x-show="show" x-transition class="flex items-start gap-3 rounded-2xl border border-red-200 bg-white px-4 py-3 shadow-xl dark:border-red-500/40 dark:bg-[#22353f]">
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-red-600 text-xs font-bold text-white">!</span>
                <p class="min-w-0 flex-1 text-sm font-medium">{{ session('error') }}</p>
                <button type="button" @click="show = false" class="shrink-0 font-bold text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-200" aria-label="Cerrar aviso">✕</button>
            </div>
        @endif
    </div>
@endif
