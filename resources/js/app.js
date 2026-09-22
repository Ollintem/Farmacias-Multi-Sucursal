import './barcode-scanner';

/**
 * Tema global de FarmaERP.
 *
 * Alterna la clase "dark" en <html> (para las variantes dark: de Tailwind)
 * y las clases theme-dark / theme-light en <html> (para los estilos CSS
 * del body). La preferencia se guarda en localStorage bajo
 * "farmacia-theme-modo" (claro | oscuro | sistema).
 */
(() => {
    const media = window.matchMedia('(prefers-color-scheme: dark)');

    const modoGuardado = () => localStorage.getItem('farmacia-theme-modo') || 'claro';

    const esOscuro = (modo) => {
        if (modo === 'oscuro') return true;
        if (modo === 'claro') return false;
        return media.matches;
    };

    const aplicar = () => {
        const modo = modoGuardado();
        const dark = esOscuro(modo);

        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.classList.toggle('theme-dark', dark);
        document.documentElement.classList.toggle('theme-light', !dark);
    };

    aplicar();

    media.addEventListener('change', aplicar);

    window.addEventListener('storage', (event) => {
        if (event.key === null || event.key.startsWith('farmacia-theme')) {
            aplicar();
        }
    });
})();
