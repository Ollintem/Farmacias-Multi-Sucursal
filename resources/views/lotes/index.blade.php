<x-layouts::app :title="__('Lotes y caducidades')">
    <div class="lotes-page">
        <style>
            .lotes-page {
                width: 100%;
                min-height: 100%;
                background: #eef3f8;
                color: #0f172a;
                font-family: Inter, 'Segoe UI', sans-serif;
            }

            .lotes-shell {
                width: 100%;
                min-height: 100%;
                padding: 1.5rem 1.4rem 1rem;
            }

            .lotes-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: rgba(255, 255, 255, 0.65);
                border: 1px solid #dfeaf0;
                border-radius: 0.95rem;
                padding: 0.8rem 1rem;
                margin-bottom: 1.5rem;
                box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
            }

            .lotes-branch {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex: 1;
            }

            .lotes-branch label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.9rem;
                color: #48576d;
                font-weight: 600;
            }

            .lotes-select {
                appearance: none;
                background: #f8fafc;
                border: 1px solid #d8e2ec;
                border-radius: 0.7rem;
                color: #0f172a;
                padding: 0.6rem 2.5rem 0.6rem 0.8rem;
                font-size: 0.92rem;
                font-weight: 600;
                outline: none;
            }

            .lotes-date {
                font-size: 0.8rem;
                color: #475569;
                font-weight: 600;
                white-space: nowrap;
                margin-right: 0.8rem;
            }

            .lotes-user {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .lotes-avatar {
                width: 2.1rem;
                height: 2.1rem;
                border-radius: 50%;
                background: linear-gradient(135deg, #f59e0b, #ef4444);
                color: white;
                font-size: 0.7rem;
                font-weight: 800;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .lotes-user-meta {
                display: flex;
                flex-direction: column;
                line-height: 1.1;
            }

            .lotes-user-meta strong {
                font-size: 0.8rem;
            }

            .lotes-user-meta span {
                font-size: 0.65rem;
                color: #64748b;
            }

            .lotes-panel {
                background: rgba(255, 255, 255, 0.7);
                border: 1px solid #dfeaf0;
                border-radius: 1rem;
                padding: 1.2rem 1.1rem 1.5rem;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
            }

            .lotes-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .lotes-header h1 {
                margin: 0;
                font-size: clamp(1.8rem, 2vw, 2.2rem);
                font-weight: 800;
                color: #0f172a;
            }

            .lotes-header p {
                margin: 0.2rem 0 0;
                font-size: 0.95rem;
                color: #64748b;
            }

            .btn-primary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                border: none;
                background: linear-gradient(135deg, #34d399, #10b981);
                color: white;
                border-radius: 0.75rem;
                padding: 0.8rem 1.15rem;
                font-weight: 700;
                box-shadow: 0 10px 20px rgba(16, 185, 129, 0.18);
            }

            .lotes-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 0.6rem;
                margin: 1rem 0 1.15rem;
            }

            .filter-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                border-radius: 9999px;
                padding: 0.45rem 0.8rem;
                font-size: 0.78rem;
                font-weight: 700;
                border: 1px solid transparent;
                background: #f1f5f9;
                color: #475569;
            }

            .filter-pill .dot {
                width: 0.7rem;
                height: 0.7rem;
                border-radius: 50%;
                display: inline-block;
            }

            .filter-pill.all { background: #f1f5f9; color: #334155; }
            .filter-pill.all .dot { background: #94a3b8; }
            .filter-pill.expired { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
            .filter-pill.expired .dot { background: #ef4444; }
            .filter-pill.warning { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
            .filter-pill.warning .dot { background: #f59e0b; }
            .filter-pill.danger { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
            .filter-pill.danger .dot { background: #ef4444; }
            .filter-pill.vigente { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
            .filter-pill.vigente .dot { background: #22c55e; }

            .lotes-search {
                margin-bottom: 1rem;
            }

            .lotes-search input {
                width: 100%;
                border: 1px solid #dfeaf0;
                border-radius: 0.8rem;
                background: #f8fafc;
                padding: 0.9rem 1rem;
                color: #0f172a;
                font-size: 1rem;
                outline: none;
            }

            .lotes-table-wrap {
                overflow: hidden;
                border-radius: 0.9rem;
                border: 1px solid #dfeaf0;
                background: rgba(255, 255, 255, 0.8);
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            thead th {
                padding: 0.9rem 0.85rem;
                text-align: left;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: #64748b;
                background: #f8fafc;
                border-bottom: 1px solid #e2e8f0;
            }

            tbody td {
                padding: 0.9rem 0.85rem;
                font-size: 0.96rem;
                color: #0f172a;
                border-bottom: 1px solid #edf2f7;
                vertical-align: middle;
            }

            tbody tr:hover {
                background: rgba(236, 253, 245, 0.4);
            }

            .product-name {
                font-weight: 700;
                margin: 0;
            }

            .product-mark {
                display: block;
                font-size: 0.72rem;
                color: #64748b;
                margin-top: 0.1rem;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 9999px;
                padding: 0.38rem 0.8rem;
                font-size: 0.75rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .status-badge.vigente {
                background: #dcfce7;
                color: #166534;
            }

            .status-badge.warning {
                background: #ffedd5;
                color: #c2410c;
            }

            .status-badge.danger {
                background: #ffe4e6;
                color: #b91c1c;
            }

            .status-badge.expired {
                background: #fecaca;
                color: #991b1b;
            }

            @media (max-width: 860px) {
                .lotes-topbar {
                    flex-wrap: wrap;
                    gap: 0.6rem;
                }

                .lotes-header {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .lotes-table-wrap {
                    overflow-x: auto;
                }

                table {
                    min-width: 920px;
                }
            }
        </style>

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
