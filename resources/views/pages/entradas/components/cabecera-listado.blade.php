@php
    $esFormal = $formal ?? false;
@endphp

<header @class([
    'noti-listado-cabecera',
    'noti-listado-cabecera--formal' => $esFormal,
    'noti-listado-cabecera-empresa' => ! $esFormal,
])>
    <div>
        <h2>{{ $titulo }}</h2>
        <p class="theme-subtle text-sm">{{ $subtitulo }}</p>
    </div>
</header>
