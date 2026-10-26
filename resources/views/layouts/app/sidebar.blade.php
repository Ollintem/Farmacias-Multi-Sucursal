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
