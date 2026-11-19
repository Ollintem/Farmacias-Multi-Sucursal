<x-layouts::app :title="__('Reportes')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
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
