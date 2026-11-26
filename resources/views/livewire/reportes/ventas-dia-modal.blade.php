@php($columnas = $consolidado ? 8 : 7)

<div wire:keydown.escape.window="cerrar">
    @if($abierto)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="ventas-dia-titulo"
        >
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="cerrar"></div>

            <div class="relative flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <header class="flex items-start justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                    <div class="min-w-0">
                        <h3 id="ventas-dia-titulo" class="text-sm font-black tracking-tight text-slate-800 dark:text-white">
                            Ventas del día
                        </h3>
                        <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ $etiqueta }}</span>
                            · {{ $sucursalNombre ?? 'Todas las sucursales' }}
                            · {{ count($ventas) }} venta(s)
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="cerrar"
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                        aria-label="Cerrar ventas del día"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </header>

                <div class="flex-1 overflow-auto">
                    <div class="entradas-tabla-wrap !rounded-none !border-0">
                        <table class="entradas-tabla !min-w-full">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Hora</th>
                                    @if($consolidado)
                                        <th>Sucursal</th>
                                    @endif
                                    <th>Caja / turno</th>
                                    <th>Pago</th>
                                    <th class="entradas-num">Artículos</th>
                                    <th class="entradas-num">Descuento</th>
                                    <th class="entradas-num">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventas as $venta)
                                    <tr wire:key="venta-dia-{{ $venta['id'] }}">
                                        <td class="font-semibold text-slate-800 dark:text-slate-100">{{ $venta['folio'] }}</td>
                                        <td>{{ $venta['hora'] }}</td>
                                        @if($consolidado)
                                            <td>{{ $venta['sucursal'] }}</td>
                                        @endif
                                        <td>
                                            <span class="block font-semibold text-slate-700 dark:text-slate-200">{{ $venta['caja'] }}</span>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $venta['turno'] }}</span>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                {{ $venta['metodo'] }}
                                            </span>
                                        </td>
                                        <td class="entradas-num">{{ number_format((int) $venta['unidades']) }}</td>
                                        <td class="entradas-num">${{ number_format((float) $venta['descuento'], 2) }}</td>
                                        <td class="entradas-num font-bold text-slate-900 dark:text-white">
                                            ${{ number_format((float) $venta['total'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $columnas }}" class="px-4 py-10 text-center text-sm font-medium text-slate-500 dark:text-slate-400">
                                            Este día no tiene ventas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($ventas) > 0)
                                <tfoot>
                                    <tr class="bg-slate-50 dark:bg-slate-800/70">
                                        <td colspan="{{ $consolidado ? 5 : 4 }}" class="px-4 py-3 text-right text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                            Totales del día
                                        </td>
                                        <td class="entradas-num px-4 py-3 font-bold text-slate-900 dark:text-white">
                                            {{ number_format((int) $totales['unidades']) }}
                                        </td>
                                        <td class="entradas-num px-4 py-3 font-bold text-slate-900 dark:text-white">
                                            ${{ number_format((float) $totales['descuento'], 2) }}
                                        </td>
                                        <td class="entradas-num px-4 py-3 text-sm font-black text-emerald-700 dark:text-emerald-300">
                                            ${{ number_format((float) $totales['total'], 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                <footer class="flex justify-end border-t border-slate-100 bg-slate-50 px-5 py-3 dark:border-slate-800 dark:bg-slate-900">
                    <button type="button" wire:click="cerrar" class="theme-button theme-button-secondary !py-2 !text-sm">
                        Cerrar
                    </button>
                </footer>
            </div>
        </div>
    @endif
</div>
