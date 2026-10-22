<x-layouts::app :title="__('Sucursales')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado limpio --}}
            <header class="mb-6" aria-label="Encabezado de sucursales">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Red de farmacias</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Sucursales</h1>
                    </div>
                    <a href="{{ route('sucursales.create') }}" class="theme-button theme-button-primary whitespace-nowrap">+ Nueva sucursal</a>
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
                <div class="module-table !rounded-none !border-0 !shadow-none overflow-hidden">
                    <div class="max-h-[380px] overflow-y-auto overflow-x-auto overscroll-contain scrollbar-thin">
                        <table class="w-full min-w-[900px] text-left text-[15px]">
                            <thead class="sticky top-0 z-10 bg-slate-50 text-slate-600 shadow-[inset_0_-1px_0_#e2e8f0] dark:bg-zinc-800 dark:text-slate-300 dark:shadow-[inset_0_-1px_0_#3f3f46]">
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
                                            <span
                                                class="inline-flex items-center gap-2"
                                                x-data="{
                                                    activa: {{ $sucursal->es_activa ? 'true' : 'false' }},
                                                    cambiar(el) {
                                                        if (el.checked === this.activa) return;

                                                        if (this.activa) {
                                                            queueMicrotask(() => { el.checked = true; });
                                                            this.$dispatch('confirmar-accion', { modo: el.dataset.modo, ruta: el.dataset.ruta, nombre: el.dataset.nombre, etiqueta: 'sucursal', campo: 'es_activa', detalle: el.dataset.detalle });
                                                        } else {
                                                            this.activa = true;
                                                            this.$nextTick(() => el.closest('form').submit());
                                                        }
                                                    },
                                                }"
                                                title="Clic para {{ $sucursal->es_activa ? 'desactivar' : 'activar' }} la sucursal"
                                            >
                                                <form action="{{ route('sucursales.estado', $sucursal) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="es_activa" :value="activa ? 1 : 0">
                                                    <flux:switch
                                                        :checked="$sucursal->es_activa"
                                                        class="cursor-pointer"
                                                        aria-label="Estado de {{ $sucursal->nombre_sucursal }}"
                                                        data-modo="desactivar"
                                                        data-ruta="{{ route('sucursales.estado', $sucursal) }}"
                                                        data-nombre="{{ $sucursal->nombre_sucursal }}"
                                                        data-detalle="Dejará de aparecer en los selectores de sucursal; sus ventas e historial se conservan."
                                                        @change="cambiar($event.target)"
                                                    />
                                                </form>
                                                <span
                                                    class="text-sm font-semibold"
                                                    :class="activa ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400'"
                                                    x-text="activa ? 'Activa' : 'Inactiva'"
                                                >{{ $sucursal->es_activa ? 'Activa' : 'Inactiva' }}</span>
                                            </span>
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

    @include('partials.confirmacion-accion')
</x-layouts::app>
