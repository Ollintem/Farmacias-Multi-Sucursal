<x-layouts::app :title="__('Lotes y caducidades')">
    <div class="lotes-page">
        <div class="lotes-shell">
            <header class="lotes-topbar">
                <div class="lotes-branch">
                    <span class="lotes-date">mié, 16 de sep de 2026</span>
                    <label>
                        <span>Sucursal</span>
                        <select class="lotes-select" aria-label="Sucursal">
                            <option selected>Ixtapaluca Centro</option>
                            <option>Chalco</option>
                            <option>Los Reyes</option>
                        </select>
                    </label>
                </div>

                <div class="lotes-user">
                    <div class="lotes-avatar">{{ strtoupper(substr(auth()->user()->nombre ?? auth()->user()->name, 0, 1)) }}</div>
                    <div class="lotes-user-meta">
                        <strong>{{ auth()->user()->name }}</strong>
                        <span>{{ auth()->user()->rol?->tipo_rol ?? 'Sin rol' }}</span>
                    </div>
                </div>
            </header>

            <section class="lotes-panel">
                <div class="lotes-header">
                    <div>
                        <h1>Lotes y caducidades</h1>
                        <p>Control de trazabilidad por lote</p>
                    </div>

                    <button type="button" class="btn-primary">+ Registrar lote</button>
                </div>

                <div class="lotes-filters">
                    <span class="filter-pill all"><span class="dot"></span>Todos</span>
                    <span class="filter-pill expired"><span class="dot"></span>Caducado</span>
                    <span class="filter-pill danger"><span class="dot"></span>Caduca &lt; 30 días</span>
                    <span class="filter-pill warning"><span class="dot"></span>Caduca &lt; 90 días</span>
                    <span class="filter-pill vigente"><span class="dot"></span>Vigente</span>
                </div>

                <div class="lotes-search">
                    <input type="text" placeholder="Buscar producto o número de lote..." aria-label="Buscar producto o número de lote" />
                </div>

                <div class="lotes-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>N.° Lote</th>
                                <th>Producto</th>
                                <th>Sucursal</th>
                                <th>Cantidad</th>
                                <th>Fecha entrada</th>
                                <th>Fecha caducidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lotes as $lote)
                                <tr>
                                    <td>{{ $lote['folio'] }}</td>
                                    <td>
                                        <span class="product-name">{{ $lote['producto'] }}</span>
                                        <span class="product-mark">{{ $lote['marca'] }}</span>
                                    </td>
                                    <td>{{ $lote['sucursal'] }}</td>
                                    <td>{{ $lote['cantidad'] }}</td>
                                    <td>{{ $lote['fecha_entrada'] }}</td>
                                    <td>{{ $lote['fecha_caducidad'] }}</td>
                                    <td>
                                        <span class="status-badge {{ $lote['estado_class'] }}">{{ $lote['estado'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
