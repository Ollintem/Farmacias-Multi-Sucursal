<x-layouts::app :title="__('Sucursales')">
    <div class="module-page">
        <div class="module-page-inner">
            <div class="module-hero">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="farma-kicker">Red de farmacias</p>
                        <h1 class="mt-2 text-3xl font-bold tracking-tight">Sucursales</h1>
                        <p class="theme-subtle mt-1 text-sm">Contacto, responsables, horarios y disponibilidad en un solo lugar.</p>
                    </div>
                    <a href="{{ route('sucursales.create') }}" class="theme-button theme-button-primary whitespace-nowrap">+ Nueva sucursal</a>
                </div>
            </div>

            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
                >
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
                >
                    {{ session('error') }}
                </div>
            @endif

            <section class="grid gap-4 md:grid-cols-3">
                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Sucursales</span>
                        <span class="stat-pill info">{{ $totalSucursales }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $totalSucursales }}</p>
                    <p class="mt-1 text-xs theme-subtle">Registradas en el sistema</p>
                </div>
                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Operando</span>
                        <span class="stat-pill positive">{{ $sucursalesActivas }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $sucursalesActivas }}</p>
                    <p class="mt-1 text-xs theme-subtle">Disponibles para la operación</p>
                </div>
                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Revisión</span>
                        <span class="stat-pill warning">{{ $totalSucursales - $sucursalesActivas }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $totalSucursales - $sucursalesActivas }}</p>
                    <p class="mt-1 text-xs theme-subtle">Sucursales inactivas</p>
                </div>
            </section>

            <section class="module-card !p-0">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-700">
                    <div>
                        <h2 class="text-lg font-bold">Directorio operativo</h2>
                        <p class="theme-subtle mt-1 text-sm">Información vigente de cada punto de atención.</p>
                    </div>
                    <span class="theme-badge">{{ $totalSucursales }} registros</span>
                </div>
                <div class="module-table !rounded-none !border-0 !shadow-none">
                    <div class="overflow-x-auto">
                        <table class="min-w-[980px] text-left text-sm">
                            <thead class="text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-5 py-4 font-semibold">Sucursal</th>
                                    <th class="px-5 py-4 font-semibold">Ubicación</th>
                                    <th class="px-5 py-4 font-semibold">Contacto</th>
                                    <th class="px-5 py-4 font-semibold">Horario</th>
                                    <th class="px-5 py-4 font-semibold">Estado</th>
                                    <th class="px-5 py-4 text-right font-semibold">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sucursales as $sucursal)
                                    <tr class="border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5">
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-[#0e9384] to-[#0c7569] text-sm font-black text-white shadow">{{ strtoupper(substr($sucursal->nombre_sucursal, 0, 1)) }}</span>
                                                <div>
                                                    <p class="font-bold text-slate-800 dark:text-slate-100">{{ $sucursal->nombre_sucursal }}</p>
                                                    <p class="mt-0.5 text-xs theme-subtle">{{ $sucursal->responsable ?: 'Responsable pendiente' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="max-w-[220px] px-5 py-4 text-slate-600 dark:text-slate-300">{{ $sucursal->direccion }}</td>
                                        <td class="px-5 py-4">
                                            <div class="font-medium text-slate-700 dark:text-slate-200">{{ $sucursal->telefono ?: 'Teléfono pendiente' }}</div>
                                            <div class="mt-1 text-xs theme-subtle">{{ $sucursal->correo_contacto ?: 'Correo pendiente' }}</div>
                                        </td>
                                        <td class="px-5 py-4 text-slate-600 dark:text-slate-300">
                                            <span class="font-semibold">{{ substr($sucursal->hora_apertura, 0, 5) }}</span>
                                            <span class="mx-1 theme-subtle">a</span>
                                            <span class="font-semibold">{{ substr($sucursal->hora_cierre, 0, 5) }}</span>
                                        </td>
                                        <td class="px-5 py-4">
                                            @if($sucursal->es_activa)
                                                <span class="inventory-status inventory-status-available">Activa</span>
                                            @else
                                                <span class="inventory-status inventory-status-low">Inactiva</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <a href="{{ route('sucursales.edit', $sucursal) }}" class="theme-button theme-button-secondary !min-h-0 !px-4 !py-2 !text-xs">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-14 text-center text-sm text-slate-500 dark:text-slate-400">No hay sucursales registradas aún.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
