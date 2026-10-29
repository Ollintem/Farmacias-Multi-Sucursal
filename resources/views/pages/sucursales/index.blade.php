<x-layouts::app :title="__('Sucursales')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado --}}
            <header class="mb-6" aria-label="Encabezado de sucursales">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Red de farmacias</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Sucursales</h1>
                        <p class="theme-subtle mt-1 text-sm">Directorio operativo · {{ $totalSucursales }} punto(s) registrado(s)</p>
                    </div>
                    <a href="{{ route('sucursales.create') }}" class="theme-button theme-button-primary whitespace-nowrap">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nueva sucursal
                    </a>
                </div>
            </header>

            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
                    role="status"
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
                    role="alert"
                >
                    {{ session('error') }}
                </div>
            @endif

            {{-- Métricas (móvil: visible solo en < lg, en escritorio viven en el topbar superior) --}}
            @php
                $inactivas = $totalSucursales - $sucursalesActivas;
            @endphp
            <div class="grid gap-3 sm:grid-cols-3 lg:hidden">
                <div class="module-card flex items-center gap-3 !p-4 !shadow-none">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 21V4.5A1.5 1.5 0 015.25 3h13.5A1.5 1.5 0 0120.25 4.5V21M3.75 21h16.5M9 9h6m-6 6h6M9 12h6"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Total</p>
                        <p class="text-xl font-black leading-none tracking-tight text-slate-900 dark:text-white">{{ $totalSucursales }}</p>
                        <p class="theme-subtle text-xs">sucursales</p>
                    </div>
                </div>
                <div class="module-card flex items-center gap-3 !p-4 !shadow-none">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/20">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Activas</p>
                        <p class="text-xl font-black leading-none tracking-tight text-slate-900 dark:text-white">{{ $sucursalesActivas }}</p>
                        <p class="theme-subtle text-xs">operativas</p>
                    </div>
                </div>
                <div @class(['module-card flex items-center gap-3 !p-4 !shadow-none', '!border-amber-200 !bg-amber-50 dark:!border-amber-500/30 dark:!bg-amber-500/10' => $inactivas > 0])>
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/20">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Inactivas</p>
                        <p class="text-xl font-black leading-none tracking-tight text-slate-900 dark:text-white">{{ $inactivas }}</p>
                        <p class="theme-subtle text-xs">ocultas</p>
                    </div>
                    @if($inactivas > 0)
                        <span class="ml-auto h-2 w-2 rounded-full bg-amber-500"></span>
                    @endif
                </div>
            </div>

            <section class="module-card noti-listado" aria-label="Directorio operativo" x-data="buscadorTabla()" x-effect="filtrar($el)">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Directorio operativo</h2>
                        <p class="theme-subtle text-sm">{{ $totalSucursales }} registro(s) · información vigente de cada punto de atención</p>
                    </div>
                </header>

                <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3 dark:border-slate-800 dark:bg-slate-900/30 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative flex-1 sm:max-w-sm">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z"/></svg>
                        <input type="search" placeholder="Buscar sucursal, dirección o teléfono" class="theme-input !py-2.5 !pl-9" aria-label="Buscar sucursal" @input.debounce.200ms="texto = $event.target.value">
                    </div>
                    <p class="theme-subtle hidden text-xs sm:block">Filtra al instante · estado y acciones con estilo unificado</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px] text-left">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-400">
                                <th class="px-5 py-3.5">Sucursal</th>
                                <th class="px-5 py-3.5">Ubicación</th>
                                <th class="px-5 py-3.5">Contacto</th>
                                <th class="px-5 py-3.5">Horario</th>
                                <th class="px-5 py-3.5">Estado</th>
                                <th class="px-5 py-3.5 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($sucursales as $sucursal)
                                <tr class="group transition hover:bg-emerald-50/50 dark:hover:bg-emerald-500/[0.04]" data-buscar="{{ $sucursal->nombre_sucursal }} {{ $sucursal->direccion }} {{ $sucursal->telefono }} {{ $sucursal->correo_contacto }} {{ $sucursal->es_activa ? 'activa' : 'inactiva' }}">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3.5">
                                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-[#0e9384] to-[#0c7569] text-[15px] font-black tracking-tight text-white shadow-md shadow-teal-900/15 ring-1 ring-black/5 transition group-hover:shadow-lg group-hover:shadow-teal-900/20">{{ strtoupper(mb_substr($sucursal->nombre_sucursal, 0, 1)) }}</span>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold leading-none text-slate-900 dark:text-white">{{ $sucursal->nombre_sucursal }}</p>
                                                <p class="theme-subtle mt-1 flex items-center gap-1 truncate text-xs">
                                                    <svg class="h-3 w-3 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                                    ID #{{ $sucursal->id }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="max-w-[260px] px-5 py-4">
                                        <p class="flex items-start gap-1.5 text-sm leading-snug text-slate-700 dark:text-slate-200">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                            <span class="line-clamp-2">{{ $sucursal->direccion }}</span>
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="space-y-1">
                                            <p class="flex items-center gap-1.5 text-sm font-medium text-slate-800 dark:text-slate-100">
                                                <svg class="h-3.5 w-3.5 shrink-0 text-[#0e9384] dark:text-[#3fbda9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                                {{ $sucursal->telefono ?: '—' }}
                                            </p>
                                            <p class="theme-subtle flex items-center gap-1.5 truncate text-xs">
                                                <svg class="h-3.5 w-3.5 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                                {{ $sucursal->correo_contacto ?: 'Correo pendiente' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                            <svg class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ substr($sucursal->hora_apertura, 0, 5) }}<span class="opacity-40">—</span>{{ substr($sucursal->hora_cierre, 0, 5) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($sucursal->es_activa)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/12 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-500/20 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/25">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                <svg class="h-3 w-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Activa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-500/10 px-2.5 py-1 text-xs font-bold text-red-700 ring-1 ring-red-500/20 dark:bg-red-500/15 dark:text-red-300 dark:ring-red-500/25">
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                Inactiva
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('sucursales.edit', $sucursal) }}" title="Editar {{ $sucursal->nombre_sucursal }}" class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 bg-white text-[#0c9f9c] shadow-sm transition hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0c7569] dark:border-slate-700 dark:bg-slate-800 dark:text-[#3fbda9] dark:hover:border-[#3fbda9] dark:hover:bg-emerald-500/10">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span class="sr-only">Editar</span>
                                            </a>
                                            <form action="{{ route('sucursales.destroy', $sucursal) }}" method="POST" class="m-0 inline p-0" onsubmit="return confirm('¿Eliminar {{ $sucursal->nombre_sucursal }}? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar {{ $sucursal->nombre_sucursal }}" class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:border-red-500/30 dark:hover:bg-red-500/10 dark:hover:text-red-300">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span class="sr-only">Eliminar</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-14 text-center">
                                        <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 21V4.5A1.5 1.5 0 015.25 3h13.5A1.5 1.5 0 0120.25 4.5V21M3.75 21h16.5M9 9h6m-6 6h6M9 12h6"/></svg>
                                            </span>
                                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">No hay sucursales registradas</p>
                                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">Crea tu primera sucursal para comenzar a operar la red de farmacias.</p>
                                            <a href="{{ route('sucursales.create') }}" class="theme-button theme-button-primary !py-2 !text-sm">+ Nueva sucursal</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            <tr data-vacio @if($sucursales->isNotEmpty()) style="display: none" @endif>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Sin resultados para esta búsqueda.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    @include('partials.buscador-cliente')
</x-layouts::app>
