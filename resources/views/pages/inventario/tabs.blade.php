<nav class="inventario-segment" aria-label="Secciones de inventario">
    @if(auth()->user()?->puedeVerModulo('Inventario'))
        <a href="{{ route('inventario.productos', ['sucursal' => $selectedSucursal?->id]) }}" @class(['inventario-seg-btn', 'inventario-seg-btn-activo' => $seccion === 'productos']) @if($seccion === 'productos') aria-current="page" @endif>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14l-8 4m0 10V11m0 10l-8-4V7m8 4L4 7"/></svg>
            <span>Productos</span>
        </a>
        <a href="{{ route('inventario.stock', ['sucursal' => $selectedSucursal?->id]) }}" @class(['inventario-seg-btn', 'inventario-seg-btn-activo' => $seccion === 'stock']) @if($seccion === 'stock') aria-current="page" @endif>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Stock</span>
        </a>
    @endif

    @if(auth()->user()?->puedeVerModulo('Lotes y caducidades'))
        <a href="{{ route('lotes.index', ['sucursal' => $selectedSucursal?->id]) }}" @class(['inventario-seg-btn', 'inventario-seg-btn-activo' => $seccion === 'lotes']) @if($seccion === 'lotes') aria-current="page" @endif>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Lotes y caducidades</span>
        </a>
    @endif
</nav>
