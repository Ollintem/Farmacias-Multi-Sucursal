<x-layouts::app :title="__('Lotes y caducidades')">
    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Trazabilidad</p>
                    <h1 class="mt-2 text-3xl font-bold">Lotes y caducidades</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('lotes.create', ['sucursal' => $selectedSucursal?->id]) }}" class="theme-button theme-button-primary">+ Registrar nuevo lote</a>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Total de lotes</span>
                        <span class="stat-pill info">{{ $totalLotes }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $totalLotes }}</p>
                    <p class="mt-1 text-xs theme-subtle">Registros activos</p>
                </div>

                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Vigentes</span>
                        <span class="stat-pill positive">OK</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $vigentes }}</p>
                    <p class="mt-1 text-xs theme-subtle">Sin riesgo de caducidad</p>
                </div>

                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Por caducar</span>
                        <span class="stat-pill warning">{{ $porCaducar }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $porCaducar }}</p>
                    <p class="mt-1 text-xs theme-subtle">Menos de 90 días</p>
                </div>

                <div class="compact-stat">
                    <div class="flex items-center justify-between">
                        <span class="theme-subtle text-sm">Caducados</span>
                        <span class="stat-pill danger">{{ $caducados }}</span>
                    </div>
                    <p class="mt-4 text-2xl font-bold">{{ $caducados }}</p>
                    <p class="mt-1 text-xs theme-subtle">Requieren atención</p>
                </div>
            </div>

            <div class="theme-card">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $selectedSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</h2>
                    </div>

                    <form method="GET" action="{{ route('lotes.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <label class="flex items-center gap-2 text-sm font-medium">
                            <span class="theme-subtle">Sucursal:</span>
                            <select name="sucursal" class="branch-select" aria-label="Seleccionar sucursal" onchange="this.form.submit()">
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}" {{ $selectedSucursal && $selectedSucursal->id === $sucursal->id ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <input type="search" name="buscar" value="{{ old('buscar', $busqueda) }}" placeholder="Buscar folio, proveedor o producto" class="theme-input w-full sm:w-72" aria-label="Buscar lote">
                        <button type="submit" class="theme-button theme-button-secondary whitespace-nowrap">Buscar</button>
                    </form>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white/80">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="px-4 py-3 font-semibold">Lote</th>
                                    <th class="px-4 py-3 font-semibold">Producto</th>
                                    <th class="px-4 py-3 font-semibold">Proveedor</th>
                                    <th class="px-4 py-3 font-semibold">Sucursal</th>
                                    <th class="px-4 py-3 font-semibold">Cantidad</th>
                                    <th class="px-4 py-3 font-semibold">Entrada</th>
                                    <th class="px-4 py-3 font-semibold">Caducidad</th>
                                    <th class="px-4 py-3 font-semibold">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lotes as $lote)
                                    <tr class="border-t border-slate-200">
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $lote['folio'] }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-slate-800">{{ $lote['producto'] }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ $lote['marca'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $lote['sucursal'] }}</td>
                                        <td class="px-4 py-3 text-slate-700">{{ $lote['cantidad'] }} uds.</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $lote['fecha_entrada'] }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $lote['fecha_caducidad'] }}</td>
                                        <td class="px-4 py-3">
                                            <span class="status-badge {{ $lote['estado_class'] }}">{{ $lote['estado'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-10 text-center text-slate-500">
                                            No se encontraron lotes para la sucursal o búsqueda actual.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
