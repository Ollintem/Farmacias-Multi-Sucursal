<x-layouts::app :title="__('Dashboard')">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="farma-kicker">Panel general · Salud y confianza</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight">Dashboard farmacéutico</h1>
                    <p class="theme-subtle mt-1 text-sm">Teal para higiene y calma, petróleo para respaldo médico profesional.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-primary">Ver usuarios</a>
                    <a href="{{ route('usuarios.create') }}" class="theme-button theme-button-secondary">Nuevo usuario</a>
                </div>
            </div>



            @php
                $sucursalId = session('active_sucursal_id') ?? auth()->user()?->id_sucursal;
                $selectedSucursal = $sucursalId ? \App\Models\Sucursal::find($sucursalId) : null;
            @endphp

            <div class="module-hero">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $selectedSucursal?->nombre_sucursal ?? 'Centro Histórico' }}</h2>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <a href="{{ route('sucursales.create') }}" class="theme-button theme-button-secondary whitespace-nowrap">+ Agregar sucursal</a>
                    </div>
                </div>
                
                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    <div class="module-stat">

                        <div class="flex items-center justify-between">
                            <span class="theme-subtle text-sm">Ventas</span>
                            <span class="stat-pill positive">+12%</span>
                        </div>
                        <p id="stat-ventas" class="mt-4 text-2xl font-bold">Sin datos</p>
                        <p class="mt-1 text-xs theme-subtle">Aún no disponible</p>
                    </div>

                    <div class="module-stat">
                        <div class="flex items-center justify-between">
                            <span class="theme-subtle text-sm">Clientes</span>
                            <span class="stat-pill info">Pendiente</span>
                        </div>
                        <p id="stat-clientes" class="mt-4 text-2xl font-bold">Sin datos</p>
                        <p class="mt-1 text-xs theme-subtle">Aún no disponible</p>
                    </div>

                    <div class="module-stat">
                        <div class="flex items-center justify-between">
                            <span class="theme-subtle text-sm">Stock</span>
                            <span class="stat-pill warning">Pendiente</span>
                        </div>
                        <p id="stat-stock" class="mt-4 text-2xl font-bold">Sin datos</p>
                        <p class="mt-1 text-xs theme-subtle">Aún no disponible</p>
                    </div>
                </div>
            </div>

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
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Sin datos</span>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm text-slate-600">Ventas en línea</p>
                                <p class="mt-2 text-2xl font-bold text-slate-400">Sin datos</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm text-slate-600">Pedidos pendientes</p>
                                <p class="mt-2 text-2xl font-bold text-slate-400">Sin datos</p>
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
