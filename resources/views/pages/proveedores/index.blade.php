<x-layouts::app :title="__('Proveedores')">
    <div class="module-page">
        <div class="module-page-inner">
            <section class="module-hero">
                <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="farma-kicker">Catálogo de suministro</p>
                        <h1 class="mt-2">Proveedores</h1>
                        <p class="theme-subtle mt-2 max-w-xl text-sm leading-6">Administra tu red de proveedores: datos de contacto, direcciones y unidades de entrega en un solo lugar.</p>
                    </div>
                    <a href="{{ route('proveedores.create') }}" class="theme-button theme-button-primary">
                        <span class="text-lg leading-none">+</span>
                        Nuevo proveedor
                    </a>
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

            <section class="grid gap-4 md:grid-cols-3">
                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Proveedores</span>
                        <span class="stat-pill info">Total</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $totalProveedores }}</p>
                    <p class="theme-subtle mt-1 text-xs">Registrados en el sistema</p>
                </div>
                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Con correo</span>
                        <span class="stat-pill positive">{{ $proveedoresConCorreo }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $proveedoresConCorreo }}</p>
                    <p class="theme-subtle mt-1 text-xs">Con canal de contacto digital</p>
                </div>
                <div class="module-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Sin teléfono</span>
                        <span class="stat-pill warning">{{ $proveedoresSinTelefono }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $proveedoresSinTelefono }}</p>
                    <p class="theme-subtle mt-1 text-xs">Pendientes de contacto telefónico</p>
                </div>
            </section>

            <section class="module-table" x-data="buscadorTabla()" x-effect="filtrar($el)">
                <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between dark:border-slate-700">
                    <div>
                        <h2 class="text-base font-bold">Directorio de proveedores</h2>
                        <p class="theme-subtle mt-1 text-sm">Información vigente de cada proveedor.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input type="search" placeholder="Buscar proveedor, correo o teléfono" class="theme-input w-full sm:w-64" aria-label="Buscar proveedor" @input.debounce.200ms="texto = $event.target.value">
                        <span class="stat-pill info w-fit whitespace-nowrap">{{ $totalProveedores }} registros</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[980px] text-left text-sm">
                        <thead class="text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Proveedor</th>
                                <th class="px-5 py-3 font-semibold">Dirección</th>
                                <th class="px-5 py-3 font-semibold">Unidad de entrega</th>
                                <th class="px-5 py-3 font-semibold">Contacto</th>
                                <th class="px-5 py-3 text-right font-semibold">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($proveedores as $proveedor)
                                <tr class="border-t border-slate-200 transition hover:bg-emerald-50/60 dark:border-slate-700 dark:hover:bg-emerald-500/5" data-buscar="{{ $proveedor->nombre_proveedor }} {{ $proveedor->correo }} {{ $proveedor->telefono }} {{ $proveedor->unidad_entrega }}">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">{{ strtoupper(mb_substr($proveedor->nombre_proveedor ?? '?', 0, 1)) }}</span>
                                            <div>
                                                <p class="text-sm font-bold">{{ $proveedor->nombre_proveedor }}</p>
                                                <p class="theme-subtle mt-0.5 text-xs">{{ $proveedor->correo ?: 'Correo pendiente' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="theme-subtle max-w-[220px] px-5 py-4">{{ $proveedor->direccion }}</td>
                                    <td class="px-5 py-4">
                                        <span class="stat-pill positive">{{ $proveedor->unidad_entrega }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm">
                                        <div class="font-medium">{{ $proveedor->telefono ?: 'Teléfono pendiente' }}</div>
                                        <div class="theme-subtle mt-1 text-xs">{{ $proveedor->correo ?: 'Correo pendiente' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('proveedores.edit', $proveedor) }}" class="inline-flex rounded-lg px-3 py-2 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100 hover:text-emerald-900 dark:text-emerald-300 dark:hover:bg-emerald-500/15 dark:hover:text-emerald-200">Editar</a>
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
