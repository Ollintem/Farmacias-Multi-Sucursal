<section class="w-full" x-data="{
    modo: '{{ $modo }}',
    init() {
        const guardado = localStorage.getItem('farmacia-theme-modo');
        if (guardado) { this.modo = guardado; }
        this.aplicar(this.modo, false);
    },
    aplicar(nuevoModo, guardar = true) {
        this.modo = nuevoModo;
        if (window.FarmaTheme) {
            window.FarmaTheme.aplicar(nuevoModo);
        } else {
            const media = window.matchMedia('(prefers-color-scheme: dark)');
            const dark = nuevoModo === 'oscuro' ? true : nuevoModo === 'claro' ? false : media.matches;
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.classList.toggle('theme-dark', dark);
            document.documentElement.classList.toggle('theme-light', !dark);
            localStorage.setItem('farmacia-theme-modo', nuevoModo);
            localStorage.setItem('farmacia-theme', dark ? 'dark' : 'light');
        }
        if (guardar) { $wire.cambiarTema(nuevoModo); }
    }
}">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">Ajustes de apariencia</flux:heading>

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
            <flux:heading>Apariencia</flux:heading>
            <flux:subheading>Teal medio para salud y calma, petróleo para confianza médica. Sin blancos puros ni negros puros.</flux:subheading>

            <div class="mt-5 w-full max-w-lg">
                <div class="grid gap-4 sm:grid-cols-3" role="radiogroup" aria-label="Tema de la aplicacion">
                    <button
                        type="button"
                        x-on:click="aplicar('claro')"
                        role="radio"
                        :aria-checked="modo === 'claro'"
                        class="rounded-2xl border p-4 text-left transition"
                        :class="modo === 'claro' ? 'border-[#0e9384] ring-2 ring-[#0e9384]/25 bg-[#f7faf9]' : 'border-[#cfddd7] dark:border-[#33505c]'"
                    >
                        <span class="flex h-20 items-center justify-center rounded-xl bg-[#eef5f2] border border-[#cfddd7]">
                            <span class="theme-switch" aria-hidden="true">
                                <span class="theme-switch-track">
                                    <span class="theme-switch-thumb"></span>
                                </span>
                            </span>
                        </span>
                        <span class="mt-3 flex items-center justify-between">
                            <span class="text-base font-semibold">Claro</span>
                            <span x-show="modo === 'claro'" class="rounded-full bg-[#0e9384]/15 px-2 py-0.5 text-xs font-semibold text-[#0c7569]">Activo</span>
                        </span>
                        <span class="mt-1 block text-sm text-slate-500">Salvia suave, ideal para el turno de día.</span>
                    </button>

                    <button
                        type="button"
                        x-on:click="aplicar('oscuro')"
                        role="radio"
                        :aria-checked="modo === 'oscuro'"
                        class="rounded-2xl border p-4 text-left transition"
                        :class="modo === 'oscuro' ? 'border-[#0e9384] ring-2 ring-[#0e9384]/25' : 'border-slate-200 dark:border-zinc-700'"
                    >
                        <span class="flex h-20 items-center justify-center rounded-xl bg-[#1e3039] border border-[#33505c]">
                            <span class="theme-switch" aria-hidden="true">
                                <span class="theme-switch-track">
                                    <span class="theme-switch-thumb"></span>
                                </span>
                            </span>
                        </span>
                        <span class="mt-3 flex items-center justify-between">
                            <span class="text-base font-semibold">Oscuro</span>
                            <span x-show="modo === 'oscuro'" class="rounded-full bg-[#0e9384]/15 px-2 py-0.5 text-xs font-semibold text-emerald-600">Activo</span>
                        </span>
                        <span class="mt-1 block text-sm text-slate-500">Petróleo suave, descansa la vista de noche.</span>
                    </button>

                    <button
                        type="button"
                        x-on:click="aplicar('sistema')"
                        role="radio"
                        :aria-checked="modo === 'sistema'"
                        class="rounded-2xl border p-4 text-left transition"
                        :class="modo === 'sistema' ? 'border-[#0e9384] ring-2 ring-[#0e9384]/25' : 'border-slate-200 dark:border-zinc-700'"
                    >
                        <span class="flex h-20 items-center justify-center rounded-xl bg-gradient-to-br from-white to-zinc-900 border border-slate-200">
                            <svg class="h-8 w-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12.75h-18a1.5 1.5 0 00-1.5 1.5v12a1.5 1.5 0 001.5 1.5h18a1.5 1.5 0 001.5-1.5v-12a1.5 1.5 0 00-1.5-1.5z" />
                            </svg>
                        </span>
                        <span class="mt-3 flex items-center justify-between">
                            <span class="text-base font-semibold">Sistema</span>
                            <span x-show="modo === 'sistema'" class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-xs font-semibold text-emerald-600">Activo</span>
                        </span>
                        <span class="mt-1 block text-sm text-slate-500">Sigue el tema de tu dispositivo.</span>
                    </button>
                </div>

                <p class="mt-4 text-sm text-slate-500">El cambio se aplica de inmediato y se guarda para todas las vistas.</p>
            </div>
        </div>
    </div>
</section>
