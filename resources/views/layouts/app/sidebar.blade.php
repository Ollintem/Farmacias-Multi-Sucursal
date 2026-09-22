<!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#eef3f8] dark:bg-zinc-950" x-data="{ currentDate: '' }" x-init="updateDate(); setInterval(updateDate, 60000)">
        <flux:sidebar sticky collapsible="true" class="erp-sidebar border-e border-[#202b43] bg-[#0f172a] text-[#a9b8d3]">
            <flux:sidebar.header>
                <flux:sidebar.brand name="FarmaERP" href="{{ route('dashboard') }}" wire:navigate>
                    <x-slot name="logo" class="erp-logo flex aspect-square size-8 items-center justify-center rounded-lg bg-[#0c9f9c] text-white">
                        <span class="text-xs font-bold">Rx</span>
                    </x-slot>
                </flux:sidebar.brand>
                <flux:sidebar.collapse class="text-[#a9b8d3]" />
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
                <flux:sidebar.item icon="archive-box" :href="route('inventario.index')" :current="request()->routeIs('inventario.*')" wire:navigate>
                    Inventario
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Entradas de almacén') ?? false)
                <flux:sidebar.item icon="inbox-arrow-down" :href="route('entradas-de-almacen.index')" :current="request()->routeIs('entradas-de-almacen.*')" wire:navigate>
                    Entradas de almacén
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Traspasos') ?? false)
                <flux:sidebar.item icon="arrows-right-left" :href="route('traspasos.index')" :current="request()->routeIs('traspasos.*')" wire:navigate>
                    Traspasos
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
                <flux:sidebar.item icon="flag" :href="route('alertas.index')" :current="request()->routeIs('alertas.*')" wire:navigate>
                    Alertas
                </flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <div class="erp-sidebar-version px-3 pb-2 text-xs">v2.4.1 - Julio 2026</div>

            {{-- Selector de Sucursal Global (mismo estilo que usuario) --}}
            <flux:dropdown position="bottom" align="start" class="erp-user-menu">
                <flux:sidebar.profile
                    :name="\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))?->nombre_sucursal ?? 'Seleccionar sucursal'"
                    :initials="\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))?->nombre_sucursal ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))->nombre_sucursal, 0, 1)) : 'S'"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-56">
                    @foreach(\App\Models\Sucursal::orderBy('nombre_sucursal')->get() as $sucursal)
                        <a
                            href="{{ request()->fullUrlWithQuery(array_merge(request()->query(), ['sucursal' => $sucursal->id])) }}"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-[#a9b8d3] hover:bg-[#1e293b] rounded-lg transition
                                {{ (session('active_sucursal_id', auth()->user()?->id_sucursal ?? '')) == $sucursal->id ? 'bg-[#0c9f9c]/20 text-[#0c9f9c] font-medium' : '' }}"
                            wire:navigate
                        >
                            <span class="w-5 h-5 flex items-center justify-center rounded bg-[#0c9f9c]/20 text-[#0c9f9c] text-xs font-bold">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sucursal->nombre_sucursal, 0, 1)) }}
                            </span>
                            <span class="truncate">{{ $sucursal->nombre_sucursal }}</span>
                            @if((session('active_sucursal_id', auth()->user()?->id_sucursal ?? '')) == $sucursal->id)
                                <svg class="ml-auto w-4 h-4 text-[#0c9f9c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @endif
                        </a>
                    @endforeach
                </flux:menu>
            </flux:dropdown>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden" x-data="{ currentDate: '' }" x-init="updateDate(); setInterval(updateDate, 60000)">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            {{-- Selector Sucursal Mobile (mismo estilo) --}}
            <flux:dropdown position="bottom" align="start" class="mr-2 erp-user-menu">
                <flux:sidebar.profile
                    :name="\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))?->nombre_sucursal ?? 'Seleccionar sucursal'"
                    :initials="\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))?->nombre_sucursal ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(\App\Models\Sucursal::find(session('active_sucursal_id', auth()->user()?->id_sucursal))->nombre_sucursal, 0, 1)) : 'S'"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-48">
                    @foreach(\App\Models\Sucursal::orderBy('nombre_sucursal')->get() as $sucursal)
                        <a
                            href="{{ request()->fullUrlWithQuery(array_merge(request()->query(), ['sucursal' => $sucursal->id])) }}"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-[#a9b8d3] hover:bg-[#1e293b] rounded-lg transition
                                {{ (session('active_sucursal_id', auth()->user()?->id_sucursal ?? '')) == $sucursal->id ? 'bg-[#0c9f9c]/20 text-[#0c9f9c] font-medium' : '' }}"
                            wire:navigate
                        >
                            <span class="w-5 h-5 flex items-center justify-center rounded bg-[#0c9f9c]/20 text-[#0c9f9c] text-xs font-bold">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sucursal->nombre_sucursal, 0, 1)) }}
                            </span>
                            <span class="truncate">{{ $sucursal->nombre_sucursal }}</span>
                            @if((session('active_sucursal_id', auth()->user()?->id_sucursal ?? '')) == $sucursal->id)
                                <svg class="ml-auto w-4 h-4 text-[#0c9f9c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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
                document.querySelector('[x-text="currentDate"]')?.textContent = formatted;
            }
        </script>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
