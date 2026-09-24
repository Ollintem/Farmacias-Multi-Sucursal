<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
<script>
    // Aplica el tema antes del primer pintado para evitar parpadeos.
    (() => {
        try {
            const guardado = localStorage.getItem('farmacia-theme-modo') || 'claro';
            const sistemaOscuro = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const oscuro = guardado === 'oscuro' || (guardado === 'sistema' && sistemaOscuro);
            document.documentElement.classList.toggle('dark', oscuro);
            document.documentElement.classList.toggle('theme-dark', oscuro);
            document.documentElement.classList.toggle('theme-light', !oscuro);
            document.documentElement.style.colorScheme = oscuro ? 'dark' : 'light';
        } catch (e) {}
    })();
</script>
