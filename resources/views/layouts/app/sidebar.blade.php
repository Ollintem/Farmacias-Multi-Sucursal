<!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#e9efec] text-[#22332f] dark:bg-[#1a2a32] dark:text-[#e6efec]" x-data="{ currentDate: '' }" x-init="updateDate(); setInterval(updateDate, 60000)">
        <flux:sidebar id="app-sidebar" sticky collapsible class="erp-sidebar border-e border-[#2c545e] bg-[#20444c] text-[#c3d4cf] dark:bg-[#1e3039]">
            <flux:sidebar.header>
                <flux:sidebar.brand name="FarmaERP" href="{{ route('dashboard') }}" wire:navigate>
                    <x-slot name="logo" class="erp-logo flex aspect-square size-8 items-center justify-center rounded-xl bg-[#0e9384] text-white shadow-lg shadow-black/20">
                        <span class="text-xs font-black">Rx</span>
                    </x-slot>
                </flux:sidebar.brand>
                <flux:sidebar.collapse class="text-[#c3d4cf] hover:text-white" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="erp-nav">
                @php($usuarioActual = auth()->user())
                @if($usuarioActual?->puedeVerModulo('Dashboard') ?? false)
                <flux:sidebar.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    Dashboard
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Punto de venta') ?? false)
                <flux:sidebar.item icon="receipt-percent" :href="route('punto-venta.index')" :current="request()->routeIs('punto-venta.*')" wire:navigate>
                    Punto de venta
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Inventario') ?? false)
                <flux:sidebar.item icon="archive-box" :href="route('inventario.productos')" :current="request()->routeIs('inventario.*')" wire:navigate>
                    Inventario
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Entradas de almacén') ?? false)
                <flux:sidebar.item icon="inbox-arrow-down" :href="route('entradas-de-almacen.index')" :current="request()->routeIs('entradas-de-almacen.*')" wire:navigate>
                    Entradas de almacén
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Proveedores') ?? false)
                <flux:sidebar.item icon="truck" :href="route('proveedores.index')" :current="request()->routeIs('proveedores.*')" wire:navigate>
                    Proveedores
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Sucursales') ?? false)
                <flux:sidebar.item icon="map-pin" :href="route('sucursales.index')" :current="request()->routeIs('sucursales.*')" wire:navigate>
                    Sucursales
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Usuarios y roles') ?? false)
                <flux:sidebar.item icon="users" :href="route('usuarios.index')" :current="request()->routeIs('usuarios.*')" wire:navigate>
                    Usuarios y roles
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Caja') ?? false)
                <flux:sidebar.item icon="banknotes" :href="route('caja.index')" :current="request()->routeIs('caja.*')" wire:navigate>
                    Caja
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Reportes') ?? false)
                <flux:sidebar.item icon="list-bullet" :href="route('reportes.index')" :current="request()->routeIs('reportes.*')" wire:navigate>
                    Reportes
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Alertas') ?? false)
                @php($alertasTotal = \App\Support\AlertasFeed::noLeidasCount((int) (session('active_sucursal_id', $usuarioActual?->id_sucursal ?? 0) ?: 0), $usuarioActual?->id))
                <flux:sidebar.item icon="bell" :href="route('alertas.index')" :current="request()->routeIs('alertas.*')" :badge="$alertasTotal > 0 ? $alertasTotal : null" wire:navigate>
                    Notificaciones
                </flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="erp-sidebar-version px-3 pb-2 text-xs"></div>

            {{-- Selector de Sucursal Global (mismo estilo que usuario) --}}
            <flux:dropdown position="bottom" align="start" class="erp-user-menu">
                <flux:sidebar.profile
                    :name="\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))?->nombre_sucursal ?? 'Seleccionar sucursal'"
                    :initials="\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))?->nombre_sucursal ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))->nombre_sucursal, 0, 1)) : 'S'"
                    icon:trailing="chevrons-up-down"
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
            @if(request()->routeIs('lotes.*') && (auth()->user()?->permisosHabilitados('crear', 'lotes-y-caducidades') ?? false))
                @php($topLoteSucursalMobile = request()->query('sucursal') ?? session('active_sucursal_id', auth()->user()?->id_sucursal))
                <a href="{{ route('lotes.create', array_filter(['sucursal' => $topLoteSucursalMobile])) }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-2.5 !text-xs shrink-0 whitespace-nowrap">+ Nuevo lote</a>
            @endif
            @if(request()->routeIs('usuarios.*') && (auth()->user()?->permisosHabilitados('crear', 'usuarios') ?? false))
                <button type="button" x-on:click="$dispatch('open-modal-rol')" class="theme-button theme-button-secondary !min-h-0 !py-2 !px-2.5 !text-xs shrink-0 whitespace-nowrap">+ Nuevo rol</button>
                <a href="{{ route('usuarios.create') }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-2.5 !text-xs shrink-0 whitespace-nowrap">+ Nuevo usuario</a>
            @endif
            <livewire:campana-notificaciones />

            <x-theme-toggle :compacto="true" />

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
                            {{ __('Settings') }}
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

        {{-- Barra superior de escritorio: herramientas según módulo + campana + tema --}}
        <div class="farma-topbar hidden lg:flex !justify-between">
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
                    <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide">
                        @include('pages.inventario.tabs', ['seccion' => $topSeccion, 'selectedSucursal' => $topSucursalTb])
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
                            'pedido','pedidos' => 'pedidos',
                            'traspaso','traspasos' => 'traspasos',
                            default => 'todas',
                        };
                        $topTotalPedidosEntradas = $topSucursalIdEntradas ? \App\Models\Pedido::where('id_sucursal', $topSucursalIdEntradas)->count() : \App\Models\Pedido::count();
                        $topTotalTraspasosEntradas = $topSucursalIdEntradas
                            ? \App\Models\Traspaso::where(function($q) use ($topSucursalIdEntradas){ $q->where('sucursal_a', $topSucursalIdEntradas)->orWhere('sucursal_b', $topSucursalIdEntradas); })->count()
                            : \App\Models\Traspaso::count();
                        $topTotalTodasEntradas = $topTotalPedidosEntradas + $topTotalTraspasosEntradas;
                        $topPillsEntradas = [
                            'todas' => ['Todas las entradas', $topTotalTodasEntradas],
                            'pedidos' => ['Pedidos', $topTotalPedidosEntradas],
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
                                @elseif($valor === 'pedidos')
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
                            <span class="pointer-events-none absolute left-3.5 top-1/2 flex h-5 w-5 -translate-y-1/2 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-zinc-700 dark:text-zinc-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input id="topbar-pos-search" type="search" placeholder="Buscar producto o código de barras..." class="w-full rounded-full border border-slate-200 bg-white py-2.5 pl-11 pr-3 text-sm font-medium leading-none text-slate-700 placeholder:text-slate-400 shadow-sm focus:border-[#0e9384] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0e9384]/15 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500" aria-label="Buscar producto en punto de venta">
                        </div>
                        <div class="hidden items-center gap-2 overflow-x-auto scrollbar-hide pr-1 lg:flex">
                            <span class="hidden shrink-0 items-center gap-1.5 whitespace-nowrap text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400 lg:inline-flex">Presentación</span>
                            <button type="button" data-topbar-presentacion="" class="topbar-pos-pill inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-bold transition border-[#0a5f56] bg-[#0e9384] text-white shadow-sm" data-active="true">
                                <span class="h-1.5 w-1.5 rounded-full bg-white"></span> Todos
                            </button>
                            @foreach($topPresentaciones as $topPres)
                                <button type="button" data-topbar-presentacion="{{ $topPres->id }}" class="topbar-pos-pill inline-flex shrink-0 items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:border-[#0e9384]/30 hover:bg-emerald-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-zinc-500"></span> {{ ucfirst($topPres->presentacion) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <span x-text="currentDate" class="farma-topbar-fecha"></span>
                @if(request()->routeIs('inventario.productos') && (auth()->user()?->permisosHabilitados('crear', 'inventario') ?? false))
                    @php($topProductoSucursal = request()->query('sucursal') ?? session('active_sucursal_id', auth()->user()?->id_sucursal))
                    <a href="{{ route('inventario.create', array_filter(['sucursal' => $topProductoSucursal])) }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap">+ Agregar producto nuevo</a>
                @endif
                @if(request()->routeIs('lotes.*') && (auth()->user()?->permisosHabilitados('crear', 'lotes-y-caducidades') ?? false))
                    @php($topLoteSucursal = request()->query('sucursal') ?? session('active_sucursal_id', auth()->user()?->id_sucursal))
                    <a href="{{ route('lotes.create', array_filter(['sucursal' => $topLoteSucursal])) }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap">+ Registrar nuevo lote</a>
                @endif
                @if(request()->routeIs('usuarios.*') && (auth()->user()?->permisosHabilitados('crear', 'usuarios') ?? false))
                    <button type="button" x-on:click="$dispatch('open-modal-rol')" class="theme-button theme-button-secondary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap">+ Nuevo rol</button>
                    <a href="{{ route('usuarios.create') }}" class="theme-button theme-button-primary !min-h-0 !py-2 !px-3 !text-xs shrink-0 whitespace-nowrap">+ Nuevo usuario</a>
                @endif
                <livewire:campana-notificaciones />
                <x-theme-toggle :compacto="true" />
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
