<div class="module-page noti-page">
    <div class="module-page-inner noti-full noti-formal">
    {{-- Encabezado limpio --}}
    <header class="mb-6" aria-label="Encabezado de ajustes">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Cuenta · {{ $heading ?? 'Ajustes' }}</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $heading ?? 'Ajustes' }}</h1>
        </div>
            </header>

    <section class="module-card noti-listado" aria-label="Ajustes de cuenta">
        <div class="p-5 sm:p-6">
        <div id="theme-shell" class="settings-shell">
    <div class="theme-shell-inner">
        <div class="flex items-start max-md:flex-col">
            <div class="me-10 w-full pb-4 md:w-[220px]">
                <flux:navlist aria-label="Ajustes">
                    <flux:navlist.item :href="route('profile.edit')" wire:navigate>Perfil</flux:navlist.item>
                    <flux:navlist.item :href="route('security.edit')" wire:navigate>Seguridad</flux:navlist.item>
                    <flux:navlist.item :href="route('appearance.edit')" wire:navigate>Apariencia</flux:navlist.item>
                </flux:navlist>
            </div>

            <flux:separator class="md:hidden" />

            <div class="flex-1 self-stretch max-md:pt-6">
                <flux:heading>{{ $heading ?? '' }}</flux:heading>
                <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

                <div class="theme-card mt-5 w-full max-w-lg">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
        </div>
    </section>
    </div>
</div>
