<x-layouts::app :title="__('Dashboard')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            @php
                $sucursalId = session('active_sucursal_id') ?? auth()->user()?->id_sucursal;
                $selectedSucursal = $sucursalId ? \App\Models\Sucursal::find($sucursalId) : null;
            @endphp

            {{-- Encabezado limpio --}}
            <header class="mb-6 flex flex-wrap items-end justify-between gap-4" aria-label="Encabezado del dashboard">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Panel general · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Dashboard farmacéutico</h1>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary text-sm !px-3 !py-2">Ver usuarios</a>
                    <a href="{{ route('sucursales.create') }}" class="theme-button theme-button-secondary text-sm !px-3 !py-2">+ Agregar sucursal</a>
                </div>
            </header>

            <div class="grid gap-4 lg:grid-cols-[1.8fr_1fr]">
                <div class="space-y-4">
                    <div class="module-card p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold">Acceso rápido</h2>
                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-600">Superadmin</span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <a href="{{ route('usuarios.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#0c9f9c] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800/60 dark:hover:border-[#0c9f9c]">
                                <p class="text-base font-semibold">Usuarios</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Gestión de personal</p>
                            </a>

                            <a href="#" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#0c9f9c] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800/60 dark:hover:border-[#0c9f9c]">
                                <p class="text-base font-semibold">Inventario</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Productos y stock</p>
                            </a>

                            <a href="#" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#0c9f9c] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800/60 dark:hover:border-[#0c9f9c]">
                                <p class="text-base font-semibold">Sucursales</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Control por tienda</p>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="module-card p-5">
                        <h2 class="text-lg font-semibold">Actividad reciente</h2>
                        <p class="mt-3 rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm theme-subtle dark:border-zinc-600">
                            No hay actividad reciente.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
