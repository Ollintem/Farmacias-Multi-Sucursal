<section class="entradas-seccion" aria-label="Listado de entradas">
    <div class="entradas-seccion-head">
        <span class="entradas-stat-icono total" aria-hidden="true">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m0 10V11m0 10l-8-4V7m8 4L4 7"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <h3>{{ $tipoActual === 'compras' ? 'Compras recibidas' : 'Todas las entradas' }}</h3>
            <p class="theme-subtle text-sm">{{ $tipoActual === 'compras' ? 'Recepción de proveedores en ' . ($selectedSucursal?->nombre_sucursal ?? 'la sucursal') . '.' : 'Compras y traspasos recibidos en ' . ($selectedSucursal?->nombre_sucursal ?? 'la sucursal') . '.' }}</p>
        </div>
        <span class="seg-contador">{{ ($entradas ?? collect())->count() }}</span>
    </div>
    <div class="entradas-tabla-wrap">
        <table class="entradas-tabla entradas-tabla--compact">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Tipo</th>
                    <th>Movimiento</th>
                    <th class="entradas-num">Stock total</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entradas ?? [] as $entrada)
                    <tr>
                        <td><span class="entradas-folio">{{ $entrada['folio'] ?? $entrada->folio ?? ('#' . ($entrada['id'] ?? $entrada->id ?? '—')) }}</span></td>
                        <td>
                            @php($tipoEntrada = strtolower($entrada['tipo'] ?? $entrada->tipo ?? $tipoActual))
                            @if($tipoEntrada === 'traspaso' || $tipoEntrada === 'traspasos')
                                <span class="stat-pill warning">Traspaso</span>
                            @else
                                <span class="stat-pill positive">Compra</span>
                            @endif
                        </td>
                        <td>
                            <div class="entradas-ruta">
                                <span class="entradas-ruta-origen">{{ $entrada['origen'] ?? $entrada->origen ?? '—' }}</span>
                                <span class="entradas-ruta-sep" aria-hidden="true">→</span>
                                <span class="entradas-ruta-destino">{{ $entrada['destino'] ?? $entrada->destino ?? ($selectedSucursal?->nombre_sucursal ?? '—') }}</span>
                            </div>
                        </td>
                        <td class="entradas-num">
                            @php($detallePresentaciones = collect($entrada['presentaciones_detalle'] ?? []))
                            @forelse($detallePresentaciones as $presentacion)
                                <span class="block leading-tight">
                                    <strong>{{ $presentacion['cantidad'] }}</strong>
                                    <span class="theme-subtle text-xs">{{ \Illuminate\Support\Str::plural($presentacion['nombre'], $presentacion['cantidad']) }}</span>
                                </span>
                            @empty
                                @php($presentaciones = (int) ($entrada['presentaciones'] ?? 0))
                                <strong>{{ $presentaciones }}</strong> <span class="theme-subtle text-xs">{{ $presentaciones === 1 ? 'presentación' : 'presentaciones' }}</span>
                            @endforelse
                            <span class="block text-[11px] theme-subtle">{{ $entrada['unidades'] ?? $entrada->unidades ?? 0 }} uds.</span>
                        </td>
                        <td class="entradas-fecha">{{ $entrada['fecha'] ?? $entrada->fecha ?? '—' }}</td>
                        <td><span class="status-badge {{ $entrada['estado_class'] ?? '' }}">{{ $entrada['estado'] ?? $entrada->estado ?? 'Registrada' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="entradas-vacio"><p class="entradas-vacio-titulo">Sin entradas</p><p class="theme-subtle text-sm">No se encontraron entradas para esta sucursal.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
