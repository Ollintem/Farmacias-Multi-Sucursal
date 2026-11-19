<div wire:keydown.escape.window="cerrar">
    @if($abierto && $detalle)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="detalles-venta-titulo"
        >
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="cerrar"></div>

            <div class="relative flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <header class="flex items-start justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                    <div class="min-w-0">
                        <h3 id="detalles-venta-titulo" class="text-sm font-black tracking-tight text-slate-800 dark:text-white">
                            Productos vendidos
                        </h3>
                        <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ $detalle['venta']['folio'] }}</span>
                            @if($detalle['venta']['fecha'])
                                · {{ $detalle['venta']['fecha'] }}
                            @endif
                            · {{ count($detalle['productos']) }} producto(s)
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="cerrar"
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                        aria-label="Cerrar detalle de productos"
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
                                    <th>Producto</th>
                                    <th>Presentación</th>
                                    <th class="entradas-num">Cantidad</th>
                                    <th class="entradas-num">Precio unitario</th>
                                    <th class="entradas-num">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($detalle['productos'] as $indice => $producto)
                                    <tr wire:key="detalle-venta-{{ $detalle['venta']['id'] }}-{{ $indice }}">
                                        <td class="font-semibold text-slate-800 dark:text-slate-100">{{ $producto['producto'] }}</td>
                                        <td>{{ $producto['presentacion'] }}</td>
                                        <td class="entradas-num">{{ number_format((int) $producto['cantidad']) }}</td>
                                        <td class="entradas-num">${{ number_format((float) $producto['precio_unitario'], 2) }}</td>
                                        <td class="entradas-num font-bold text-slate-900 dark:text-white">
                                            ${{ number_format((float) $producto['total'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-10 text-center text-sm font-medium text-slate-500 dark:text-slate-400">
                                            Esta venta no tiene productos registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50 dark:bg-slate-800/70">
                                    <td colspan="4" class="px-4 py-3 text-right text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                        Subtotal
                                    </td>
                                    <td class="entradas-num px-4 py-3 text-sm font-black text-emerald-700 dark:text-emerald-300">
                                        ${{ number_format((float) $detalle['subtotal'], 2) }}
                                    </td>
                                </tr>
                            </tfoot>
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
