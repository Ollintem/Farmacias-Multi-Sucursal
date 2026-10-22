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
        'Proveedores' => [
            'siglas' => 'Pv',
            'descripcion' => 'Aquí se administrará el catálogo de proveedores: datos de contacto, condiciones de entrega y su vínculo con pedidos y lotes.',
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
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            <div>
                <button type="button" onclick="history.back()" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-700 transition hover:text-emerald-900 dark:text-emerald-300 dark:hover:text-emerald-200">← Volver</button>
            </div>

            @if(session('success'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
                    role="status"
                >
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => show = false, 2000)"
                    x-show="show"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"
                    role="alert"
                >
                    {{ session('error') }}
                </div>
            @endif

            <header class="mb-6" aria-label="Encabezado del módulo">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">FarmaERP · Próximamente</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $nombre }}</h1>
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
  <dl class="grid grid-cols-2 gap-px bg-slate-200 dark:bg-zinc-800 lg:grid-cols-3">
    <div class="relative flex items-center gap-2.5 bg-white px-3.5 py-3 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60">
    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600 ring-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6h.008v.008H6V6z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Diseño</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">Preparado</p>
        </div>
</div>
    <div class="relative flex items-center gap-2.5 bg-white px-3.5 py-3 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60">
    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600 ring-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Menú</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">Conectado</p>
        </div>
</div>
    <div class="relative flex items-center gap-2.5 bg-white px-3.5 py-3 transition hover:bg-slate-50 dark:bg-zinc-900 dark:hover:bg-zinc-800/60">
    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600 ring-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700 ring-1">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Lógica</p>
            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">Pendiente</p>
        </div>
</div>
  </dl>
</div>
                </div>
            </header>

            <section class="module-card noti-listado" aria-label="Área de trabajo">
                <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
                    <div>
                        <h2>Área de trabajo</h2>
                        <p class="theme-subtle text-sm">{{ $detalle['descripcion'] }}</p>
                    </div>
                </header>
                <div>
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
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>
