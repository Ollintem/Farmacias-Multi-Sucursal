<x-layouts::app :title="__('Nueva compra')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            @include('pages.entradas.components.encabezado-formulario', [
                'ariaLabel' => 'Encabezado de nueva compra',
                'etiqueta' => 'Almacén · Nuevo registro',
                'titulo' => 'Registrar nueva compra',
                'volverUrl' => route('entradas-de-almacen.index', ['sucursal' => old('id_sucursal', $selectedSucursalId), 'tipo' => 'compras']),
            ])

            <section class="module-card noti-listado" aria-label="Datos de la compra">
                @include('pages.entradas.components.cabecera-listado', [
                    'titulo' => 'Datos de la compra',
                    'subtitulo' => 'Mercancía adquirida a un proveedor para una sucursal',
                ])

                <div class="p-5 sm:p-6">
                    @include('pages.entradas.components.formulario-compra', [
                        'proveedores' => $proveedores,
                        'sucursales' => $sucursales,
                        'selectedSucursalId' => $selectedSucursalId,
                        'productos' => $productos,
                    ])
                </div>
            </section>
        </div>
    </div>

    @include('pages.entradas.components.script-compra', [
        'presentacionPorProducto' => $presentacionPorProducto,
    ])
</x-layouts::app>
