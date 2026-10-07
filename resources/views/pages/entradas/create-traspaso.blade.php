<x-layouts::app :title="__('Nuevo traspaso')">
    <div id="theme-shell">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Almacén</p>
                    <h1 class="mt-2 text-3xl font-bold">Registrar nuevo traspaso</h1>
                    <p class="mt-1 text-sm theme-subtle">Solicita producto a otra sucursal: llegará como alerta para aceptar o rechazar.</p>
                </div>
            </div>

            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('traspasos.store') }}" method="POST" class="theme-card">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Sucursal origen</label>
                        <input type="hidden" name="sucursal_a" value="{{ $origenSucursal?->id }}">
                        <p class="theme-input flex items-center font-semibold" aria-readonly="true">{{ $origenSucursal?->nombre_sucursal ?? 'Sin sucursal' }}</p>
                        <p class="mt-1 text-xs theme-subtle">El origen es tu sucursal activa y el traspaso nace en estado Pendiente.</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Sucursal destino</label>
                        <x-option-pick name="sucursal_b" label="Sucursal destino" placeholder="Selecciona el destino" :options="$sucursalesDestino->pluck('nombre_sucursal', 'id')" :value="old('sucursal_b', $selectedSucursalId ?? '')" />
                        @error('sucursal_b')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">Mensaje para la sucursal destino</label>
                        <textarea name="mensaje" rows="3" maxlength="1000" placeholder="Ej. Urgente: necesitamos 20 uds. para cubrir la venta del fin de semana." class="theme-input">{{ old('mensaje') }}</textarea>
                        @error('mensaje')
                            <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-8" x-data="traspasoLotes()">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-bold">Lotes a traspasar</h2>
                            <p class="mt-1 text-sm theme-subtle">Solo se muestran lotes disponibles y no caducados. <span x-text="totalUnidades()"></span></p>
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
                                        @foreach(($lotesDisponibles ?? collect()) as $lote)
                                            <option value="{{ $lote->id }}">{{ $lote->folio }} · {{ $lote->producto?->nombre_producto ?? 'Sin producto' }} · {{ (int) $lote->inventarios->sum('stock') }} uds. · Caduca {{ $lote->fecha_de_caducidad ? \Carbon\Carbon::parse($lote->fecha_de_caducidad)->format('Y-m-d') : 'Sin fecha' }}</option>
                                        @endforeach
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

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('alertas.index', ['sucursal' => old('sucursal_b', $selectedSucursalId), 'filtro' => 'traspasos']) }}" class="theme-button theme-button-secondary">Cancelar</a>
                    <button type="submit" class="theme-button theme-button-primary">Enviar solicitud</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function traspasoLotes() {
            return {
                rows: {!! json_encode(collect(old('lotes', [['lote' => '', 'cantidad' => 1]]))->map(fn ($item) => ['key' => uniqid(), 'lote' => (string) ($item['lote'] ?? ''), 'cantidad' => (int) ($item['cantidad'] ?? 1)])->values()) !!},
                mapaStock: {!! json_encode(($lotesDisponibles ?? collect())->mapWithKeys(fn ($lote) => [$lote->id => (int) $lote->inventarios->sum('stock')])) !!},
                agregar() {
                    this.rows.push({ key: `${Date.now()}-${this.rows.length}`, lote: '', cantidad: 1 });
                },
                eliminar(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },
                stockDe(loteId) {
                    return this.mapaStock[String(loteId)] ?? 0;
                },
                totalUnidades() {
                    const total = this.rows.reduce((sum, row) => sum + (Number(row.cantidad) || 0), 0);

                    return this.rows.length === 0 ? '' : `${total} ${total === 1 ? 'unidad' : 'unidades'} en total.`;
                },
            };
        }

        if (window.Alpine) {
            window.Alpine.data('traspasoLotes', traspasoLotes);
        }

        document.addEventListener('alpine:init', () => {
            window.Alpine.data('traspasoLotes', traspasoLotes);
        });
    </script>

</x-layouts::app>
