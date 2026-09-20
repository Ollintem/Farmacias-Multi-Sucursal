<x-layouts::app :title="__($tituloModulo ?? 'Módulo')">
    <div class="module-page">
        <div class="module-page-inner">
            <section class="module-hero">
                <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-600 text-sm font-black text-white">Rx</span>
                            <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-700 dark:text-emerald-300">FarmaERP · Próximamente</p>
                        </div>
                        <h1 class="mt-5 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $tituloModulo ?? 'Módulo' }}</h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600 dark:text-slate-300">Este espacio ya está preparado dentro de la operación de la farmacia. Su flujo completo se habilitará en una siguiente etapa.</p>
                    </div>
                    <span class="w-fit rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">En preparación</span>
                </div>
                <div class="absolute -right-10 -top-16 h-48 w-48 rounded-full border-[22px] border-white/35 dark:border-emerald-200/10"></div>
            </section>

            <section class="module-card overflow-hidden">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                    <h2 class="text-base font-bold text-slate-950 dark:text-white">Área de trabajo</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">La navegación ya está disponible para mantener una experiencia consistente.</p>
                </div>
                <div class="grid gap-4 px-6 py-10 text-center sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                        <span class="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-sky-100 text-sm font-black text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">01</span>
                        <p class="mt-3 text-sm font-bold text-slate-800 dark:text-white">Diseño preparado</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Listo para el flujo del módulo.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                        <span class="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">02</span>
                        <p class="mt-3 text-sm font-bold text-slate-800 dark:text-white">Menú conectado</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">La ruta ya forma parte del panel.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                        <span class="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-amber-100 text-sm font-black text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">03</span>
                        <p class="mt-3 text-sm font-bold text-slate-800 dark:text-white">Lógica pendiente</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Se conectará a la base de datos.</p>
                    </div>
                </div>
                <div class="border-t border-slate-200 px-6 py-5 dark:border-slate-700">
                    <a href="{{ route('dashboard') }}" class="theme-button theme-button-primary">Volver al dashboard <span class="ml-2">→</span></a>
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
