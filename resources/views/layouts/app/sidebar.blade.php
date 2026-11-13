<!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#e9efec] text-[#22332f] dark:bg-[#1a2a32] dark:text-[#e6efec]" x-data="{ currentDate: '' }" x-init="updateDate(); setInterval(updateDate, 60000)">
        <flux:sidebar id="app-sidebar" sticky collapsible class="erp-sidebar erp-sidebar-v2 border-e border-[#2c545e] bg-[#20444c] text-[#c3d4cf] dark:bg-[#1e3039]">
            <flux:sidebar.header class="!border-b-0">
                <flux:sidebar.brand name="FarmaERP" href="{{ route('dashboard') }}" wire:navigate>
                    <x-slot name="logo" class="erp-logo flex aspect-square size-8 items-center justify-center rounded-xl bg-[#0e9384] text-white shadow-lg shadow-black/20">
                        <span class="text-xs font-black">Rx</span>
                    </x-slot>
                </flux:sidebar.brand>
                <flux:sidebar.collapse class="text-[#c3d4cf] hover:text-white" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="erp-nav erp-nav-animated">
                @php($usuarioActual = auth()->user())
                @if($usuarioActual?->puedeVerModulo('Dashboard') ?? false)
                <flux:sidebar.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate data-erp-label="dashboard">
                    Dashboard
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Punto de venta') ?? false)
                <flux:sidebar.item icon="receipt-percent" :href="route('punto-venta.index')" :current="request()->routeIs('punto-venta.*')" wire:navigate data-erp-label="punto venta pos">
                    Punto de venta
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Inventario') ?? false)
                <flux:sidebar.item icon="archive-box" :href="route('inventario.productos')" :current="request()->routeIs('inventario.*')" wire:navigate data-erp-label="inventario stock productos lotes">
                    Inventario
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Entradas de almacén') ?? false)
                <flux:sidebar.item icon="inbox-arrow-down" :href="route('entradas-de-almacen.index')" :current="request()->routeIs('entradas-de-almacen.*')" wire:navigate data-erp-label="entradas almacen compras traspasos">
                    Entradas de almacén
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Proveedores') ?? false)
                <flux:sidebar.item icon="truck" :href="route('proveedores.index')" :current="request()->routeIs('proveedores.*')" wire:navigate data-erp-label="proveedores">
                    Proveedores
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Sucursales') ?? false)
                <flux:sidebar.item icon="map-pin" :href="route('sucursales.index')" :current="request()->routeIs('sucursales.*')" wire:navigate data-erp-label="sucursales">
                    Sucursales
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Usuarios y roles') ?? false)
                <flux:sidebar.item icon="users" :href="route('usuarios.index')" :current="request()->routeIs('usuarios.*')" wire:navigate data-erp-label="usuarios roles permisos">
                    Usuarios y roles
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Caja') ?? false)
                <flux:sidebar.item icon="banknotes" :href="route('caja.index')" :current="request()->routeIs('caja.*')" wire:navigate data-erp-label="caja cortes">
                    Caja
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Reportes') ?? false)
                <flux:sidebar.item icon="list-bullet" :href="route('reportes.index')" :current="request()->routeIs('reportes.*')" wire:navigate data-erp-label="reportes">
                    Reportes
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Alertas') ?? false)
                @php($alertasTotal = \App\Support\AlertasFeed::noLeidasCount((int) (session('active_sucursal_id', $usuarioActual?->id_sucursal ?? 0) ?: 0), $usuarioActual?->id))
                <flux:sidebar.item icon="bell" :href="route('alertas.index')" :current="request()->routeIs('alertas.*')" :badge="$alertasTotal > 0 ? $alertasTotal : null" wire:navigate data-erp-label="notificaciones alertas">
                    Notificaciones
                </flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- Zona inferior: sucursal + usuario + barra de acciones (como la imagen) --}}
            <div class="erp-sidebar-bottom flex flex-col gap-2 px-2.5 pb-3 pt-2">
                <div class="erp-sidebar-bottom-card rounded-2xl border border-white/10 bg-[#16313a]/60 p-1.5 backdrop-blur">
                    {{-- Selector de Sucursal --}}
                    <flux:dropdown position="top" align="start" class="erp-user-menu w-full">
                        <flux:sidebar.profile
                            :name="\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))?->nombre_sucursal ?? 'Seleccionar sucursal'"
                            :initials="\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))?->nombre_sucursal ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))->nombre_sucursal, 0, 1)) : 'S'"
                            icon:trailing="chevrons-up-down"
                            class="w-full"
                        />
                        <flux:menu class="w-56">
                            @foreach(\App\Models\Sucursal::where('es_activa', true)->orderBy('nombre_sucursal')->get() as $sucursal)
                                <a
                                    href="{{ request()->fullUrlWithQuery(array_merge(request()->query(), ['sucursal' => $sucursal->id])) }}"
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-[#d5e5e0] hover:bg-white/10 rounded-lg transition
                                        {{ (session('active_sucursal_id', auth()->user()?->id_sucursal ?? '')) == $sucursal->id ? 'bg-[#0e9384]/25 text-white font-semibold' : '' }}"
                                    wire:navigate
                                >
                                    <span class="w-5 h-5 flex items-center justify-center rounded bg-[#0e9384]/25 text-[#7fd7c8] text-xs font-bold">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sucursal->nombre_sucursal, 0, 1)) }}
                                    </span>
                                    <span class="truncate">{{ $sucursal->nombre_sucursal }}</span>
                                    @if((session('active_sucursal_id', auth()->user()?->id_sucursal ?? '')) == $sucursal->id)
                                        <svg class="ml-auto w-4 h-4 text-[#7fd7c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </a>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>

                    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
                </div>

                {{-- Barra de acciones inferior: modo oscuro + ajustes + cerrar sesión --}}
                <div class="erp-sidebar-actions grid grid-cols-3 gap-1.5 rounded-2xl border border-white/[0.08] bg-[#0f2a32]/80 p-1.5 backdrop-blur">
                    {{-- 1. Modo oscuro / claro (botón principal activo) --}}
                    <button type="button" data-farma-theme-toggle class="erp-sidebar-action erp-sidebar-action--theme" title="Cambiar modo oscuro" aria-label="Cambiar modo oscuro">
                        <svg data-farma-theme-icon-sun class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.41-1.41M7.05 7.05L5.64 5.64m12.72 0l-1.41 1.41M7.05 16.95l-1.41 1.41M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg data-farma-theme-icon-moon class="hidden h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    </button>
                    {{-- 2. Ajustes --}}
                    <a href="{{ route('profile.edit') }}" wire:navigate class="erp-sidebar-action" title="Ajustes" aria-label="Ajustes">
                        <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </a>
                    {{-- 3. Cerrar sesión - puerta abierta --}}
                    <form method="POST" action="{{ route('logout') }}" class="contents">
                        @csrf
                        <button type="submit" class="erp-sidebar-action erp-sidebar-action--logout" title="Cerrar sesión" aria-label="Cerrar sesión" data-test="sidebar-logout-button">
                            {{-- Heroicon arrow-right-start-on-rectangle (puerta abierta saliendo) --}}
                            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            <script>
                (() => {
                    // Ripple: guarda coordenadas del click para el efecto radial
                    document.querySelectorAll('.erp-nav-animated [data-flux-sidebar-item]').forEach((item) => {
                        item.addEventListener('click', (e) => {
                            const rect = item.getBoundingClientRect();
                            const x = ((e.clientX - rect.left) / rect.width) * 100;
                            const y = ((e.clientY - rect.top) / rect.height) * 100;
                            item.style.setProperty('--x', x + '%');
                            item.style.setProperty('--y', y + '%');
                        });
                    });
                })();
            </script>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="border-b border-[#cfddd7] bg-[#f7faf9] lg:hidden dark:border-[#33505c] dark:bg-[#22353f]" x-data="{ currentDate: '' }" x-init="updateDate(); setInterval(updateDate, 60000)">
            <flux:sidebar.toggle for="app-sidebar" class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            {{-- Selector Sucursal Mobile (mismo estilo) --}}
            <flux:dropdown position="bottom" align="start" class="mr-2 erp-user-menu">
                <flux:sidebar.profile
                    :name="\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))?->nombre_sucursal ?? 'Seleccionar sucursal'"
                    :initials="\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))?->nombre_sucursal ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))->nombre_sucursal, 0, 1)) : 'S'"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-48">
                    @foreach(\App\Models\Sucursal::where('es_activa', true)->orderBy('nombre_sucursal')->get() as $sucursal)
                        <a
                            href="{{ request()->fullUrlWithQuery(array_merge(request()->query(), ['sucursal' => $sucursal->id])) }}"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-[#d5e5e0] hover:bg-white/10 rounded-lg transition
                                {{ (session('active_sucursal_id', auth()->user()?->id_sucursal ?? '')) == $sucursal->id ? 'bg-[#0e9384]/25 text-white font-semibold' : '' }}"
                            wire:navigate
                        >
                            <span class="w-5 h-5 flex items-center justify-center rounded bg-[#0e9384]/25 text-[#7fd7c8] text-xs font-bold">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sucursal->nombre_sucursal, 0, 1)) }}
                            </span>
                            <span class="truncate">{{ $sucursal->nombre_sucursal }}</span>
                            @if((session('active_sucursal_id', auth()->user()?->id_sucursal ?? '')) == $sucursal->id)
                                <svg class="ml-auto w-4 h-4 text-[#7fd7c8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @endif
                        </a>
                    @endforeach
                </flux:menu>
            </flux:dropdown>

            {{-- Fecha Mobile --}}
            <span
                x-text="currentDate"
                class="hidden sm:inline-block text-xs font-medium text-[#6b7280] font-mono mr-3"
            ></span>

            @if(request()->routeIs('inventario.productos') && (auth()->user()?->permisosHabilitados('crear', 'inventario') ?? false))
                @php($topProductoSucursalMobile = request()->query('sucursal') ?? session('active_sucursal_id', auth()->user()?->id_sucursal))
                <a href="{{ route('inventario.create', array_filter(['sucursal' => $topProductoSucursalMobile])) }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-2.5 !text-xs shrink-0 whitespace-nowrap">+ Agregar producto</a>
            @endif
            @if(request()->routeIs('entradas-de-almacen.*') && (auth()->user()?->permisosHabilitados('crear', 'entradas-de-almacen') ?? false))
                @php($topEntradasSucursalMobile = request()->query('sucursal') ?? session('active_sucursal_id', auth()->user()?->id_sucursal))
                @if(Route::has('compras.create'))
                    <a href="{{ route('compras.create', array_filter(['sucursal' => $topEntradasSucursalMobile])) }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-2.5 !text-xs shrink-0 whitespace-nowrap">+ Compra</a>
                @endif
                @if(Route::has('traspasos.create'))
                    <a href="{{ route('traspasos.create', array_filter(['sucursal' => $topEntradasSucursalMobile])) }}" class="theme-button theme-button-secondary !min-h-0 !py-2 !px-2.5 !text-xs shrink-0 whitespace-nowrap">⇄ Traspaso</a>
                @endif
            @endif
            @if(request()->routeIs('usuarios.*') && (auth()->user()?->permisosHabilitados('crear', 'usuarios') ?? false))
                <button type="button" x-on:click="$dispatch('open-modal-rol')" class="theme-button theme-button-secondary !min-h-0 !py-2 !px-2.5 !text-xs shrink-0 whitespace-nowrap">+ Nuevo rol</button>
                <a href="{{ route('usuarios.create') }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-2.5 !text-xs shrink-0 whitespace-nowrap">+ Nuevo usuario</a>
            @endif
            <livewire:campana-notificaciones />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />
                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate text-zinc-500 dark:text-zinc-400">{{ auth()->user()->rol?->tipo_rol ?? 'Sin rol asignado' }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            Ajustes
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <script>
            function updateDate() {
                const now = new Date();
                const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
                const dateStr = now.toLocaleDateString('es-ES', options);
                const parts = dateStr.split(' ');
                const formatted = `${parts[0]}, ${parts[1]} de ${parts[2]} de ${parts[3]}`;
                const fecha = document.querySelector('[x-text="currentDate"]');
                if (fecha) {
                    fecha.textContent = formatted;
                }
            }
        </script>

        {{-- Barra superior de escritorio: pill flotante (sin botón +) --}}
        <div class="farma-topbar hidden lg:flex">
            <div class="farma-topbar-pill-wrap">
                <div class="farma-topbar-pill">
                    <div class="flex min-w-0 flex-1 items-center">
                <?php if(request()->routeIs('alertas.*')): ?>
                    <?php
                        $topSucursalIdAlertas = (int) (session('active_sucursal_id', auth()->user()?->id_sucursal ?? 0) ?: 0);
                        $topUsuarioIdAlertas = auth()->id();
                        $topAvisosAlertas = $topSucursalIdAlertas ? \App\Support\AlertasFeed::listado($topSucursalIdAlertas, $topUsuarioIdAlertas) : [];
                        $topTotalAlertas = count($topAvisosAlertas);
                        $topNoLeidasAlertas = collect($topAvisosAlertas)->where('leida', false)->count();
                        $topTiposAlertas = [
                            'stock' => collect($topAvisosAlertas)->where('grupo', 'stock')->count(),
                            'caducidad' => collect($topAvisosAlertas)->where('grupo', 'caducidad')->count(),
                            'traspasos' => collect($topAvisosAlertas)->where('grupo', 'traspasos')->count(),
                            'ventas' => collect($topAvisosAlertas)->where('grupo', 'ventas')->count(),
                        ];
                        $topFiltroAlertas = strtolower(trim((string) (request()->query('filtro') ?? request()->query('seccion') ?? 'todas')));
                        if ($topFiltroAlertas === 'traspaso') $topFiltroAlertas = 'traspasos';
                        if ($topFiltroAlertas === 'caducidades') $topFiltroAlertas = 'caducidad';
                        if (!in_array($topFiltroAlertas, ['todas','no_leidas','stock','caducidad','traspasos','ventas'], true)) {
                            // Intentar leer desde Livewire (si no hay query, viene de componente, usar todas)
                            $topFiltroAlertas = 'todas';
                        }
                        // Si la petición Livewire ya tiene filtro en session/component, no lo sabemos aquí; usamos query como fuente principal
                        $topPills = [
                            'todas' => ['Todas', $topTotalAlertas],
                            'no_leidas' => ['No leídas', $topNoLeidasAlertas],
                            'stock' => ['Stock', $topTiposAlertas['stock']],
                            'caducidad' => ['Caducidad', $topTiposAlertas['caducidad']],
                            'traspasos' => ['Traspasos', $topTiposAlertas['traspasos']],
                            'ventas' => ['Ventas', $topTiposAlertas['ventas']],
                        ];
                    ?>
                    <nav class="flex flex-wrap items-center gap-1.5" aria-label="Filtros de notificaciones">
                        @foreach($topPills as $valor => [$etiqueta, $conteo])
                            @php($esActivo = $topFiltroAlertas === $valor)
                            <a href="{{ request()->fullUrlWithQuery(['filtro' => $valor]) }}" wire:navigate @class([
                                'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold transition',
                                'border-[#0e9384] bg-[#0e9384] text-white shadow-sm' => $esActivo,
                                'border-slate-200 bg-white text-slate-600 hover:border-[#0e9384]/30 hover:bg-teal-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => !$esActivo,
                            ])>
                                <span>{{ $etiqueta }}</span>
                                <span class="grid min-w-[1.4rem] place-items-center rounded-full px-1.5 py-0.5 text-[11px] font-black @if($esActivo) bg-white/20 text-white @else bg-slate-100 text-slate-600 dark:bg-zinc-700 dark:text-zinc-300 @endif">{{ $conteo }}</span>
                            </a>
                        @endforeach
                    </nav>
                <?php elseif(request()->routeIs('inventario.*') || request()->routeIs('lotes.*')): ?>
                    <?php
                        $topIsProductos = request()->routeIs('inventario.productos');
                        $topIsStock = request()->routeIs('inventario.stock') || request()->routeIs('inventario.index');
                        $topIsLotes = request()->routeIs('lotes.*');
                        $topSeccion = $topIsProductos ? 'productos' : ($topIsStock ? 'stock' : ($topIsLotes ? 'lotes' : 'productos'));
                        $topSucursalIdTb = (int) (session('active_sucursal_id', auth()->user()?->id_sucursal ?? 0) ?: 0);
                        $topSucursalTb = $topSucursalIdTb ? \App\Models\Sucursal::find($topSucursalIdTb) : null;
                    ?>
                    <div class="flex flex-1 items-center gap-3 min-w-0">
                        <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide shrink-0">
                            @include('pages.inventario.components.tabs', ['seccion' => $topSeccion, 'selectedSucursal' => $topSucursalTb])
                        </div>
                        @if($topIsStock)
                            <?php
                                $topSucursalIdInv = (int) (session('active_sucursal_id', auth()->user()?->id_sucursal ?? request()->query('sucursal') ?? 0) ?: 0);
                                if (!$topSucursalIdInv) {
                                    $topSucursalIdInv = \App\Models\Sucursal::orderBy('nombre_sucursal')->value('id') ?? 0;
                                }
                                $topProductosCount = 0;
                                $topStockCritico = 0;
                                $topUnidades = 0;
                                $topValorTotal = 0.0;
                                if ($topSucursalIdInv) {
                                    try {
                                        $topStockPorProducto = \App\Models\Inventario::stockPorProducto($topSucursalIdInv);
                                        $topProductosKpi = \App\Models\Producto::query()
                                            ->where('productos.es_activo', true)
                                            ->whereHas('inventarios', fn($q) => $q->where('inventario.id_sucursal', $topSucursalIdInv))
                                            ->with('presentacionesPrecio.presentacion')
                                            ->get();
                                        $topProductosCount = $topProductosKpi->count();
                                        foreach ($topProductosKpi as $topP) {
                                            $topStock = (int) ($topStockPorProducto[$topP->id] ?? 0);
                                            $topUnidades += $topStock;
                                            if ($topStock <= $topP->stockMinimoAlerta()) {
                                                $topStockCritico++;
                                            }
                                            $topPrecio = $topP->precioUnidad() ?? (float) ($topP->presentacionPrincipalDe()?->precio_presentacion ?? 0);
                                            $topValorTotal += $topStock * $topPrecio;
                                        }
                                    } catch (\Throwable $e) {
                                        $topProductosCount = 0;
                                        $topStockCritico = 0;
                                        $topUnidades = 0;
                                        $topValorTotal = 0.0;
                                    }
                                }
                            ?>
                            <div class="hidden lg:flex items-center gap-2 overflow-x-auto scrollbar-hide py-0.5">
                                <span class="inline-flex items-center gap-2.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-600 dark:bg-zinc-700 dark:text-zinc-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                    </span>
                                    <span class="leading-none">
                                        <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Productos</span>
                                        <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topProductosCount }}</span>
                                    </span>
                                </span>
                                <span class="inline-flex items-center gap-2.5 rounded-full border px-3 py-1.5 shadow-sm {{ $topStockCritico > 0 ? 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10' : 'border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-800' }}">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full {{ $topStockCritico > 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400' }}">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                    </span>
                                    <span class="leading-none">
                                        <span class="block text-[10px] font-bold uppercase tracking-widest {{ $topStockCritico > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-zinc-400' }}">Stock crítico</span>
                                        <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topStockCritico }}</span>
                                    </span>
                                </span>
                                <span class="inline-flex items-center gap-2.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                                    </span>
                                    <span class="leading-none">
                                        <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Unidades</span>
                                        <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ number_format($topUnidades) }}</span>
                                    </span>
                                </span>
                                <span class="inline-flex items-center gap-2.5 rounded-full border border-emerald-200 bg-white px-3 py-1.5 shadow-sm dark:border-emerald-500/30 dark:bg-zinc-800">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                    <span class="leading-none">
                                        <span class="block text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Valor total</span>
                                        <span class="text-sm font-black leading-none text-slate-900 dark:text-white">${{ number_format($topValorTotal, 2) }}</span>
                                    </span>
                                </span>
                            </div>
                        @elseif($topIsLotes)
                            <?php
                                $topSucursalIdLotes = (int) (session('active_sucursal_id', auth()->user()?->id_sucursal ?? request()->query('sucursal') ?? 0) ?: 0);
                                if (!$topSucursalIdLotes) {
                                    $topSucursalIdLotes = \App\Models\Sucursal::orderBy('nombre_sucursal')->value('id') ?? 0;
                                }
                                $topTotalLotes = 0;
                                $topVigentes = 0;
                                $topPorCaducar = 0;
                                $topCaducados = 0;
                                $topFiltrosBase = array_filter(['sucursal' => $topSucursalIdLotes ?: null]);
                                if ($topSucursalIdLotes) {
                                    try {
                                        $topLotesKpi = \App\Models\Lote::with(['inventarios', 'producto'])
                                            ->where(function ($q) use ($topSucursalIdLotes) {
                                                $q->whereHas('inventarios', fn($iq) => $iq->where('inventario.id_sucursal', $topSucursalIdLotes))
                                                  ->orWhereDoesntHave('inventarios');
                                            })
                                            ->get();
                                        $topTotalLotes = $topLotesKpi->count();
                                        foreach ($topLotesKpi as $topLote) {
                                            $topFecha = $topLote->fecha_de_caducidad ?? $topLote->fecha_caducidad;
                                            $topFechaCarbon = $topFecha ? \Carbon\Carbon::parse($topFecha) : null;
                                            $topClase = \App\Support\EstadoCaducidad::clasificar($topFechaCarbon);
                                            $topEstadoClass = $topClase->claseLotes();
                                            if ($topLote->anulado_en !== null) {
                                                $topEstadoClass = 'expired';
                                            }
                                            if ($topEstadoClass === 'vigente') $topVigentes++;
                                            elseif (in_array($topEstadoClass, ['warning','danger'], true)) $topPorCaducar++;
                                            elseif ($topEstadoClass === 'expired') $topCaducados++;
                                        }
                                    } catch (\Throwable $e) {
                                        $topTotalLotes = 0;
                                        $topVigentes = 0;
                                        $topPorCaducar = 0;
                                        $topCaducados = 0;
                                        $topFiltrosBase = [];
                                    }
                                }
                                $topEnlaceLotes = function(string $clave) use ($topFiltrosBase) {
                                    if ($clave === 'todos') return route('lotes.index', $topFiltrosBase);
                                    return route('lotes.index', array_merge($topFiltrosBase, ['estado' => $clave]));
                                };
                            ?>
                            <div class="hidden lg:flex items-center gap-2 overflow-x-auto scrollbar-hide py-0.5">
                                <a href="{{ $topEnlaceLotes('todos') }}" class="inline-flex items-center gap-2.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 shadow-sm hover:border-slate-300 dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-600 dark:bg-zinc-700 dark:text-zinc-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                    </span>
                                    <span class="leading-none">
                                        <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Total de lotes</span>
                                        <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topTotalLotes }}</span>
                                    </span>
                                </a>
                                <a href="{{ $topEnlaceLotes('vigentes') }}" class="inline-flex items-center gap-2.5 rounded-full border border-emerald-200 bg-white px-3 py-1.5 shadow-sm hover:border-emerald-300 dark:border-emerald-500/30 dark:bg-zinc-800">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                    <span class="leading-none">
                                        <span class="block text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Vigentes</span>
                                        <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topVigentes }}</span>
                                    </span>
                                </a>
                                <a href="{{ $topEnlaceLotes('por-caducar') }}" class="inline-flex items-center gap-2.5 rounded-full border px-3 py-1.5 shadow-sm {{ $topPorCaducar > 0 ? 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10' : 'border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-800' }}">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full {{ $topPorCaducar > 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400' }}">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                    <span class="leading-none">
                                        <span class="block text-[10px] font-bold uppercase tracking-widest {{ $topPorCaducar > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-zinc-400' }}">Por caducar</span>
                                        <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topPorCaducar }}</span>
                                    </span>
                                </a>
                                <a href="{{ $topEnlaceLotes('caducados') }}" class="inline-flex items-center gap-2.5 rounded-full border px-3 py-1.5 shadow-sm {{ $topCaducados > 0 ? 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10' : 'border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-800' }}">
                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full {{ $topCaducados > 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400' }}">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                    </span>
                                    <span class="leading-none">
                                        <span class="block text-[10px] font-bold uppercase tracking-widest {{ $topCaducados > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-zinc-400' }}">Caducados</span>
                                        <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topCaducados }}</span>
                                    </span>
                                </a>
                            </div>
                        @endif
                    </div>
                <?php elseif(request()->routeIs('sucursales.index')): ?>
                    <?php
                        $topTotalSucursales = \App\Models\Sucursal::count();
                        $topActivas = \App\Models\Sucursal::where('es_activa', true)->count();
                        $topInactivas = $topTotalSucursales - $topActivas;
                    ?>
                    <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide py-0.5">
                        <span class="inline-flex items-center gap-2.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-600 dark:bg-zinc-700 dark:text-zinc-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-6-8h6M7 4.5A1.5 1.5 0 018.5 3h7A1.5 1.5 0 0117 4.5V19a1.5 1.5 0 01-1.5 1.5h-7A1.5 1.5 0 017 19V4.5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 16h10"/></svg>
                            </span>
                            <span class="leading-none">
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Total</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topTotalSucursales }}</span>
                                    <span class="text-[11px] font-medium leading-none text-slate-500 dark:text-zinc-400">sucursales</span>
                                </span>
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-2.5 rounded-full border border-emerald-200 bg-white px-3 py-1.5 shadow-sm dark:border-emerald-500/30 dark:bg-zinc-800">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="leading-none">
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Activas</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topActivas }}</span>
                                    <span class="text-[11px] font-medium leading-none text-slate-500 dark:text-zinc-400">operativas</span>
                                </span>
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-2.5 rounded-full border px-3 py-1.5 shadow-sm {{ $topInactivas > 0 ? 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10' : 'border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-800' }}">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full {{ $topInactivas > 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400' }}">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            </span>
                            <span class="leading-none">
                                <span class="block text-[10px] font-bold uppercase tracking-widest {{ $topInactivas > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-zinc-400' }}">Inactivas</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topInactivas }}</span>
                                    <span class="text-[11px] font-medium leading-none text-slate-500 dark:text-zinc-400">ocultas</span>
                                </span>
                            </span>
                        </span>
                    </div>
                <?php elseif(request()->routeIs('proveedores.index')): ?>
                    <?php
                        $topTotalProveedores = \App\Models\Proveedor::count();
                        $topProvActivos = \App\Models\Proveedor::where('es_activo', true)->count();
                        $topProvInactivos = $topTotalProveedores - $topProvActivos;
                        $topProvCompletos = \App\Models\Proveedor::where('es_activo', true)->whereNotNull('telefono')->where('telefono','!=','')->whereNotNull('correo')->where('correo','!=','')->count();
                    ?>
                    <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide py-0.5">
                        <span class="inline-flex items-center gap-2.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-600 dark:bg-zinc-700 dark:text-zinc-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5v-.75A2.25 2.25 0 0018 4.5h-1.5a2.25 2.25 0 00-2.25 2.25v.75m-6 0V6.75A2.25 2.25 0 017.5 4.5H6A2.25 2.25 0 003.75 6.75v.75m13.5 0v6a2.25 2.25 0 01-2.25 2.25h-1.5a2.25 2.25 0 01-2.25-2.25v-6m-6 0v6a2.25 2.25 0 002.25 2.25H9A2.25 2.25 0 0011.25 13.5v-6m-6 0h13.5"/></svg>
                            </span>
                            <span class="leading-none">
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Total</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topTotalProveedores }}</span>
                                    <span class="text-[11px] font-medium leading-none text-slate-500 dark:text-zinc-400">proveedores</span>
                                </span>
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-2.5 rounded-full border border-emerald-200 bg-white px-3 py-1.5 shadow-sm dark:border-emerald-500/30 dark:bg-zinc-800">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="leading-none">
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Activos</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topProvActivos }}</span>
                                    <span class="text-[11px] font-medium leading-none text-slate-500 dark:text-zinc-400">completos</span>
                                </span>
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-2.5 rounded-full border px-3 py-1.5 shadow-sm {{ $topProvInactivos > 0 ? 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10' : 'border-slate-200 bg-white dark:border-zinc-700 dark:bg-zinc-800' }}">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full {{ $topProvInactivos > 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400' }}">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            </span>
                            <span class="leading-none">
                                <span class="block text-[10px] font-bold uppercase tracking-widest {{ $topProvInactivos > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-slate-500 dark:text-zinc-400' }}">Inactivos</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topProvInactivos }}</span>
                                    <span class="text-[11px] font-medium leading-none text-slate-500 dark:text-zinc-400">ocultos</span>
                                </span>
                            </span>
                        </span>
                    </div>
                <?php elseif(request()->routeIs('entradas-de-almacen.*')): ?>
                    <?php
                        $topSucursalIdEntradas = (int) (session('active_sucursal_id', auth()->user()?->id_sucursal ?? request()->query('sucursal') ?? 0) ?: 0);
                        if (!$topSucursalIdEntradas) {
                            $topSucursalIdEntradas = \App\Models\Sucursal::orderBy('nombre_sucursal')->value('id') ?? 0;
                        }
                        $topTipoEntradas = strtolower(trim((string) request()->query('tipo', 'todas')));
                        $topTipoEntradas = match($topTipoEntradas) {
                            'compra','compras' => 'compras',
                            'traspaso','traspasos' => 'traspasos',
                            default => 'todas',
                        };
                        $topTotalComprasEntradas = $topSucursalIdEntradas ? \App\Models\Compra::where('id_sucursal', $topSucursalIdEntradas)->count() : \App\Models\Compra::count();
                        $topTotalTraspasosEntradas = $topSucursalIdEntradas
                            ? \App\Models\Traspaso::where(function($q) use ($topSucursalIdEntradas){ $q->where('sucursal_a', $topSucursalIdEntradas)->orWhere('sucursal_b', $topSucursalIdEntradas); })->count()
                            : \App\Models\Traspaso::count();
                        $topTotalTodasEntradas = $topTotalComprasEntradas + $topTotalTraspasosEntradas;
                        $topPillsEntradas = [
                            'todas' => ['Todas las entradas', $topTotalTodasEntradas],
                            'compras' => ['Compras', $topTotalComprasEntradas],
                            'traspasos' => ['Traspasos', $topTotalTraspasosEntradas],
                        ];
                    ?>
                    <nav class="flex items-center gap-1.5 overflow-x-auto scrollbar-hide py-0.5" aria-label="Filtros de entradas">
                        @foreach($topPillsEntradas as $valor => [$etiqueta, $conteo])
                            @php($esActivoEntrada = $topTipoEntradas === $valor)
                            <a href="{{ route('entradas-de-almacen.index', array_filter(['sucursal' => $topSucursalIdEntradas, 'tipo' => $valor === 'todas' ? null : $valor])) }}" @class([
                                'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold whitespace-nowrap transition',
                                'border-[#0e9384] bg-[#0e9384] text-white shadow-sm' => $esActivoEntrada,
                                'border-slate-200 bg-white text-slate-600 hover:border-[#0e9384]/30 hover:bg-teal-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' => !$esActivoEntrada,
                            ])>
                                @if($valor === 'todas')
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                                @elseif($valor === 'compras')
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                                @else
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                @endif
                                <span>{{ $etiqueta }}</span>
                                <span class="grid min-w-[1.4rem] place-items-center rounded-full px-1.5 py-0.5 text-[11px] font-black @if($esActivoEntrada) bg-white/20 text-white @else bg-slate-100 text-slate-600 dark:bg-zinc-700 dark:text-zinc-300 @endif">{{ $conteo }}</span>
                            </a>
                        @endforeach
                    </nav>
                <?php elseif(request()->routeIs('punto-venta.*')): ?>
                    <?php
                        $topPresentaciones = \App\Models\PresentacionProducto::orderBy('presentacion')->get();
                        $topFiltroPres = (string) (request()->query('presentacion', '') ?? '');
                    ?>
                    <div class="flex flex-1 items-center gap-3 min-w-0">
                        <div class="relative hidden max-w-md flex-1 lg:flex">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 flex h-5 w-5 -translate-y-1/2 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input id="topbar-pos-search" type="search" placeholder="Buscar producto o código de barras..." class="w-full rounded-full border border-slate-200 bg-white py-2.5 pl-11 pr-3 text-sm font-medium leading-none text-slate-700 placeholder:text-slate-400 shadow-sm focus:border-[#0e9384] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0e9384]/15 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:bg-slate-800" aria-label="Buscar producto en punto de venta">
                        </div>
                        <div class="hidden items-center gap-2 overflow-x-auto scrollbar-hide pr-1 lg:flex">
                            <span class="hidden shrink-0 items-center gap-1.5 whitespace-nowrap text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 lg:inline-flex">Presentación</span>
                            <button type="button" data-topbar-presentacion="" class="topbar-pos-pill inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-bold transition border-[#0a5f56] bg-[#0e9384] text-white shadow-sm" data-active="true">
                                <span class="h-1.5 w-1.5 rounded-full bg-white"></span> Todos
                            </button>
                            @foreach($topPresentaciones as $topPres)
                                <button type="button" data-topbar-presentacion="{{ $topPres->id }}" class="topbar-pos-pill inline-flex shrink-0 items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:border-[#0e9384]/30 hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-[#3fbda9] dark:hover:bg-slate-700 dark:hover:text-white">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-500"></span> {{ ucfirst($topPres->presentacion) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                <?php elseif(request()->routeIs('caja.*')): ?>
                    <?php
                        $topSucursalIdCaja = (int) (session('active_sucursal_id', auth()->user()?->id_sucursal ?? request()->query('sucursal') ?? 0) ?: 0);
                        if (!$topSucursalIdCaja) {
                            $topSucursalIdCaja = \App\Models\Sucursal::orderBy('nombre_sucursal')->value('id') ?? 0;
                        }
                        $topAbiertasCaja = $topSucursalIdCaja ? \App\Models\CorteCaja::where('id_sucursal', $topSucursalIdCaja)->where('estado', 'abierto')->count() : 0;
                        $topEsperadoCaja = 0.0;
                        if ($topSucursalIdCaja && $topAbiertasCaja > 0) {
                            $topCortesAbiertos = \App\Models\CorteCaja::where('id_sucursal', $topSucursalIdCaja)->where('estado', 'abierto')->get();
                            foreach ($topCortesAbiertos as $topCorte) {
                                $topVentasCorte = (float) \App\Models\Venta::where('id_corte_caja', $topCorte->id)->sum('total');
                                if ($topVentasCorte === 0.0) {
                                    $topVentasCorte = (float) \App\Models\Venta::where('ventas.id_sucursal', $topCorte->id_sucursal)->where('ventas.creado_en', '>=', $topCorte->fecha_inicio)->whereNull('ventas.id_corte_caja')->join('pagos','pagos.id','=','ventas.id_pago')->where('pagos.metodo','Efectivo')->sum('ventas.total');
                                }
                                $topIngresos = (float) \App\Models\MovimientoCaja::where('id_corte_caja', $topCorte->id)->where('tipo','ingreso')->sum('monto');
                                $topRetiros = (float) \App\Models\MovimientoCaja::where('id_corte_caja', $topCorte->id)->where('tipo','retiro')->sum('monto');
                                $topEsperadoCaja += round((float)$topCorte->efectivo_inicial + $topVentasCorte + $topIngresos - $topRetiros, 2);
                            }
                        }
                        $topHoyInicio = now()->startOfDay();
                        $topVentasHoyCaja = $topSucursalIdCaja ? (float) \App\Models\Venta::where('id_sucursal', $topSucursalIdCaja)->where('creado_en','>=',$topHoyInicio)->sum('total') : 0.0;
                        $topHistorialCaja = $topSucursalIdCaja ? \App\Models\CorteCaja::where('id_sucursal', $topSucursalIdCaja)->where('estado','cerrado')->count() : 0;
                    ?>
                    <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide py-0.5">
                        <span class="inline-flex items-center gap-2.5 rounded-full border border-emerald-200 bg-white px-3 py-1.5 shadow-sm dark:border-emerald-500/30 dark:bg-zinc-800">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </span>
                            <span class="leading-none">
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Abiertas</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topAbiertasCaja }}</span>
                                    <span class="text-[11px] font-medium leading-none text-slate-500 dark:text-zinc-400">caja(s)</span>
                                </span>
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-2.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-600 dark:bg-zinc-700 dark:text-zinc-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="leading-none">
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Esperado</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black leading-none text-slate-900 dark:text-white">${{ number_format($topEsperadoCaja, 2) }}</span>
                                    <span class="text-[11px] font-medium leading-none text-slate-500 dark:text-zinc-400">abiertas</span>
                                </span>
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-2.5 rounded-full border border-sky-200 bg-white px-3 py-1.5 shadow-sm dark:border-sky-500/30 dark:bg-zinc-800">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="leading-none">
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-sky-700 dark:text-sky-300">Ventas hoy</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black leading-none text-slate-900 dark:text-white">${{ number_format($topVentasHoyCaja, 2) }}</span>
                                </span>
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-2.5 rounded-full border border-amber-200 bg-white px-3 py-1.5 shadow-sm dark:border-amber-500/30 dark:bg-zinc-800">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <span class="leading-none">
                                <span class="block text-[10px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-300">Historial</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-sm font-black leading-none text-slate-900 dark:text-white">{{ $topHistorialCaja }}</span>
                                    <span class="text-[11px] font-medium leading-none text-slate-500 dark:text-zinc-400">cierre(s)</span>
                                </span>
                            </span>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <span x-text="currentDate" class="farma-topbar-fecha"></span>
                <?php if (request()->routeIs('punto-venta.*')): ?>
                    <?php
                        $topPosSucursalId = (int) (session('active_sucursal_id', auth()->user()?->id_sucursal ?? 0) ?: 0);
                        $topPosUsuarioId = (int) (auth()->id() ?? 0);
                        $topPosCorte = null;
                        if ($topPosSucursalId) {
                            $topPosCorte = \App\Models\CorteCaja::where('id_sucursal', $topPosSucursalId)
                                ->where('estado', 'abierto')
                                ->where('id_usuario', $topPosUsuarioId)
                                ->orderByDesc('fecha_inicio')
                                ->first();
                        }
                        $topPosCajaPropia = $topPosCorte !== null && (int) $topPosCorte->id_usuario === $topPosUsuarioId;
                        $topPosTurno = $topPosCorte
                            ? (\App\Http\Controllers\CajaController::TURNOS[$topPosCorte->turno] ?? $topPosCorte->turno)
                            : null;
                    ?>
                    <div class="flex items-center gap-2">
                        <?php if ($topPosCorte): ?>
                            <span class="inline-flex items-center gap-2 rounded-full border border-[#0e9384]/20 bg-white px-3 py-1.5 shadow-sm dark:border-[#3fbda9]/30 dark:bg-[#1e3039]" title="Caja que recibirá la próxima venta">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-[#0c7569] dark:text-[#5ec8b4]"><?php echo e($topPosCajaPropia ? 'MI CAJA' : 'Caja'); ?></span>
                                <span class="text-xs font-black text-slate-800 dark:text-slate-100"><?php echo e($topPosCorte->caja); ?> · <?php echo e($topPosTurno); ?></span>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 shadow-sm dark:border-slate-600 dark:bg-slate-800" title="Las ventas están bloqueadas hasta abrir una caja">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-600 dark:text-slate-300">Sin caja abierta</span>
                            </span>
                        <?php endif; ?>

                        <?php if (! $topPosCajaPropia): ?>
                            <?php if ($topPosCorte): ?>
                                <button
                                    type="button"
                                    x-on:click="$dispatch('pos-activar-caja')"
                                    class="theme-button theme-button-secondary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap"
                                    title="Abrir otra caja en esta sucursal"
                                >
                                    Abrir otra caja
                                </button>
                            <?php else: ?>
                                <button
                                    type="button"
                                    x-on:click="$dispatch('pos-activar-caja')"
                                    class="theme-button theme-button-primary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap"
                                    title="Abrir tu caja y elegir turno sin salir del punto de venta"
                                >
                                    Activar caja
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($topPosCajaPropia): ?>
                            <button
                                type="button"
                                x-on:click="$dispatch('pos-cerrar-caja')"
                                class="theme-button theme-button-primary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap"
                                title="Cerrar tu caja y arquear el turno"
                            >
                                Cerrar caja
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                @if(request()->routeIs('inventario.productos') && (auth()->user()?->permisosHabilitados('crear', 'inventario') ?? false))
                    @php($topProductoSucursal = request()->query('sucursal') ?? session('active_sucursal_id', auth()->user()?->id_sucursal))
                    <a href="{{ route('inventario.create', array_filter(['sucursal' => $topProductoSucursal])) }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap">+ Agregar producto nuevo</a>
                @endif
                @if(request()->routeIs('entradas-de-almacen.*') && (auth()->user()?->permisosHabilitados('crear', 'entradas-de-almacen') ?? false))
                    @php($topEntradasSucursal = request()->query('sucursal') ?? session('active_sucursal_id', auth()->user()?->id_sucursal))
                    @if(Route::has('compras.create'))
                        <a href="{{ route('compras.create', array_filter(['sucursal' => $topEntradasSucursal])) }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap">+ Nueva compra</a>
                    @endif
                    @if(Route::has('traspasos.create'))
                        <a href="{{ route('traspasos.create', array_filter(['sucursal' => $topEntradasSucursal])) }}" class="theme-button theme-button-secondary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap">⇄ Nuevo traspaso</a>
                    @endif
                @endif
                @if(request()->routeIs('usuarios.*') && (auth()->user()?->permisosHabilitados('crear', 'usuarios') ?? false))
                    <button type="button" x-on:click="$dispatch('open-modal-rol')" class="theme-button theme-button-secondary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap">+ Nuevo rol</button>
                    <a href="{{ route('usuarios.create') }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap">+ Nuevo usuario</a>
                @endif
                @if(request()->routeIs('caja.index'))
                    <?php $cajaTopMiAbierta_tmp = \App\Models\CorteCaja::where('id_usuario', (int) (auth()->id() ?? 0))->where('estado','abierto')->exists(); ?>
                    @if(! $cajaTopMiAbierta_tmp)
                    <button type="button" x-on:click="$dispatch('open-abrir-caja')" class="group inline-flex items-center gap-2 rounded-full bg-[#0e9384] px-4 py-2 text-xs font-bold text-white shadow-sm ring-1 ring-[#0e9384]/20 hover:bg-[#0c7a6e] hover:shadow-md hover:-translate-y-px active:translate-y-0 transition-all" title="Abrir nueva caja">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-white text-[#0e9384] shadow-sm ring-1 ring-black/5 transition-transform group-hover:rotate-90">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </span>
                        Abrir caja
                    </button>
                    @endif
                    <button type="button" x-on:click="$dispatch('open-resumen-dia')" class="group inline-flex items-center gap-2 rounded-full bg-[#0e9384] px-4 py-2 text-xs font-bold text-white shadow-sm ring-1 ring-[#0e9384]/20 hover:bg-[#0c7a6e] hover:shadow-md hover:-translate-y-px active:translate-y-0 transition-all" title="Ver resumen del día">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-white text-[#0e9384] shadow-sm ring-1 ring-black/5">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M3 13h2.5a2 2 0 012-2H9a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6zM11 9h2.5a2 2 0 012-2H17a2 2 0 012 2v8a2 2 0 01-2 2h-2.5a2 2 0 01-2-2V9zM19 15h1a2 2 0 012 2v2a2 2 0 01-2 2h-1"/></svg>
                        </span>
                        Resumen
                    </button>
                    @php($cajaTopBtnSucursal = request()->query('sucursal') ?? session('active_sucursal_id', auth()->user()?->id_sucursal))
                    <a href="{{ route('caja.auditoria', array_filter(['sucursal' => $cajaTopBtnSucursal])) }}" class="group inline-flex items-center gap-2 rounded-full bg-[#0e9384] px-4 py-2 text-xs font-bold text-white shadow-sm ring-1 ring-[#0e9384]/20 hover:bg-[#0c7a6e] hover:shadow-md hover:-translate-y-px active:translate-y-0 transition-all" title="Historial y auditoría en ventana dedicada">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-white text-[#0e9384] shadow-sm ring-1 ring-black/5">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 12h4M12 16h4M9 12h.01M9 16h.01"/></svg>
                        </span>
                        Historial
                    </a>
                @elseif(request()->routeIs('caja.auditoria'))
                    @php($cajaVolverSucursal = request()->query('sucursal') ?? session('active_sucursal_id', auth()->user()?->id_sucursal))
                    <a href="{{ route('caja.index', array_filter(['sucursal' => $cajaVolverSucursal])) }}" class="group inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 transition-all" title="Volver a operación de caja">
                        <span class="grid h-6 w-6 place-items-center rounded-full bg-slate-100 text-slate-600 ring-1 ring-slate-200 group-hover:bg-slate-200 dark:bg-zinc-700 dark:text-zinc-300 dark:ring-zinc-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        </span>
                        Volver a caja
                    </a>
                @endif
                <livewire:campana-notificaciones />
                </div>
            </div>
            </div>
        </div>

        {{ $slot }}

        @include('partials.alertas-toasts')
        @include('partials.notificacion-detalle-modal')

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
