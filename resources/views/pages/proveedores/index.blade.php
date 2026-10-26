<x-layouts::app :title="__('Proveedores')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado limpio --}}
            <header class="mb-6" aria-label="Encabezado de proveedores">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Catálogo de suministro</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Proveedores</h1>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('proveedores.create') }}" class="theme-button theme-button-primary">+ Nuevo proveedor</a>
                    </div>
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

            <section class="module-card noti-listado" aria-label="Directorio de proveedores" x-data="buscadorTabla()" x-effect="filtrar($el)">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Directorio de proveedores</h2>
                        <p class="theme-subtle text-sm">{{ $totalProveedores }} registro(s) · información vigente de cada proveedor</p>
                    </div>
                </header>
                <div class="flex px-5 py-4 sm:px-6">
                    <input type="search" placeholder="Buscar proveedor, correo o teléfono" class="theme-input w-full sm:max-w-sm" aria-label="Buscar proveedor" @input.debounce.200ms="texto = $event.target.value">
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[1080px] text-left text-[15px]">
                        <thead class="text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="px-6 py-4 text-base font-semibold">Proveedor</th>
                                <th class="px-6 py-4 text-base font-semibold">Unidad de entrega</th>
                                <th class="px-6 py-4 text-base font-semibold">Contacto</th>
                                <th class="px-6 py-4 text-base font-semibold">Estado</th>
                                <th class="px-6 py-4 text-right text-base font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($proveedores as $proveedor)
                                @php($contactoCompleto = filled($proveedor->telefono) && filled($proveedor->correo))
                                <tr class="group border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5" data-buscar="{{ $proveedor->nombre_proveedor }} {{ $proveedor->correo }} {{ $proveedor->telefono }} {{ $proveedor->unidad_entrega }} {{ $proveedor->direccion }}">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            @if($proveedor->fotoUrl())
                                                <img src="{{ $proveedor->fotoUrl() }}" alt="Foto de {{ $proveedor->nombre_proveedor }}" class="h-14 w-14 shrink-0 rounded-2xl border border-slate-200 object-cover shadow-md ring-2 ring-transparent transition group-hover:ring-[#0e9384]/40 dark:border-slate-700">
                                            @else
                                                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-[#0e9384] to-[#0c7569] text-xl font-black text-white shadow-md shadow-teal-900/20 ring-2 ring-transparent transition group-hover:ring-[#0e9384]/40">{{ strtoupper(mb_substr($proveedor->nombre_proveedor ?? '?', 0, 1)) }}</span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $proveedor->nombre_proveedor }}</p>
                                                <p class="theme-subtle mt-1 max-w-[300px] truncate text-sm" title="{{ $proveedor->direccion }}">{{ $proveedor->direccion }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="stat-pill positive !px-4 !py-1.5 !text-sm">{{ $proveedor->unidad_entrega }}</span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="flex items-center gap-2 text-base font-semibold text-slate-800 dark:text-slate-100">
                                            <svg class="h-4 w-4 text-[#0e9384] dark:text-[#3fbda9]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                            {{ $proveedor->telefono ?: 'Teléfono pendiente' }}
                                        </p>
                                        <p class="theme-subtle mt-1.5 flex items-center gap-2 text-sm">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                            {{ $proveedor->correo ?: 'Correo pendiente' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-5">
                                        @if(! $proveedor->es_activo)
                                            <span class="stat-pill info">Inactivo</span>
                                        @elseif($contactoCompleto)
                                            <span class="stat-pill positive !px-4 !py-1.5 !text-sm">Completo</span>
                                        @else
                                            <span class="stat-pill warning !px-4 !py-1.5 !text-sm">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('proveedores.edit', $proveedor) }}" class="theme-button theme-button-secondary !min-h-0 !px-5 !py-2.5 !text-sm">Editar</a>
                                            <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" class="m-0 inline p-0" onsubmit="return confirm('¿Eliminar a {{ $proveedor->nombre_proveedor }}? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="theme-button !min-h-0 !border-red-200 !bg-red-50 !px-5 !py-2.5 !text-sm !text-red-700 hover:!bg-red-100 hover:!text-red-800 dark:!border-red-500/30 dark:!bg-red-500/10 dark:!text-red-300 dark:hover:!bg-red-500/20">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="theme-subtle px-5 py-14 text-center text-sm">No hay proveedores registrados aún.</td>
                                </tr>
                            @endforelse
                            <tr data-vacio @if($proveedores->isNotEmpty()) style="display: none" @endif>
                                <td colspan="5" class="theme-subtle px-5 py-10 text-center text-sm">Sin resultados para esta búsqueda.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    @include('partials.buscador-cliente')
</x-layouts::app>
