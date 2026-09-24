@props(['compacto' => false])

{{-- Interruptor de modo claro/oscuro. Solo se usa en el panel lateral.
     Usa window.FarmaTheme de app.js y persiste en todas las vistas. --}}
<button
    type="button"
    data-farma-theme-toggle
    class="farma-theme-toggle {{ $compacto ? 'px-2 py-1 text-xs' : '' }}"
    title="Cambiar entre modo claro y oscuro"
    aria-label="Cambiar entre modo claro y oscuro"
>
    <span class="farma-theme-dot" aria-hidden="true">
        <svg data-farma-theme-icon-sun class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.41-1.41M7.05 7.05L5.64 5.64m12.72 0l-1.41 1.41M7.05 16.95l-1.41 1.41M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <svg data-farma-theme-icon-moon class="hidden h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
        </svg>
    </span>
    @unless($compacto)
        <span data-farma-theme-label>Modo claro</span>
    @endunless
</button>
