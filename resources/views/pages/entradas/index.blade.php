<x-layouts::app :title="__('Entradas de almacén')">
    <div class="module-page pos-page noti-page relative isolate !overflow-hidden scrollbar-hide">
        {{-- Fondo agua POS — idéntico a Punto de venta (livewire/punto-de-venta) --}}
        <div class="pos-water" aria-hidden="true">
            <div class="pos-water-gradient"></div>
            <div class="pos-water-highlight"></div>
            <div class="pos-water-layer pos-water-layer--back">
                <div class="pos-wave-track pos-wave-track--slow">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,60 C150,85 350,35 600,60 C850,85 1050,35 1200,60 L1200,120 L0,120 Z" fill="rgba(6,182,212,0.20)"/></svg>
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,60 C150,85 350,35 600,60 C850,85 1050,35 1200,60 L1200,120 L0,120 Z" fill="rgba(6,182,212,0.20)"/></svg>
                </div>
            </div>
            <div class="pos-water-layer pos-water-layer--mid">
                <div class="pos-wave-track pos-wave-track--mid">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,55 C180,90 380,30 600,58 C820,86 1000,28 1200,55 L1200,120 L0,120 Z" fill="rgba(14,147,132,0.18)"/></svg>
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,55 C180,90 380,30 600,58 C820,86 1000,28 1200,55 L1200,120 L0,120 Z" fill="rgba(14,147,132,0.18)"/></svg>
                </div>
            </div>
            <div class="pos-water-layer">
                <div class="pos-wave-track pos-wave-track--fast">
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,65 C160,95 340,25 600,65 C860,105 1020,20 1200,65 L1200,120 L0,120 Z" fill="rgba(34,211,238,0.24)"/></svg>
                    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true"><path d="M0,65 C160,95 340,25 600,65 C860,105 1020,20 1200,65 L1200,120 L0,120 Z" fill="rgba(34,211,238,0.24)"/></svg>
                </div>
            </div>
        </div>
        <div class="module-page-inner noti-full noti-formal relative z-10">
            @php($tipoActual = $tipo ?? request('tipo', 'todas'))
            @php($totalTodas = $totalEntradas ?? ($entradas->count() ?? 0))

            @include('pages.entradas.components.alertas')

            <section class="module-card noti-listado noti-listado--formal pos-glass-highlight !bg-white/70 backdrop-blur-xl supports-[backdrop-filter]:!bg-white/60 !border-white/50 shadow-xl shadow-teal-900/5 dark:!bg-slate-800/60 dark:!border-white/10 flex flex-col flex-1 overflow-hidden min-h-[520px] lg:min-h-[640px]" aria-label="Listado de entradas" x-data="{ traspasoModal: null }">
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
