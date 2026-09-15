<x-layouts::app :title="__($tituloModulo ?? 'Módulo')">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">FarmaERP</p>
            <h1 class="mt-2 text-3xl font-bold">{{ $tituloModulo ?? 'Módulo' }}</h1>
            <p class="mt-2 text-sm text-slate-500">Este módulo está habilitado en el catálogo y su pantalla definitiva está en construcción.</p>
        </div>

        <div class="rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-12 text-center">
            <p class="text-sm text-slate-500">Contenido de <span class="font-semibold">{{ $tituloModulo ?? 'este módulo' }}</span> próximamente.</p>
            <a href="{{ route('dashboard') }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Volver al dashboard</a>
        </div>
    </div>
</x-layouts::app>
