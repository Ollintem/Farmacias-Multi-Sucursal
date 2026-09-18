<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#eef3f8] dark:bg-zinc-950">
        <flux:sidebar sticky collapsible="mobile" class="erp-sidebar border-e border-[#202b43] bg-[#0f172a] text-[#a9b8d3]">
            <flux:sidebar.header>
                <flux:sidebar.brand name="FarmaERP" href="{{ route('dashboard') }}" wire:navigate>
                    <x-slot name="logo" class="erp-logo flex aspect-square size-8 items-center justify-center rounded-lg bg-[#0c9f9c] text-white">
                        <span class="text-xs font-bold">Rx</span>
                    </x-slot>
                </flux:sidebar.brand>
                <flux:sidebar.collapse class="lg:hidden text-[#a9b8d3]" />
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
                @if($usuarioActual?->puedeVerModulo('Lotes y caducidades') ?? false)
                <flux:sidebar.item icon="clock" :href="route('lotes.index')" :current="request()->routeIs('lotes.*')" wire:navigate>
                    Lotes y caducidades
                </flux:sidebar.item>
                @endif
                @if($usuarioActual?->puedeVerModulo('Entradas de almacén') ?? false)
                <flux:sidebar.item icon="inbox-arrow-down" :href="route('entradas.index')" :current="request()->routeIs('entradas.*')" wire:navigate>
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

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

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
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
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

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
