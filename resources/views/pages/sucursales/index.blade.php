<x-layouts::app :title="__('Sucursales')">
    <div class="min-h-full bg-[#f3f8f6] px-4 py-5 text-slate-900 dark:bg-[#0b1322] dark:text-white sm:px-6 lg:px-8 lg:py-7">
        <div class="mx-auto flex max-w-[1500px] flex-col gap-6">
            <section class="relative overflow-hidden rounded-[1.75rem] border border-emerald-100 bg-[linear-gradient(120deg,#e5f8ef_0%,#f7fbfa_55%,#e7f1fb_100%)] px-6 py-7 shadow-[0_18px_45px_rgba(15,118,110,0.08)] dark:border-slate-700/70 dark:bg-[linear-gradient(120deg,#123b3a_0%,#142338_65%,#17253e_100%)] sm:px-8">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-600 text-sm font-black text-white shadow-lg shadow-emerald-900/15">Rx</span>
                            <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-700 dark:text-emerald-300">Red de farmacias</p>
                        </div>
                        <h1 class="mt-5 text-3xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-4xl">Sucursales</h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600 dark:text-slate-300">Centraliza la operación de tus farmacias: contacto, responsables, horarios y disponibilidad en un solo lugar.</p>
                    </div>
                    <a href="{{ route('sucursales.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-900/15 transition hover:-translate-y-0.5 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                        <span class="text-lg leading-none">+</span>
                        Nueva sucursal
                    </a>
                </div>
                <div class="absolute -right-10 -top-16 h-48 w-48 rounded-full border-[22px] border-white/35 dark:border-emerald-200/10"></div>
                <div class="absolute -bottom-20 right-28 h-40 w-40 rounded-full border-[18px] border-emerald-400/15"></div>
            </section>

            @if (session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_10px_30px_rgba(15,23,42,0.05)] dark:border-slate-700 dark:bg-slate-900/70">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Sucursales</p>
                            <p class="mt-3 text-3xl font-bold text-slate-950 dark:text-white">{{ $totalSucursales }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Registradas en el sistema</p>
                        </div>
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-sky-100 text-sm font-black text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">01</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-[0_10px_30px_rgba(16,185,129,0.08)] dark:border-emerald-500/30 dark:bg-emerald-500/10">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">Operando</p>
                            <p class="mt-3 text-3xl font-bold text-emerald-900 dark:text-emerald-100">{{ $sucursalesActivas }}</p>
                            <p class="mt-1 text-xs text-emerald-700/75 dark:text-emerald-200/75">Disponibles para la operación</p>
                        </div>
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-600 text-white">✓</span>
                    </div>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-[0_10px_30px_rgba(245,158,11,0.07)] dark:border-amber-500/30 dark:bg-amber-500/10">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">Revisión</p>
                            <p class="mt-3 text-3xl font-bold text-amber-900 dark:text-amber-100">{{ $totalSucursales - $sucursalesActivas }}</p>
                            <p class="mt-1 text-xs text-amber-700/75 dark:text-amber-200/75">Sucursales inactivas</p>
                        </div>
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-amber-400 text-sm font-black text-amber-950">!</span>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-[0_14px_35px_rgba(15,23,42,0.06)] dark:border-slate-700 dark:bg-slate-900/75">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-700">
                    <div>
                        <h2 class="text-base font-bold text-slate-950 dark:text-white">Directorio operativo</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Información vigente de cada punto de atención.</p>
                    </div>
                    <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $totalSucursales }} registros</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[980px] divide-y divide-slate-200 text-left dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/80">
                            <tr>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Sucursal</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Ubicación</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Contacto</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Horario</th>
                                <th class="px-5 py-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Estado</th>
                                <th class="px-5 py-4 text-right text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($sucursales as $sucursal)
                                <tr class="transition hover:bg-emerald-50/60 dark:hover:bg-emerald-500/5">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">{{ strtoupper(substr($sucursal->nombre_sucursal, 0, 1)) }}</span>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $sucursal->nombre_sucursal }}</p>
                                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $sucursal->responsable ?: 'Responsable pendiente' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="max-w-[220px] px-5 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $sucursal->direccion }}</td>
                                    <td class="px-5 py-4 text-sm">
                                        <div class="font-medium text-slate-700 dark:text-slate-200">{{ $sucursal->telefono ?: 'Teléfono pendiente' }}</div>
                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $sucursal->correo_contacto ?: 'Correo pendiente' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        <span class="font-semibold">{{ substr($sucursal->hora_apertura, 0, 5) }}</span>
                                        <span class="mx-1 text-slate-400">a</span>
                                        <span class="font-semibold">{{ substr($sucursal->hora_cierre, 0, 5) }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold {{ $sucursal->es_activa ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $sucursal->es_activa ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $sucursal->es_activa ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('sucursales.edit', $sucursal) }}" class="inline-flex rounded-lg px-3 py-2 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100 hover:text-emerald-900 dark:text-emerald-300 dark:hover:bg-emerald-500/15 dark:hover:text-emerald-200">Editar</a>
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
            </section>
        </div>
    </div>
</x-layouts::app>
