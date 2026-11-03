<x-layouts::app :title="__('Caja')">
    <div class="module-page noti-page">
        <div class="module-page-inner noti-full noti-formal">
            {{-- Encabezado --}}
            <header class="mb-4 flex flex-wrap items-end justify-between gap-4" aria-label="Encabezado de caja">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Caja · {{ $sucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Control de caja</h1>
                    <p class="theme-subtle mt-1 text-sm">Apertura y cierre por turno, arqueo multi-caja y movimientos de efectivo.</p>
                </div>
                <form method="GET" action="{{ route('caja.index') }}" class="flex items-center gap-2">
                    <label class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Sucursal</label>
                    <select name="sucursal" onchange="this.form.submit()" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-[#0e9384] focus:outline-none focus:ring-2 focus:ring-[#0e9384]/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}" @selected($sucursal?->id === $s->id)>{{ $s->nombre_sucursal }}</option>
                        @endforeach
                    </select>
                </form>
            </header>

            {{-- KPIs --}}
            @php
                $abiertosCount = $cortesAbiertos->count();
                $totalEsperadoAbiertos = $cortesAbiertos->sum(fn($c) => (float) ($c->efectivo_esperado_calculado ?? $c->efectivo_inicial));
            @endphp
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:hidden">
                <dl class="grid grid-cols-2 gap-px bg-slate-200 dark:bg-zinc-800 lg:grid-cols-4">
                    <div class="flex items-center gap-2.5 bg-white px-3.5 py-3 dark:bg-zinc-900">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Abiertas</p>
                            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $abiertosCount }} caja(s)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 bg-white px-3.5 py-3 dark:bg-zinc-900">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-600 ring-1 ring-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Esperado (abiertas)</p>
                            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">${{ number_format($totalEsperadoAbiertos, 2) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 bg-white px-3.5 py-3 dark:bg-zinc-900">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-sky-100 text-sky-700 ring-1 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-500/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h10V3H3v10zm0 8h10v-6H3v6zm12 0h10V11H15v10zm0-18v6h10V3H15z"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Ventas hoy</p>
                            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">${{ number_format((float) ($resumenHoy['totalHoy'] ?? 0), 2) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 bg-white px-3.5 py-3 dark:bg-zinc-900">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-100 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 dark:text-zinc-400 leading-none">Historial</p>
                            <p class="mt-1 text-lg font-bold leading-none tracking-tight text-slate-900 dark:text-white">{{ $historialCount }} cierre(s)</p>
                        </div>
                    </div>
                </dl>
            </div>

            @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                    <ul class="list-disc ps-4">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if(!$sucursal)
                <div class="module-card p-10 text-center">
                    <p class="text-sm font-semibold text-slate-600 dark:text-zinc-300">No hay sucursal seleccionada.</p>
                    <p class="theme-subtle mt-1 text-sm">Selecciona una sucursal activa para operar caja.</p>
                </div>
            @else
            <div class="grid gap-6">
                {{-- Cajas abiertas (principal) --}}
                <div class="space-y-6">
                    {{-- Cajas abiertas (multi-caja) --}}
                    <section class="module-card !p-0 overflow-hidden" aria-label="Cajas abiertas">
                        <header class="flex items-center justify-between border-b border-slate-200 bg-slate-50/60 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                            <div>
                                <h2 class="text-sm font-black tracking-tight text-slate-800 dark:text-white">Cajas abiertas</h2>
                                <p class="text-xs font-medium text-slate-500 dark:text-zinc-400">Multi-caja por sucursal · {{ $sucursal->nombre_sucursal }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold {{ $abiertosCount > 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200' : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $abiertosCount > 0 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                {{ $abiertosCount > 0 ? $abiertosCount.' abierta(s)' : 'Sin caja abierta' }}
                            </span>
                        </header>

                        @if($cortesAbiertos->isEmpty())
                            <div class="px-6 py-10 text-center">
                                <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">!</span>
                                <p class="mt-3 text-sm font-bold text-slate-800 dark:text-white">No hay caja abierta</p>
                                <p class="theme-subtle mt-1 text-sm">Abre una caja para habilitar el Punto de Venta en esta sucursal.</p>
                                <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                    Ventas bloqueadas sin caja abierta
                                </p>
                            </div>
                        @else
                            <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                                @foreach($cortesAbiertos as $corte)
                                    @php
                                        $esperado = (float) ($corte->efectivo_esperado_calculado ?? $corte->efectivo_inicial);
                                        $ingresos = (float) $corte->movimientos->where('tipo','ingreso')->sum('monto');
                                        $retiros = (float) $corte->movimientos->where('tipo','retiro')->sum('monto');
                                        $ventasCorte = \App\Models\Venta::where('id_corte_caja', $corte->id)->with('pago')->get();
                                        $ventasTotal = (float) $ventasCorte->sum('total');
                                    @endphp
                                    <div class="p-5">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="inline-flex items-center rounded-full bg-[#0e9384] px-3 py-1 text-xs font-black text-white">{{ $corte->caja }}</span>
                                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $corte->turno }}</span>
                                                    <span class="text-xs font-medium text-slate-500 dark:text-zinc-400">por {{ $corte->usuario?->nombre }} {{ $corte->usuario?->apellido }} · {{ $corte->fecha_inicio->format('d/m/Y H:i') }}</span>
                                                </div>
                                                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800">
                                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Fondo inicial</p>
                                                        <p class="mt-1 text-sm font-black text-slate-800 dark:text-white">${{ number_format((float) $corte->efectivo_inicial, 2) }}</p>
                                                    </div>
                                                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                                                        <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-300">Ventas del corte</p>
                                                        <p class="mt-1 text-sm font-black text-emerald-800 dark:text-emerald-200">${{ number_format($ventasTotal, 2) }} <span class="text-[11px] font-semibold">({{ $ventasCorte->count() }})</span></p>
                                                    </div>
                                                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-900">
                                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Movimientos</p>
                                                        <p class="mt-1 text-xs font-bold"><span class="text-emerald-600">+${{ number_format($ingresos,2) }}</span> <span class="text-slate-400">/</span> <span class="text-red-600">-${{ number_format($retiros,2) }}</span></p>
                                                    </div>
                                                    <div class="rounded-xl border border-[#0e9384]/30 bg-teal-50 px-3 py-2.5 dark:border-[#0e9384]/40 dark:bg-[#0e9384]/15">
                                                        <p class="text-[10px] font-bold uppercase tracking-widest text-[#0c7569] dark:text-emerald-300">
                                                            @if($esCorteCiego) Esperado <span class="rounded bg-amber-100 px-1 py-0.5 text-[9px] text-amber-700">ciego</span> @else Esperado @endif
                                                        </p>
                                                        @if($esCorteCiego)
                                                            <p class="mt-1 text-sm font-black text-slate-400">•••••</p>
                                                            <p class="text-[11px] text-slate-500">Oculto hasta el cierre</p>
                                                        @else
                                                            <p class="mt-1 text-sm font-black text-[#0c7569] dark:text-emerald-300">${{ number_format($esperado, 2) }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Movimientos recientes --}}
                                        @if($corte->movimientos->isNotEmpty())
                                            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 dark:border-zinc-700">
                                                <div class="bg-slate-50 px-3 py-2 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:bg-zinc-800 dark:text-zinc-400">Movimientos del corte</div>
                                                <ul class="divide-y divide-slate-100 dark:divide-zinc-800">
                                                    @foreach($corte->movimientos->sortByDesc('creado_en')->take(5) as $mov)
                                                        <li class="flex items-center justify-between px-3 py-2 text-sm">
                                                            <span class="flex items-center gap-2">
                                                                <span class="h-2 w-2 rounded-full {{ $mov->tipo === 'ingreso' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                                                <span class="font-semibold {{ $mov->tipo === 'ingreso' ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-300' }}">{{ ucfirst($mov->tipo) }} ${{ number_format((float) $mov->monto,2) }}</span>
                                                                @if($mov->motivo)<span class="text-slate-500 dark:text-zinc-400">— {{ $mov->motivo }}</span>@endif
                                                            </span>
                                                            <span class="text-xs text-slate-400">{{ $mov->creado_en->format('H:i d/m') }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        {{-- Acciones: movimiento + cerrar --}}
                                        <div class="mt-4 grid gap-3 lg:grid-cols-2">
                                            <form method="POST" action="{{ route('caja.movimiento', $corte) }}" class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-zinc-700 dark:bg-zinc-800">
                                                @csrf
                                                <p class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-zinc-300">Registrar movimiento</p>
                                                <div class="mt-2 grid grid-cols-2 gap-2">
                                                    <select name="tipo" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                                                        <option value="ingreso">Ingreso</option>
                                                        <option value="retiro">Retiro</option>
                                                    </select>
                                                    <input type="number" step="0.01" min="0.01" name="monto" placeholder="Monto" required class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                                                </div>
                                                <input type="text" name="motivo" maxlength="255" placeholder="Motivo (opcional)" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900 dark:text-white">
                                                <button type="submit" class="theme-button theme-button-secondary mt-2 w-full !py-2 text-sm">Guardar movimiento</button>
                                            </form>

                                            <form method="POST" action="{{ route('caja.cerrar', $corte) }}" class="rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/30 dark:bg-amber-500/10" onsubmit="return confirm('¿Cerrar {{ $corte->caja }} — {{ $corte->turno }}? Esta acción no se puede deshacer.')">
                                                @csrf
                                                <p class="text-xs font-bold uppercase tracking-widest text-amber-800 dark:text-amber-200">Cerrar caja</p>
                                                @if($esCorteCiego)
                                                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">Corte ciego: ingresa el efectivo contado sin ver el esperado.</p>
                                                @else
                                                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">Esperado actual: <span class="font-black">${{ number_format($esperado,2) }}</span></p>
                                                @endif
                                                <input type="number" step="0.01" min="0" name="efectivo_declarado" placeholder="Efectivo declarado" required class="mt-2 w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm dark:border-amber-500/40 dark:bg-zinc-900 dark:text-white">
                                                <button type="submit" class="theme-button theme-button-primary mt-2 w-full !bg-amber-600 hover:!bg-amber-700 !py-2 text-sm">Cerrar y arquear</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    {{-- Instrucciones de uso — debajo de esta caja --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900" aria-label="Instrucciones de uso de la caja">
                        <div class="flex items-center gap-2">
                            <span class="grid h-7 w-7 place-items-center rounded-full bg-[#0e9384]/10 text-[#0e9384] ring-1 ring-[#0e9384]/15">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <h3 class="text-sm font-black text-slate-800 dark:text-white">Cómo usar esta caja</h3>
                            <span class="ml-auto hidden items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-500 dark:bg-zinc-800 dark:text-zinc-400 sm:inline-flex">Caja 1 · Matutino</span>
                        </div>
                        <ol class="mt-4 grid gap-3 text-xs leading-relaxed text-slate-600 dark:text-zinc-300 sm:grid-cols-2">
                            <li class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white text-xs font-black text-[#0e9384] shadow-sm ring-1 ring-slate-200 dark:bg-zinc-900 dark:ring-zinc-700">1</span>
                                <div>
                                    <p class="font-bold text-slate-800 dark:text-white">Fondo inicial</p>
                                    <p>Es el efectivo con el que abriste este turno. Las ventas aún no lo modifican.</p>
                                </div>
                            </li>
                            <li class="flex gap-3 rounded-xl border border-emerald-100 bg-emerald-50/60 p-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white text-xs font-black text-emerald-700 shadow-sm ring-1 ring-emerald-200 dark:bg-zinc-900">2</span>
                                <div>
                                    <p class="font-bold text-emerald-800 dark:text-emerald-200">Ventas del corte</p>
                                    <p>Suma automática de todas las ventas ligadas a <span class="font-semibold">Caja 1 · Matutino</span> desde la apertura.</p>
                                </div>
                            </li>
                            <li class="flex gap-3 rounded-xl border border-slate-100 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 dark:bg-zinc-800">3</span>
                                <div>
                                    <p class="font-bold text-slate-800 dark:text-white">Movimientos</p>
                                    <p><span class="font-semibold text-emerald-600">+ Ingreso</span> / <span class="font-semibold text-red-600">− Retiro</span> de efectivo con motivo (ej. fondo, pago a proveedor).</p>
                                </div>
                            </li>
                            <li class="flex gap-3 rounded-xl border border-[#0e9384]/15 bg-[#f0fdfa] p-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white text-xs font-black text-[#0e9384] shadow-sm ring-1 ring-[#0e9384]/15 dark:bg-zinc-900">4</span>
                                <div>
                                    <p class="font-bold text-[#0c7569] dark:text-emerald-200">Esperado</p>
                                    <p><span class="font-mono font-bold">Esperado = Inicial + Ventas + Ingresos − Retiros</span>. En corte ciego se oculta hasta el cierre.</p>
                                </div>
                            </li>
                            <li class="flex gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-zinc-700 dark:bg-zinc-800 sm:col-span-2">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white text-xs font-black text-slate-700 shadow-sm ring-1 ring-slate-200 dark:bg-zinc-900">5</span>
                                <div class="flex-1">
                                    <p class="font-bold text-slate-800 dark:text-white">Registrar movimiento</p>
                                    <p>Elige <span class="font-semibold">Retiro/Ingreso</span> → escribe <span class="font-semibold">Monto</span> → agrega <span class="font-semibold">Motivo</span> → <span class="font-semibold">Guardar movimiento</span>. Queda auditado con usuario y hora.</p>
                                </div>
                            </li>
                            <li class="flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/20 dark:bg-amber-500/10 sm:col-span-2">
                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white text-xs font-black text-amber-700 shadow-sm ring-1 ring-amber-200 dark:bg-zinc-900">6</span>
                                <div class="flex-1">
                                    <p class="font-bold text-amber-800 dark:text-amber-200">Cerrar caja — Cerrar y arquear</p>
                                    <p>Cuenta tu efectivo físico, escríbelo en <span class="font-semibold">Efectivo declarado</span> y presiona <span class="font-semibold">Cerrar y arquear</span>. El sistema calcula <span class="font-semibold">Diferencia = Declarado − Esperado</span> y bloquea la caja (multi-caja permite seguir con otra).</p>
                                </div>
                            </li>
                        </ol>
                        <p class="mt-3 text-center text-[11px] font-medium text-slate-400 dark:text-zinc-500">Tip: Cada caja es independiente por sucursal y turno (Mañana/Tarde). Las ventas del POS se ligan automáticamente a tu caja abierta.</p>
                    </section>

                </div>

                </div>
            </div>
            @endif

            {{-- Modales invocados desde el topbar (ahorran espacio en la ventana principal) --}}
            <div x-data="{ abrirCajaOpen:false, resumenOpen:false }" @open-abrir-caja.window="abrirCajaOpen=true" @open-resumen-dia.window="resumenOpen=true">
                {{-- Modal Abrir caja --}}
                <div x-show="abrirCajaOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
                    <div @click="abrirCajaOpen=false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                    <div @click.away="abrirCajaOpen=false" class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-black text-slate-800 dark:text-white">Abrir nueva caja</h3>
                                <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Multi-caja: Caja 1 y Caja 2 con turno Mañana/Tarde.</p>
                            </div>
                            <button @click="abrirCajaOpen=false" class="rounded-full p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800">✕</button>
                        </div>
                        <form method="POST" action="{{ route('caja.abrir') }}" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="id_sucursal" value="{{ $sucursal?->id }}">
                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-zinc-300">Caja física</span>
                                <select name="caja" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <option value="Caja 1">Caja 1 — Mostrador principal</option>
                                    <option value="Caja 2">Caja 2 — Mostrador secundario</option>
                                    <option value="Caja 3">Caja 3</option>
                                    <option value="Caja móvil">Caja móvil</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-zinc-300">Turno</span>
                                <select name="turno" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <option value="Matutino">Mañana — Matutino (08:00–15:00)</option>
                                    <option value="Vespertino">Tarde — Vespertino (15:00–22:00)</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-zinc-300">Fondo inicial</span>
                                <div class="mt-1 flex items-center overflow-hidden rounded-xl border border-slate-200 bg-white focus-within:border-[#0e9384] focus-within:ring-2 focus-within:ring-[#0e9384]/15 dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="pl-3 text-sm font-black text-slate-400">$</span>
                                    <input type="number" step="0.01" min="0" name="efectivo_inicial" value="0.00" required class="flex-1 border-0 bg-transparent px-2 py-2.5 text-sm font-bold text-slate-800 focus:outline-none focus:ring-0 dark:text-white">
                                </div>
                            </label>
                            <button type="submit" class="theme-button theme-button-primary w-full">Abrir caja</button>
                        </form>
                    </div>
                </div>
                {{-- Modal Resumen del día --}}
                <div x-show="resumenOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
                    <div @click="resumenOpen=false" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
                    <div @click.away="resumenOpen=false" class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-black text-slate-800 dark:text-white">Resumen del día</h3>
                                <p class="text-xs text-slate-500 dark:text-zinc-400">{{ $sucursal?->nombre_sucursal }} · {{ now()->format('d/m/Y') }}</p>
                            </div>
                            <button @click="resumenOpen=false" class="rounded-full p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800">✕</button>
                        </div>
                        @if($resumenHoy)
                            <dl class="mt-4 space-y-2 text-sm">
                                <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2 dark:bg-zinc-800"><dt class="font-medium text-slate-600 dark:text-zinc-400">Ventas</dt><dd class="font-black tabular-nums text-slate-800 dark:text-white">{{ $resumenHoy['ventasHoy']->count() }}</dd></div>
                                <div class="flex justify-between rounded-lg bg-white px-3 py-2 ring-1 ring-slate-200 dark:bg-zinc-900 dark:ring-zinc-700"><dt class="font-bold text-slate-800 dark:text-white">Total</dt><dd class="font-black tabular-nums text-[#0c7569] dark:text-emerald-300">${{ number_format($resumenHoy['totalHoy'],2) }}</dd></div>
                                <div class="flex justify-between px-3 py-1 text-xs"><dt class="text-slate-500 dark:text-zinc-400">Efectivo</dt><dd class="font-bold tabular-nums">${{ number_format($resumenHoy['efectivoHoy'],2) }}</dd></div>
                                <div class="flex justify-between px-3 py-1 text-xs"><dt class="text-slate-500 dark:text-zinc-400">Tarjeta</dt><dd class="font-bold tabular-nums">${{ number_format($resumenHoy['tarjetaHoy'],2) }}</dd></div>
                                <div class="flex justify-between px-3 py-1 text-xs"><dt class="text-slate-500 dark:text-zinc-400">Transferencia</dt><dd class="font-bold tabular-nums">${{ number_format($resumenHoy['transferenciaHoy'],2) }}</dd></div>
                            </dl>
                            <a href="{{ route('punto-venta.index') }}" class="theme-button theme-button-secondary mt-4 w-full justify-center">Ir a Punto de Venta</a>
                        @else
                            <p class="mt-4 text-sm text-slate-500 dark:text-zinc-400">Sin movimientos hoy.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
