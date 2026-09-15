<x-layouts::app :title="__('Sucursales')">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Administración</p>
                <h1 class="mt-2 text-3xl font-bold">Sucursales</h1>
                <p class="mt-2 text-sm text-slate-500">Consulta las farmacias registradas en el sistema.</p>
            </div>
            <a href="{{ route('sucursales.create') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">+ Agregar sucursal</a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left">
                    <thead class="bg-emerald-50/80">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Nombre</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Dirección</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Apertura</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Cierre</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($sucursales as $sucursal)
                            <tr class="hover:bg-emerald-50/50">
                                <td class="px-4 py-4 text-sm font-medium">{{ $sucursal->nombre_sucursal }}</td>
                                <td class="px-4 py-4 text-sm">{{ $sucursal->direccion }}</td>
                                <td class="px-4 py-4 text-sm">{{ $sucursal->hora_apertura }}</td>
                                <td class="px-4 py-4 text-sm">{{ $sucursal->hora_cierre }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">No hay sucursales registradas aún.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
