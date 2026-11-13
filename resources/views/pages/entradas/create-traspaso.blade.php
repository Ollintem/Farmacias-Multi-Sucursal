<x-layouts::app :title="__('Nuevo traspaso')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            @include('pages.entradas.components.encabezado-formulario', [
                'ariaLabel' => 'Acciones de nuevo traspaso',
                'volverUrl' => route('alertas.index', ['sucursal' => $sucursalLink, 'filtro' => 'traspasos']),
            ])

            @include('pages.entradas.components.alertas')

            <section class="module-card noti-listado" aria-label="Datos del traspaso">
                @include('pages.entradas.components.cabecera-listado', [
                    'titulo' => 'Datos del traspaso',
                    'subtitulo' => 'Solicita producto a otra sucursal: llegará como alerta para aceptar o rechazar',
                ])

                <div class="p-5 sm:p-6">
                    @include('pages.entradas.components.formulario-traspaso', [
                        'fijaEsDestino' => $fijaEsDestino,
                        'campoPicker' => $campoPicker,
                        'campoFijo' => $campoFijo,
                        'valorPicker' => $valorPicker,
                        'paramQuery' => $paramQuery,
                        'opcionesSucursal' => $opcionesSucursal,
                        'fija' => $fija,
                        'nombreOrigenLotes' => $nombreOrigenLotes,
                        'mostrarLotes' => $mostrarLotes,
                        'lotesDisponibles' => $lotesDisponibles,
                        'origenLotesId' => $origenLotesId,
                        'sucursalLink' => $sucursalLink,
                    ])
                </div>
            </section>
        </div>
    </div>

    @include('pages.entradas.components.script-traspaso', [
        'lotesDisponibles' => $lotesDisponibles,
        'origenLotesId' => $origenLotesId,
    ])
</x-layouts::app>
