import './barcode-scanner';

/**
 * Tema global de FarmaERP.
 *
 * Psicología del color: teal medio (#0e9384) para salud/confianza,
 * petróleo (#245a6b) para profesionalismo. Fondos medios, nunca
 * blanco puro ni negro puro, para reducir fatiga visual en farmacia.
 *
 * Modos: "claro" | "oscuro" | "sistema". Se guarda en localStorage
 * bajo "farmacia-theme-modo".
 */
(() => {
    const media = window.matchMedia('(prefers-color-scheme: dark)');

    const modoGuardado = () => localStorage.getItem('farmacia-theme-modo') || 'claro';

    const esOscuro = (modo) => {
        if (modo === 'oscuro') {
            return true;
        }
        if (modo === 'claro') {
            return false;
        }
        return media.matches;
    };

    const aplicar = (modoForzado) => {
        const modo = modoForzado || modoGuardado();
        const dark = esOscuro(modo);

        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.classList.toggle('theme-dark', dark);
        document.documentElement.classList.toggle('theme-light', !dark);
        document.documentElement.style.colorScheme = dark ? 'dark' : 'light';

        localStorage.setItem('farmacia-theme-modo', modo);
        localStorage.setItem('farmacia-theme', dark ? 'dark' : 'light');

        try {
            window.Flux?.applyAppearance(modo === 'sistema' ? 'system' : dark ? 'dark' : 'light');
        } catch (e) {}

        document.dispatchEvent(new CustomEvent('farma-theme-cambiado', { detail: { modo, dark } }));
        sincronizarEtiquetas(dark);

        return { modo, dark };
    };

    const alternar = () => {
        const actual = modoGuardado();
        const oscuroActual = esOscuro(actual);
        return aplicar(oscuroActual ? 'claro' : 'oscuro');
    };

    const sincronizarEtiquetas = (dark) => {
        document.querySelectorAll('[data-farma-theme-label]').forEach((el) => {
            el.textContent = dark ? 'Modo oscuro' : 'Modo claro';
        });
        document.querySelectorAll('[data-farma-theme-icon-sun]').forEach((el) => {
            el.classList.toggle('hidden', dark);
        });
        document.querySelectorAll('[data-farma-theme-icon-moon]').forEach((el) => {
            el.classList.toggle('hidden', !dark);
        });
    };

    window.FarmaTheme = {
        aplicar,
        alternar,
        modo: modoGuardado,
        esOscuro: () => esOscuro(modoGuardado()),
    };

    document.addEventListener('click', (event) => {
        const boton = event.target.closest('[data-farma-theme-toggle]');
        if (boton) {
            event.preventDefault();
            alternar();
        }
    });

    aplicar();
    sincronizarEtiquetas(esOscuro(modoGuardado()));

    // Reaplica el tema tras cada navegación (Livewire / Flux) para que
    // el modo elegido no se pierda al cambiar de vista. Solo cambia
    // cuando el usuario pulsa el interruptor del panel lateral.
    document.addEventListener('livewire:navigated', () => {
        aplicar();
    });

    media.addEventListener('change', () => {
        if (modoGuardado() === 'sistema') {
            aplicar('sistema');
        }
    });

    window.addEventListener('storage', (event) => {
        if (event.key === null || event.key.startsWith('farmacia-theme')) {
            aplicar();
        }
    });
})();
