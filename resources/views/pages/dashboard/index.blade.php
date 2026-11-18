<x-layouts::app :title="__('Dashboard')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            @include('pages.dashboard.components.acciones-panel')

            @include('pages.dashboard.components.grafica')

            @include('pages.dashboard.components.target')

            <div class="grid gap-4 lg:grid-cols-[1.8fr_1fr]">
                <div class="space-y-4">
                    @include('pages.dashboard.components.acceso-rapido')
                </div>

                <div class="space-y-4">
                    @include('pages.dashboard.components.actividad-reciente')
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
