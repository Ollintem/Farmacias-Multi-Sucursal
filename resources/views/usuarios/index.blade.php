<x-layouts::app :title="__('Usuarios')">
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
            }

            .theme-dark .theme-button-primary {
                background: linear-gradient(135deg, #34d399, #10b981);
                color: #06241a;
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

            .theme-table {
                border-radius: 1.25rem;
                overflow: hidden;
                border: 1px solid transparent;
                box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            }

            .theme-light .theme-table {
                background: rgba(255, 255, 255, 0.9);
                border-color: #dfeae0;
            }

            .theme-dark .theme-table {
                background: rgba(15, 23, 42, 0.72);
                border-color: rgba(148, 163, 184, 0.25);
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

            .theme-tools {
                display: flex;
                align-items: center;
                gap: 0.75rem;
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

            .theme-badge {
                border-radius: 9999px;
                padding: 0.3rem 0.65rem;
                font-size: 0.7rem;
                font-weight: 700;
                letter-spacing: 0.04em;
            }

            .theme-light .theme-badge {
                background: rgba(16, 185, 129, 0.1);
                color: #047857;
                border: 1px solid rgba(16, 185, 129, 0.25);
            }

            .theme-dark .theme-badge {
                background: rgba(16, 185, 129, 0.12);
                color: #a7f3d0;
                border: 1px solid rgba(16, 185, 129, 0.3);
            }
        </style>

        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Personal</p>
                    <h1 class="mt-2 text-3xl font-bold">Usuarios</h1>
                </div>

                <div class="theme-tools">
                    <button type="button" id="theme-toggle" class="theme-switch" aria-label="Cambiar tema">
                        <span class="theme-switch-track">
                            <span class="theme-switch-thumb"></span>
                        </span>
                        <span class="theme-switch-text">Claro</span>
                    </button>

                    <a href="{{ route('dashboard') }}" class="theme-button theme-button-secondary">Dashboard</a>
                    <a href="{{ route('usuarios.create') }}" class="theme-button theme-button-primary">Nuevo usuario</a>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600">
                    {{ session('success') }}
                </div>
            @endif

            <div class="theme-table">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left">
                        <thead class="bg-emerald-50/80">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Nombre</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Usuario</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Email</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Rol</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sucursal</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($usuarios as $usuario)
                                <tr class="bg-transparent hover:bg-emerald-50/50">
                                    <td class="px-4 py-4 text-sm">{{ $usuario->nombre }} {{ $usuario->apellido }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $usuario->nombre_usuario }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $usuario->email }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $usuario->rol?->tipo_rol ?? 'Sin rol' }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $usuario->sucursal?->nombre_sucursal ?? 'Sin sucursal' }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        @if($usuario->es_activo)
                                            <span class="inline-flex rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-600">Activo</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-red-500/15 px-2.5 py-1 text-xs font-semibold text-red-600">Inactivo</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No hay usuarios registrados aún.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            (() => {
                const shell = document.getElementById('theme-shell');
                const toggle = document.getElementById('theme-toggle');
                const label = toggle?.querySelector('.theme-switch-text');

                const applyTheme = (darkMode) => {
                    shell.classList.toggle('theme-dark', darkMode);
                    shell.classList.toggle('theme-light', !darkMode);
                    if (label) {
                        label.textContent = darkMode ? 'Oscuro' : 'Claro';
                    }
                };

                const savedTheme = localStorage.getItem('farmacia-theme');
                applyTheme(savedTheme === 'dark');

                toggle?.addEventListener('click', () => {
                    const isDark = !shell.classList.contains('theme-dark');
                    localStorage.setItem('farmacia-theme', isDark ? 'dark' : 'light');
                    applyTheme(isDark);
                });
            })();
        </script>
    </div>
</x-layouts::app>
