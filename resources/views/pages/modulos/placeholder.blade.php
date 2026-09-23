@php
    $nombre = $tituloModulo ?? 'Módulo';

    $detalleModulos = [
        'Caja' => [
            'siglas' => 'Cj',
            'descripcion' => 'Aquí se operará la apertura y cierre de caja, los cortes de turno y el registro de movimientos de efectivo por sucursal.',
        ],
        'Reportes' => [
            'siglas' => 'Rp',
            'descripcion' => 'Aquí se generarán los reportes de ventas, inventario, caducidades y desempeño por sucursal para la toma de decisiones.',
        ],
        'Alertas' => [
            'siglas' => 'Al',
            'descripcion' => 'Aquí se mostrarán las alertas de stock crítico, productos próximos a caducar y eventos que requieren atención inmediata.',
        ],
        'Entradas de almacén' => [
            'siglas' => 'En',
            'descripcion' => 'Aquí se registrarán las entradas de mercancía al almacén: recepción de proveedores, cantidades, lotes y documentos de respaldo.',
        ],
        'Traspasos' => [
            'siglas' => 'Tr',
            'descripcion' => 'Aquí se gestionarán los traspasos de productos entre sucursales, con folios de salida, recepción y control de existencias.',
        ],
    ];

    $detalle = $detalleModulos[$nombre] ?? [
        'siglas' => 'Rx',
        'descripcion' => 'Este espacio ya está preparado dentro de la operación de la farmacia. Su flujo completo se habilitará en una siguiente etapa.',
    ];
@endphp

<x-layouts::app :title="__($nombre)">
    <div class="module-page">
        <div class="module-page-inner">
            <div>
                <button type="button" onclick="history.back()" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition hover:text-emerald-900 dark:text-emerald-300 dark:hover:text-emerald-200">← Volver</button>
            </div>

            @if(session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <section class="module-hero">
                <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-emerald-600 text-sm font-black text-white">{{ $detalle['siglas'] }}</span>
                            <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-700 dark:text-emerald-300">FarmaERP · Próximamente</p>
                        </div>
                        <h1 class="mt-5 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $nombre }}</h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $detalle['descripcion'] }}</p>
                    </div>
                    <span class="w-fit rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">En preparación</span>
                </div>
                <div class="absolute -right-10 -top-16 h-48 w-48 rounded-full border-[22px] border-white/35 dark:border-emerald-200/10"></div>
            </section>

            <section class="module-card overflow-hidden">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                    <h2 class="text-base font-bold text-slate-950 dark:text-white">Área de trabajo</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">La navegación ya está disponible para mantener una experiencia consistente.</p>
                </div>
                <div class="grid gap-4 px-6 py-10 text-center sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                        <span class="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-sky-100 text-sm font-black text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">01</span>
                        <p class="mt-3 text-sm font-bold text-slate-800 dark:text-white">Diseño preparado</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Listo para el flujo del módulo.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                        <span class="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">02</span>
                        <p class="mt-3 text-sm font-bold text-slate-800 dark:text-white">Menú conectado</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">La ruta ya forma parte del panel.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
                        <span class="mx-auto grid h-10 w-10 place-items-center rounded-xl bg-amber-100 text-sm font-black text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">03</span>
                        <p class="mt-3 text-sm font-bold text-slate-800 dark:text-white">Lógica pendiente</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Se conectará a la base de datos.</p>
                    </div>
                </div>
                <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-5 sm:flex-row sm:items-center dark:border-slate-700">
                    <button type="button" onclick="history.back()" class="theme-button theme-button-secondary">Volver atrás</button>
                    <a href="{{ route('dashboard') }}" class="theme-button theme-button-primary">Ir al dashboard <span class="ml-2">→</span></a>
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
