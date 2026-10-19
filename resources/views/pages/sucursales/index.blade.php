<x-layouts::app :title="__('Sucursales')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado homologado: hero verde + KPIs blancos --}}
            <section class="module-card noti-hero" aria-label="Encabezado de sucursales">
                <div class="noti-hero-fondo" aria-hidden="true"></div>
                <div class="noti-encabezado-texto noti-hero-texto">
                    <p class="noti-hero-kicker"><span class="noti-hero-dot"></span> Red de farmacias</p>
                    <h1 class="mt-2">Sucursales</h1>
                    <dl class="noti-resumen noti-kpis">
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg> Sucursales</dt><dd>{{ $totalSucursales }}</dd>
                        </div>
                        <div class="noti-kpi">
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Operando</dt><dd>{{ $sucursalesActivas }}</dd>
                        </div>
                        <div @class(['noti-kpi', 'noti-kpi-alerta' => ($totalSucursales - $sucursalesActivas) > 0])>
                            <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg> Revisión</dt><dd>{{ $totalSucursales - $sucursalesActivas }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('sucursales.create') }}" class="theme-button theme-button-primary whitespace-nowrap">+ Nueva sucursal</a>
                    </div>
                </div>
            </section>

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

            <section class="module-card noti-listado" aria-label="Directorio operativo">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Directorio operativo</h2>
                        <p class="theme-subtle text-sm">{{ $totalSucursales }} registro(s) · información vigente de cada punto de atención</p>
                    </div>
                </header>
                <div>
                <div class="module-table !rounded-none !border-0 !shadow-none">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] text-left text-[15px]">
                            <thead class="text-slate-600 dark:text-slate-300">
                                <tr>
                                    <th class="px-6 py-4 text-base font-semibold">Sucursal</th>
                                    <th class="px-6 py-4 text-base font-semibold">Ubicación</th>
                                    <th class="px-6 py-4 text-base font-semibold">Contacto</th>
                                    <th class="px-6 py-4 text-base font-semibold">Horario</th>
                                    <th class="px-6 py-4 text-base font-semibold">Estado</th>
                                    <th class="px-6 py-4 text-right text-base font-semibold">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sucursales as $sucursal)
                                    <tr class="border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5">
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-4">
                                                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-[#0e9384] to-[#0c7569] text-xl font-black text-white shadow-md shadow-teal-900/20">{{ strtoupper(substr($sucursal->nombre_sucursal, 0, 1)) }}</span>
                                                <div class="min-w-0">
                                                    <p class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $sucursal->nombre_sucursal }}</p>
                                                    <p class="theme-subtle mt-1 text-sm">{{ $sucursal->responsable ?: 'Responsable pendiente' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="max-w-[260px] px-6 py-5 text-[15px] text-slate-600 dark:text-slate-300">{{ $sucursal->direccion }}</td>
                                        <td class="px-6 py-5">
                                            <div class="text-base font-medium text-slate-700 dark:text-slate-200">{{ $sucursal->telefono ?: 'Teléfono pendiente' }}</div>
                                            <div class="theme-subtle mt-1.5 text-sm">{{ $sucursal->correo_contacto ?: 'Correo pendiente' }}</div>
                                        </td>
                                        <td class="px-6 py-5 text-[15px] text-slate-600 dark:text-slate-300">
                                            <span class="font-semibold">{{ substr($sucursal->hora_apertura, 0, 5) }}</span>
                                            <span class="theme-subtle mx-1">a</span>
                                            <span class="font-semibold">{{ substr($sucursal->hora_cierre, 0, 5) }}</span>
                                        </td>
                                        <td class="px-6 py-5">
                                            @if($sucursal->es_activa)
                                                <span class="inventory-status inventory-status-available !px-4 !py-1.5 !text-sm">Activa</span>
                                            @else
                                                <span class="inventory-status inventory-status-low !px-4 !py-1.5 !text-sm">Inactiva</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('sucursales.edit', $sucursal) }}" class="theme-button theme-button-secondary !min-h-0 !px-5 !py-2.5 !text-sm">Editar</a>
                                                <form action="{{ route('sucursales.destroy', $sucursal) }}" method="POST" class="m-0 inline p-0" onsubmit="return confirm('¿Eliminar {{ $sucursal->nombre_sucursal }}? Esta acción no se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="theme-button !min-h-0 !border-red-200 !bg-red-50 !px-5 !py-2.5 !text-sm !text-red-700 hover:!bg-red-100 hover:!text-red-800 dark:!border-red-500/30 dark:!bg-red-500/10 dark:!text-red-300 dark:hover:!bg-red-500/20">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-14 text-center text-base text-slate-500 dark:text-slate-400">No hay sucursales registradas aún.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
