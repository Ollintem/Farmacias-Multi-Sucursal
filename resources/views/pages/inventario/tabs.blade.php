<nav class="module-tabs" aria-label="Secciones de inventario">
    @if(auth()->user()?->puedeVerModulo('Inventario'))
        <a href="{{ route('inventario.productos', ['sucursal' => $selectedSucursal?->id]) }}" @class(['module-tab', 'module-tab-active' => $seccion === 'productos'])>
            Productos
        </a>
        <a href="{{ route('inventario.stock', ['sucursal' => $selectedSucursal?->id]) }}" @class(['module-tab', 'module-tab-active' => $seccion === 'stock'])>
            Stock
        </a>
    @endif

    @if(auth()->user()?->puedeVerModulo('Lotes y caducidades'))
        <a href="{{ route('lotes.index', ['sucursal' => $selectedSucursal?->id]) }}" @class(['module-tab', 'module-tab-active' => $seccion === 'lotes'])>
            Lotes y caducidades
        </a>
    @endif
</nav>
