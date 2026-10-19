<x-layouts::app :title="__('Dashboard')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            @php
                $sucursalId = session('active_sucursal_id') ?? auth()->user()?->id_sucursal;
                $selectedSucursal = $sucursalId ? \App\Models\Sucursal::find($sucursalId) : null;
            @endphp

            {{-- Encabezado homologado: hero verde + KPIs blancos --}}
            <section class="module-card noti-hero" aria-label="Encabezado del dashboard">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Panel general · {{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    <h1 class="mt-2">Dashboard farmacéutico</h1>
                    <dl class="noti-resumen noti-kpis">
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .298-.119.585-.33.795a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0l-.09-.092a1.125 1.125 0 00-1.591 0l-.09.092a.75.75 0 01-1.08 0Z"/></svg> Ventas</dt><dd>Sin datos</dd>
                        </div>
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg> Clientes</dt><dd>Sin datos</dd>
                        </div>
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg> Stock</dt><dd>Sin datos</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-primary">Ver usuarios</a>
                        <a href="{{ route('sucursales.create') }}" class="theme-button theme-button-secondary">+ Agregar sucursal</a>
                    </div>
                </div>
            </section>

            <div class="grid gap-4 lg:grid-cols-[1.8fr_1fr]">
                <div class="space-y-4">
                    <div class="module-card p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold">Acceso rápido</h2>
                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-600">Superadmin</span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <a href="{{ route('usuarios.index') }}" class="rounded-2xl border border-[#cfddd7] bg-[#eef5f2] p-4 transition hover:-translate-y-0.5 hover:border-[#0e9384] hover:bg-[#dcebe5] dark:border-[#33505c] dark:bg-[#1e3039] dark:hover:border-[#3fbda9]">
                                <p class="text-base font-semibold">Usuarios</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Gestión de personal</p>
                            </a>

                            <a href="#" class="rounded-2xl border border-[#bccfdd] bg-[#e8eff3] p-4 transition hover:-translate-y-0.5 hover:border-[#2a7fa0] hover:bg-[#d7e5e8] dark:border-[#33505c] dark:bg-[#1e3039] dark:hover:border-[#2a7fa0]">
                                <p class="text-base font-semibold">Inventario</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Productos y stock</p>
                            </a>

                            <a href="#" class="rounded-2xl border border-[#d8cfae] bg-[#f3efe2] p-4 transition hover:-translate-y-0.5 hover:border-[#b7791f] hover:bg-[#ece5cf] dark:border-[#33505c] dark:bg-[#1e3039] dark:hover:border-[#b7791f]">
                                <p class="text-base font-semibold">Sucursales</p>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">Control por tienda</p>
                            </a>
                        </div>
                    </div>

                    <div class="module-card p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="theme-subtle text-sm">Resumen de operación</p>
                                <h3 class="mt-1 text-xl font-bold">Farmacia hoy</h3>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">Sin datos</span>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                                <p class="text-sm font-medium text-slate-700 dark:text-zinc-300">Ventas en línea</p>
                                <p class="mt-2 text-2xl font-bold text-slate-500 dark:text-zinc-400">Sin datos</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                                <p class="text-sm font-medium text-slate-700 dark:text-zinc-300">Pedidos pendientes</p>
                                <p class="mt-2 text-2xl font-bold text-slate-500 dark:text-zinc-400">Sin datos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="module-card p-5">
                        <h2 class="text-lg font-semibold">Estado del sistema</h2>

                        <div class="mt-5 space-y-4">
                            <p class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm theme-subtle">
                                Aún no hay información del sistema para mostrar.
                            </p>
                        </div>
                    </div>

                    <div class="module-card p-5">
                        <h2 class="text-lg font-semibold">Actividad reciente</h2>
                        <ul class="mt-4 space-y-3">
                            <li class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm theme-subtle">
                                No hay actividad reciente.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
