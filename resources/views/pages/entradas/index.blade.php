<x-layouts::app :title="__('Entradas de almacén')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            @php($tipoActual = $tipo ?? request('tipo', 'todas'))
            @php($totalTodas = $totalEntradas ?? ($entradas->count() ?? 0))

            @include('pages.entradas.components.alertas')

            <section class="module-card noti-listado noti-listado--formal" aria-label="Listado de entradas" x-data="{ traspasoModal: null }">
                @include('pages.entradas.components.cabecera-listado', [
                    'titulo' => 'Listado de entradas',
                    'subtitulo' => ($selectedSucursal?->nombre_sucursal ?? 'Sin sucursal') . ' · ' . $totalTodas . ' registro(s)',
                    'formal' => true,
                ])

                <div class="p-5 sm:p-6">
                    @if($tipoActual === 'traspasos')
                        @include('pages.entradas.components.traspasos-tarjetas', [
                            'solicitudes' => $solicitudes ?? collect(),
                            'solicitados' => $solicitados ?? collect(),
                            'selectedSucursal' => $selectedSucursal,
                        ])

                        @include('pages.entradas.components.traspasos-modal-solicitudes', [
                            'solicitudes' => $solicitudes ?? collect(),
                            'selectedSucursal' => $selectedSucursal,
                        ])

                        @include('pages.entradas.components.traspasos-modal-solicitados', [
                            'solicitados' => $solicitados ?? collect(),
                            'selectedSucursal' => $selectedSucursal,
                        ])
                    @else
                        @include('pages.entradas.components.entradas-tabla', [
                            'tipoActual' => $tipoActual,
                            'entradas' => $entradas ?? collect(),
                            'selectedSucursal' => $selectedSucursal,
                        ])
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
