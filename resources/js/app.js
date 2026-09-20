import './barcode-scanner';

/**
 * Tema global de FarmaERP.
 *
 * Aplica en todas las vistas con #theme-shell el modo elegido en
 * Ajustes > Apariencia (claro / oscuro / sistema). Ese es el único
 * control del tema; las vistas no tienen interruptores propios.
 */
(() => {
    const shell = document.getElementById('theme-shell');

    if (! shell) {
        return;
    }

    const media = window.matchMedia('(prefers-color-scheme: dark)');

    const modoGuardado = () => localStorage.getItem('farmacia-theme-modo')
        || (localStorage.getItem('farmacia-theme') === 'dark' ? 'oscuro' : 'claro');

    const esOscuro = () => {
        const modo = modoGuardado();

        if (modo === 'oscuro') {
            return true;
        }

        if (modo === 'claro') {
            return false;
        }

        return media.matches;
    };

    const aplicar = () => {
        const dark = esOscuro();

        shell.classList.toggle('theme-dark', dark);
        shell.classList.toggle('theme-light', ! dark);
    };

    aplicar();

    media.addEventListener('change', aplicar);

    window.addEventListener('storage', (event) => {
        if (event.key === null || event.key.startsWith('farmacia-theme')) {
            aplicar();
        }
    });
})();
