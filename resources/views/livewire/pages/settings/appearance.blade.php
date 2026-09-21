<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">Ajustes de apariencia</flux:heading>

    <div id="theme-shell" class="theme-light settings-shell">
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
                    <flux:heading>Apariencia</flux:heading>
                    <flux:subheading>Elige el tema de FarmaERP, igual que el interruptor del dashboard</flux:subheading>

                    <div class="theme-card mt-5 w-full max-w-lg">
                        <div class="grid gap-4 sm:grid-cols-3" role="radiogroup" aria-label="Tema de la aplicacion">
                            <button type="button" data-tema-card="claro" role="radio" aria-checked="false" class="tema-card rounded-2xl border p-4 text-left transition">
                                <span class="tema-preview tema-preview-claro flex h-20 items-center justify-center rounded-xl">
                                    <span class="theme-switch" aria-hidden="true">
                                        <span class="theme-switch-track">
                                            <span class="theme-switch-thumb"></span>
                                        </span>
                                    </span>
                                </span>
                                <span class="mt-3 flex items-center justify-between">
                                    <span class="text-base font-semibold">Claro</span>
                                    <span data-tema-check class="hidden rounded-full bg-emerald-500/15 px-2 py-0.5 text-xs font-semibold text-emerald-600">Activo</span>
                                </span>
                                <span class="mt-1 block text-sm text-slate-500">Fondo claro para el dia.</span>
                            </button>

                            <button type="button" data-tema-card="oscuro" role="radio" aria-checked="false" class="tema-card rounded-2xl border p-4 text-left transition">
                                <span class="tema-preview tema-preview-oscuro flex h-20 items-center justify-center rounded-xl">
                                    <span class="theme-switch" aria-hidden="true">
                                        <span class="theme-switch-track">
                                            <span class="theme-switch-thumb"></span>
                                        </span>
                                    </span>
                                </span>
                                <span class="mt-3 flex items-center justify-between">
                                    <span class="text-base font-semibold">Oscuro</span>
                                    <span data-tema-check class="hidden rounded-full bg-emerald-500/15 px-2 py-0.5 text-xs font-semibold text-emerald-600">Activo</span>
                                </span>
                                <span class="mt-1 block text-sm text-slate-500">Fondo oscuro para la noche.</span>
                            </button>

                            <button type="button" data-tema-card="sistema" role="radio" aria-checked="false" class="tema-card rounded-2xl border p-4 text-left transition">
                                <span class="tema-preview tema-preview-sistema flex h-20 items-center justify-center rounded-xl">
                                    <svg class="h-8 w-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12.75h-18a1.5 1.5 0 00-1.5 1.5v12a1.5 1.5 0 001.5 1.5h18a1.5 1.5 0 001.5-1.5v-12a1.5 1.5 0 00-1.5-1.5z" />
                                    </svg>
                                </span>
                                <span class="mt-3 flex items-center justify-between">
                                    <span class="text-base font-semibold">Sistema</span>
                                    <span data-tema-check class="hidden rounded-full bg-emerald-500/15 px-2 py-0.5 text-xs font-semibold text-emerald-600">Activo</span>
                                </span>
                                <span class="mt-1 block text-sm text-slate-500">Sigue el tema de tu dispositivo.</span>
                            </button>
                        </div>

                        <p class="mt-4 text-sm text-slate-500">El cambio se aplica de inmediato y se guarda para todas las vistas.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const shell = document.getElementById('theme-shell');
            const cards = [...document.querySelectorAll('[data-tema-card]')];
            const media = window.matchMedia('(prefers-color-scheme: dark)');
            let modo = localStorage.getItem('farmacia-theme-modo') || 'claro';

            const esOscuro = (modoActual) => {
                if (modoActual === 'oscuro') return true;
                if (modoActual === 'claro') return false;
                return media.matches;
            };

            const pintarSeleccion = () => {
                cards.forEach((card) => {
                    const activa = card.dataset.temaCard === modo;
                    card.setAttribute('aria-checked', activa ? 'true' : 'false');
                    card.classList.toggle('tema-card-activa', activa);
                    card.querySelector('[data-tema-check]')?.classList.toggle('hidden', !activa);
                });
            };

            const aplicar = (modoNuevo) => {
                modo = modoNuevo;
                const dark = esOscuro(modo);
                shell.classList.toggle('theme-dark', dark);
                shell.classList.toggle('theme-light', !dark);
                localStorage.setItem('farmacia-theme-modo', modo);
                localStorage.setItem('farmacia-theme', dark ? 'dark' : 'light');
                pintarSeleccion();
            };

            cards.forEach((card) => {
                card.addEventListener('click', () => aplicar(card.dataset.temaCard));
            });

            media.addEventListener('change', () => {
                if (modo === 'sistema') aplicar('sistema');
            });

            aplicar(modo);
        })();
    </script>
</section>
