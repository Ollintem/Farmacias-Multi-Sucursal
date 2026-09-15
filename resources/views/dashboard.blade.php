<x-layouts::app :title="__('Dashboard')">
    <div id="theme-shell" class="theme-light">
        <style>
            #theme-shell {
                width: 100%;
                min-height: 100%;
                transition: all 0.25s ease;
            }

            .theme-light {
                background: linear-gradient(180deg, #f4f9f3 0%, #edf3ef 100%);
                color: #0f172a;
            }

            .theme-dark {
                background: linear-gradient(180deg, #111827 0%, #0b1220 100%);
                color: #f8fafc;
            }

            .theme-shell-inner {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                width: 100%;
                border-radius: 1.5rem;
                padding: 1.5rem;
                transition: all 0.25s ease;
            }

            .theme-light .theme-shell-inner {
                background: rgba(255, 255, 255, 0.55);
                border: 1px solid #d8e5d8;
                box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
            }

            .theme-dark .theme-shell-inner {
                background: rgba(15, 23, 42, 0.78);
                border: 1px solid rgba(148, 163, 184, 0.25);
                box-shadow: 0 12px 30px rgba(2, 6, 23, 0.45);
            }

            .theme-card {
                border-radius: 1.25rem;
                border: 1px solid transparent;
                padding: 1.25rem;
                transition: all 0.25s ease;
            }

            .theme-light .theme-card {
                background: rgba(255, 255, 255, 0.9);
                border-color: #dfeae0;
                color: #0f172a;
            }

            .theme-dark .theme-card {
                background: rgba(15, 23, 42, 0.7);
                border-color: rgba(148, 163, 184, 0.25);
                color: #f8fafc;
            }

            .theme-subtle {
                color: inherit;
                opacity: 0.75;
            }

            .theme-light .theme-subtle { color: #475569; }
            .theme-dark .theme-subtle { color: #cbd5e1; }

            .theme-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.9rem;
                padding: 0.7rem 1rem;
                font-weight: 600;
                transition: all 0.2s ease;
                border: 1px solid transparent;
                text-decoration: none;
            }

            .theme-light .theme-button-primary {
                background: linear-gradient(135deg, #22c55e, #16a34a);
                color: #052e16;
                box-shadow: 0 10px 22px rgba(34, 197, 94, 0.25);
            }

            .theme-dark .theme-button-primary {
                background: linear-gradient(135deg, #34d399, #10b981);
                color: #06241a;
                box-shadow: 0 10px 22px rgba(16, 185, 129, 0.25);
            }

            .theme-light .theme-button-secondary {
                background: rgba(255, 255, 255, 0.8);
                border-color: #d8e5d8;
                color: #0f172a;
            }

            .theme-dark .theme-button-secondary {
                background: rgba(15, 23, 42, 0.8);
                border-color: rgba(148, 163, 184, 0.25);
                color: #f8fafc;
            }

            .theme-switch {
                display: inline-flex;
                align-items: center;
                gap: 0.75rem;
                border: none;
                background: transparent;
                cursor: pointer;
                color: inherit;
            }

            .theme-switch-track {
                position: relative;
                display: inline-flex;
                width: 3.1rem;
                height: 1.8rem;
                border-radius: 9999px;
                background: rgba(148, 163, 184, 0.4);
                transition: all 0.2s ease;
                padding: 0.2rem;
            }

            .theme-light .theme-switch-track {
                background: rgba(148, 163, 184, 0.38);
            }

            .theme-dark .theme-switch-track {
                background: rgba(34, 197, 94, 0.3);
            }

            .theme-switch-thumb {
                position: absolute;
                top: 0.2rem;
                left: 0.2rem;
                width: 1.4rem;
                height: 1.4rem;
                border-radius: 9999px;
                background: white;
                box-shadow: 0 2px 10px rgba(15, 23, 42, 0.18);
                transition: transform 0.2s ease;
            }

            .theme-dark .theme-switch-thumb {
                transform: translateX(1.3rem);
                background: #d1fae5;
            }

            .theme-switch-text {
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .branch-select {
                border-radius: 0.85rem;
                border: 1px solid rgba(148, 163, 184, 0.38);
                padding: 0.7rem 0.9rem;
                font-size: 0.9rem;
                outline: none;
                transition: all 0.2s ease;
            }

            .theme-light .branch-select {
                background: rgba(255, 255, 255, 0.9);
                color: #0f172a;
            }

            .theme-dark .branch-select {
                background: rgba(15, 23, 42, 0.7);
                color: #f8fafc;
            }

            .compact-stat {
                border-radius: 1rem;
                padding: 0.9rem 1rem;
                min-height: 110px;
                border: 1px solid rgba(148, 163, 184, 0.2);
            }

            .theme-light .compact-stat {
                background: linear-gradient(180deg, rgba(248, 250, 252, 0.95), rgba(240, 253, 244, 0.9));
            }

            .theme-dark .compact-stat {
                background: linear-gradient(180deg, rgba(15, 23, 42, 0.9), rgba(17, 24, 39, 0.8));
            }

            .stat-pill {
                display: inline-flex;
                align-items: center;
                border-radius: 9999px;
                padding: 0.28rem 0.7rem;
                font-size: 0.7rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .stat-pill.positive {
                background: rgba(34, 197, 94, 0.12);
                color: #15803d;
            }

            .stat-pill.warning {
                background: rgba(251, 191, 36, 0.12);
                color: #b45309;
            }

            .stat-pill.info {
                background: rgba(59, 130, 246, 0.12);
                color: #1d4ed8;
            }
        </style>

        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Panel general</p>
                    <h1 class="mt-2 text-3xl font-bold">Dashboard farmacéutico</h1>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" id="theme-toggle" class="theme-switch" aria-label="Cambiar tema">
                        <span class="theme-switch-track">
                            <span class="theme-switch-thumb"></span>
                        </span>
                        <span class="theme-switch-text">Claro</span>
                    </button>

                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-primary">Ver usuarios</a>
                    <a href="{{ route('usuarios.create') }}" class="theme-button theme-button-secondary">Nuevo usuario</a>
                </div>
            </div>

            <div class="theme-card">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="theme-subtle text-xs uppercase tracking-[0.25em]">Sucursal activa</p>
                        <h2 class="mt-2 text-2xl font-bold">Centro Histórico</h2>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <label class="flex items-center gap-2 text-sm font-medium">
                            <span class="theme-subtle">Ver:</span>
                            <select id="branch-select" class="branch-select" aria-label="Seleccionar sucursal">
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}" data-name="{{ $sucursal->nombre_sucursal }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <a href="{{ route('sucursales.create') }}" class="theme-button theme-button-secondary whitespace-nowrap">+ Agregar sucursal</a>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    <div class="compact-stat">
                        <div class="flex items-center justify-between">
                            <span class="theme-subtle text-sm">Ventas</span>
                            <span class="stat-pill positive">+12%</span>
                        </div>
                        <p id="stat-ventas" class="mt-4 text-2xl font-bold">Sin datos</p>
                        <p class="mt-1 text-xs theme-subtle">Aún no disponible</p>
                    </div>

                    <div class="compact-stat">
                        <div class="flex items-center justify-between">
                            <span class="theme-subtle text-sm">Clientes</span>
                            <span class="stat-pill info">Pendiente</span>
                        </div>
                        <p id="stat-clientes" class="mt-4 text-2xl font-bold">Sin datos</p>
                        <p class="mt-1 text-xs theme-subtle">Aún no disponible</p>
                    </div>

                    <div class="compact-stat">
                        <div class="flex items-center justify-between">
                            <span class="theme-subtle text-sm">Stock</span>
                            <span class="stat-pill warning">Pendiente</span>
                        </div>
                        <p id="stat-stock" class="mt-4 text-2xl font-bold">Sin datos</p>
                        <p class="mt-1 text-xs theme-subtle">Aún no disponible</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.8fr_1fr]">
                <div class="space-y-4">
                    <div class="theme-card">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold">Acceso rápido</h2>
                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-600">Superadmin</span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <a href="{{ route('usuarios.index') }}" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 transition hover:border-emerald-400 hover:bg-emerald-100/80">
                                <p class="text-base font-semibold">Usuarios</p>
                                <p class="mt-1 text-sm text-slate-500">Gestión de personal</p>
                            </a>

                            <a href="#" class="rounded-2xl border border-sky-200 bg-sky-50 p-4 transition hover:border-sky-400 hover:bg-sky-100/80">
                                <p class="text-base font-semibold">Inventario</p>
                                <p class="mt-1 text-sm text-slate-500">Productos y stock</p>
                            </a>

                            <a href="#" class="rounded-2xl border border-violet-200 bg-violet-50 p-4 transition hover:border-violet-400 hover:bg-violet-100/80">
                                <p class="text-base font-semibold">Sucursales</p>
                                <p class="mt-1 text-sm text-slate-500">Control por tienda</p>
                            </a>
                        </div>
                    </div>

                    <div class="theme-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="theme-subtle text-sm">Resumen de operación</p>
                                <h3 class="mt-1 text-xl font-bold">Farmacia hoy</h3>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Sin datos</span>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm text-slate-600">Ventas en línea</p>
                                <p class="mt-2 text-2xl font-bold text-slate-400">Sin datos</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm text-slate-600">Pedidos pendientes</p>
                                <p class="mt-2 text-2xl font-bold text-slate-400">Sin datos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="theme-card">
                        <h2 class="text-lg font-semibold">Estado del sistema</h2>

                        <div class="mt-5 space-y-4">
                            <p class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm theme-subtle">
                                Aún no hay información del sistema para mostrar.
                            </p>
                        </div>
                    </div>

                    <div class="theme-card">
                        <h2 class="text-lg font-semibold">Actividad reciente</h2>
                        <ul class="mt-4 space-y-3">
                            <li class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm theme-subtle">
                                No hay actividad reciente.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (() => {
                const shell = document.getElementById('theme-shell');
                const toggle = document.getElementById('theme-toggle');
                const label = toggle?.querySelector('.theme-switch-text');
                const branchSelect = document.getElementById('branch-select');
                const title = document.querySelector('.theme-card h2');
                const applyTheme = (darkMode) => {
                    shell.classList.toggle('theme-dark', darkMode);
                    shell.classList.toggle('theme-light', !darkMode);
                    if (label) {
                        label.textContent = darkMode ? 'Oscuro' : 'Claro';
                    }
                };

                const savedTheme = localStorage.getItem('farmacia-theme');
                applyTheme(savedTheme === 'dark');

                const updateBranchStats = (value) => {
                    const option = [...(branchSelect?.options || [])].find((item) => item.value === value);
                    if (title) title.textContent = option?.dataset.name || 'Sucursal activa';
                };

                branchSelect?.addEventListener('change', (event) => updateBranchStats(event.target.value));
                updateBranchStats(branchSelect?.value || '');

                toggle?.addEventListener('click', () => {
                    const isDark = !shell.classList.contains('theme-dark');
                    localStorage.setItem('farmacia-theme', isDark ? 'dark' : 'light');
                    applyTheme(isDark);
                });
            })();
        </script>
    </div>
</x-layouts::app>
