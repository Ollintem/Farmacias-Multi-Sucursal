<x-layouts::app :title="__('Reportes')">
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
            <header class="flex flex-wrap items-center justify-between gap-4" aria-label="Encabezado de reportes">
            </header>

            @if($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    <ul class="list-disc ps-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('pages.reportes.components.filtro-reporte', [
                'filtros' => $filtros,
                'sucursales' => $sucursales,
                'puedeConsultarVarias' => $puedeConsultarVarias,
            ])

            @include('pages.reportes.components.tabla-reporte', ['reporte' => $reporte, 'interactivo' => true])

            <livewire:reportes.detalles-modal />
        </div>
    </div>
</x-layouts::app>
