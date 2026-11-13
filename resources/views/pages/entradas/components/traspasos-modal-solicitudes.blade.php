<div x-show="traspasoModal === 'solicitudes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div x-show="traspasoModal === 'solicitudes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative flex max-h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-[#0b1322]" @click.stop>
        <header class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-5 py-4 dark:border-slate-700 dark:bg-slate-900/40">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg></span>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Solicitudes de traspasos</h3>
                    <p class="theme-subtle text-xs">{{ ($solicitudes ?? collect())->count() }} solicitud(es) · {{ $selectedSucursal?->nombre_sucursal ?? 'esta sucursal' }}</p>
                </div>
            </div>
            <button type="button" @click="traspasoModal = null" class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </header>
        <div class="flex-1 overflow-auto">
            <div class="entradas-tabla-wrap !rounded-none !border-0">
                <table class="entradas-tabla entradas-tabla--compact">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Movimiento</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitudes ?? [] as $solicitud)
                            <tr>
                                <td><span class="entradas-folio">{{ $solicitud['folio'] }}</span></td>
                                <td>
                                    <div class="entradas-ruta">
                                        <span class="entradas-ruta-origen">{{ $solicitud['origen'] }}</span>
                                        <span class="entradas-ruta-sep" aria-hidden="true">→</span>
                                        <span class="entradas-ruta-destino">{{ $solicitud['destino'] }}</span>
                                    </div>
                                    <span class="entradas-ruta-meta">Solicitado por {{ $solicitud['solicitado_por'] }}</span>
                                </td>
                                <td class="entradas-fecha">{{ $solicitud['fecha'] }}</td>
                                <td><span class="status-badge {{ $solicitud['estado_class'] }}">{{ $solicitud['estado'] }}</span></td>
                                <td class="text-right">
                                    @if(($solicitud['estado_raw'] ?? '') === 'enviado')
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('traspasos.aceptar', $solicitud['id']) }}">@csrf<button type="submit" class="theme-button theme-button-primary !min-h-0 !px-3 !py-1.5 !text-xs">Aceptar</button></form>
                                            <form method="POST" action="{{ route('traspasos.rechazar', $solicitud['id']) }}">@csrf<button type="submit" class="theme-button theme-button-secondary !min-h-0 !px-3 !py-1.5 !text-xs">Rechazar</button></form>
                                        </div>
                                    @elseif(($solicitud['estado_raw'] ?? '') === 'pendiente')
                                        <span class="entradas-mudo">En espera de envío</span>
                                    @else
                                        <span class="entradas-mudo">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="entradas-vacio"><p class="entradas-vacio-titulo">Sin solicitudes</p><p class="theme-subtle text-sm">No hay solicitudes para esta sucursal.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
