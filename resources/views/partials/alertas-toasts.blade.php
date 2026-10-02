{{-- Avisos flotantes de alertas: visibles en cualquier ventana, abajo a la derecha.
     Se ocultan solos al cabo de unos segundos y traen tache para cerrarlos antes. --}}
@php
    $resumenAlertas = ['pendientes' => 0, 'rojos' => 0, 'total' => 0];
    $mostrarResumenAlertas = false;
    $sucursalToastId = (int) (session('active_sucursal_id') ?? auth()->user()?->id_sucursal ?? 0);
    $puedeVerAlertas = auth()->check() && (auth()->user()?->puedeVerModulo('Alertas') ?? false);

    if ($puedeVerAlertas && $sucursalToastId > 0 && ! session('alertas_toast_visto')) {
        $resumenAlertas = \App\Support\AlertasResumen::counts($sucursalToastId);
        $mostrarResumenAlertas = $resumenAlertas['total'] > 0;

        if ($mostrarResumenAlertas) {
            session()->put('alertas_toast_visto', true);
        }
    }

    $hayFlash = (session('success') || session('error')) && ! request()->routeIs('alertas.*');
@endphp

@if($hayFlash || $mostrarResumenAlertas)
    <div class="fixed right-4 bottom-4 z-[90] flex w-80 max-w-[calc(100vw-2rem)] flex-col gap-2" role="status" aria-live="polite">
        @if(session('success') && ! request()->routeIs('alertas.*'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 8000)" x-show="show" x-transition class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-white px-4 py-3 shadow-xl dark:border-emerald-500/40 dark:bg-[#22353f]">
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                <p class="min-w-0 flex-1 text-sm font-medium">{{ session('success') }}</p>
                <button type="button" @click="show = false" class="shrink-0 font-bold text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-200" aria-label="Cerrar aviso">✕</button>
            </div>
        @endif

        @if(session('error') && ! request()->routeIs('alertas.*'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 12000)" x-show="show" x-transition class="flex items-start gap-3 rounded-2xl border border-red-200 bg-white px-4 py-3 shadow-xl dark:border-red-500/40 dark:bg-[#22353f]">
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-red-600 text-xs font-bold text-white">!</span>
                <p class="min-w-0 flex-1 text-sm font-medium">{{ session('error') }}</p>
                <button type="button" @click="show = false" class="shrink-0 font-bold text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-200" aria-label="Cerrar aviso">✕</button>
            </div>
        @endif

        @if($mostrarResumenAlertas && $resumenAlertas['pendientes'] > 0)
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 10000)" x-show="show" x-transition class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-white px-4 py-3 shadow-xl dark:border-amber-500/40 dark:bg-[#22353f]">
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-amber-500 text-xs font-bold text-white">⇄</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold">Tienes {{ $resumenAlertas['pendientes'] }} {{ Str::plural('solicitud', $resumenAlertas['pendientes']) }} de traspaso pendientes</p>
                    <a href="{{ route('alertas.index', ['sucursal' => $sucursalToastId, 'seccion' => 'traspasos']) }}" class="text-sm font-bold text-emerald-700 hover:underline dark:text-emerald-300">Ver bandeja →</a>
                </div>
                <button type="button" @click="show = false" class="shrink-0 font-bold text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-200" aria-label="Cerrar aviso">✕</button>
            </div>
        @endif

        @if($mostrarResumenAlertas && $resumenAlertas['rojos'] > 0)
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 10000)" x-show="show" x-transition class="flex items-start gap-3 rounded-2xl border border-red-200 bg-white px-4 py-3 shadow-xl dark:border-red-500/40 dark:bg-[#22353f]">
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-red-500 text-xs font-bold text-white">●</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold">{{ $resumenAlertas['rojos'] }} {{ Str::plural('producto', $resumenAlertas['rojos']) }} por caducar (≤ 30 días)</p>
                    <a href="{{ route('alertas.index', ['sucursal' => $sucursalToastId, 'seccion' => 'caducidades', 'nivel' => 'rojo']) }}" class="text-sm font-bold text-emerald-700 hover:underline dark:text-emerald-300">Ver semáforo →</a>
                </div>
                <button type="button" @click="show = false" class="shrink-0 font-bold text-slate-400 transition hover:text-slate-700 dark:hover:text-slate-200" aria-label="Cerrar aviso">✕</button>
            </div>
        @endif
    </div>
@endif
