<div class="mt-8" x-data="traspasoLotes()">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-lg font-bold">Lotes a traspasar</h2>
            <p class="mt-1 text-sm theme-subtle">Stock disponible en {{ $nombreOrigenLotes }}. <span x-text="totalUnidades()"></span></p>
        </div>
        <button type="button" @click="agregar()" class="theme-button theme-button-secondary">+ Agregar lote</button>
    </div>

    @error('lotes')
        <span class="mt-2 block text-sm text-red-500">{{ $message }}</span>
    @enderror

    @if(($lotesDisponibles ?? collect())->isEmpty())
        <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-slate-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-slate-200">
            No hay lotes disponibles para traspasar: todos están sin existencias o caducados.
        </p>
    @endif

    <div class="mt-4 flex flex-col gap-3">
        <template x-for="(row, index) in rows" :key="row.key">
            <div class="grid gap-3 md:grid-cols-[1fr_160px_44px] md:items-start">
                <div>
                    <label class="mb-2 block text-sm font-medium">Lote</label>
                    <select :name="`lotes[${index}][lote]`" x-model="row.lote" class="theme-input" required>
                        <option value="">Selecciona un lote</option>
                        @if($mostrarLotes)
                            @foreach(($lotesDisponibles ?? collect()) as $lote)
                                <option value="{{ $lote->id }}">{{ $lote->folio }} · {{ $lote->producto?->nombre_producto ?? 'Sin producto' }} · {{ $lote->producto?->presentacionParaLote()?->presentacion?->presentacion ?? 'Unidad' }} · {{ (int) ($lote->inventarios->firstWhere('id_sucursal', $origenLotesId)?->stock ?? 0) }} uds. · Caduca {{ $lote->fecha_de_caducidad ? \Carbon\Carbon::parse($lote->fecha_de_caducidad)->format('Y-m-d') : 'Sin fecha' }}</option>
                            @endforeach
                        @else
                            <option value="" disabled>Selecciona primero la sucursal {{ $fijaEsDestino ? 'origen' : 'destino' }}</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">Cantidad</label>
                    <input type="number" min="1" :max="stockDe(row.lote) || null" :name="`lotes[${index}][cantidad]`" x-model.number="row.cantidad" class="theme-input" required>
                    <p class="mt-1 text-xs theme-subtle" x-text="row.lote !== '' ? `Disponibles: ${stockDe(row.lote)} uds.` : ''"></p>
                </div>
                <div class="md:pt-9">
                    <button type="button" @click="eliminar(index)" x-show="rows.length > 1" class="theme-button theme-button-secondary w-11 px-0" aria-label="Quitar lote">×</button>
                </div>
            </div>
        </template>
    </div>
</div>
