<div class="module-page noti-page">
    <div class="module-page-inner noti-full noti-formal">
    <section class="module-card noti-listado" aria-label="Ajustes de cuenta">
        <div class="p-5 sm:p-6">
        <div class="mb-4 flex">
            <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-600 shadow-sm hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
        </div>
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
