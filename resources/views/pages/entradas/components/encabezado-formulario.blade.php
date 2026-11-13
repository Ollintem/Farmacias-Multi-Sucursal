@php
    $etiqueta = $etiqueta ?? null;
    $titulo = $titulo ?? null;
    $volverTexto = $volverTexto ?? '← Volver';
@endphp

<header class="mb-6" aria-label="{{ $ariaLabel ?? 'Acciones del formulario' }}">
    <div>
        @if($etiqueta)
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ $etiqueta }}</p>
        @endif

        @if($titulo)
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $titulo }}</h1>
        @endif

        <div class="{{ $titulo ? 'mt-4' : '' }} flex flex-wrap gap-2">
            <a href="{{ $volverUrl }}" class="theme-button theme-button-secondary">{{ $volverTexto }}</a>
        </div>
    </div>
</header>
