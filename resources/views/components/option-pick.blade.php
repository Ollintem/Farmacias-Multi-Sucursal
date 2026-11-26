@props(['name', 'options' => [], 'value' => '', 'autoSubmit' => false, 'label' => null, 'placeholder' => 'Seleccionar una opción', 'id' => null])

{{-- Selector colapsable: cerrado muestra solo la opción elegida (o el
     texto de ayuda); la lista con scroll aparece únicamente al abrirlo.
     Sin selección el hidden viaja vacío; con autoSubmit se envía el
     formulario más cercano al elegir. --}}
@php($valorInicial = (string) old($name, $value))
@php($mapa = collect($options))
@php($etiquetaInicial = ($valorInicial !== '' && $mapa->has($valorInicial)) ? $mapa->get($valorInicial) : $placeholder)

<div
    class="relative"
    x-data='{
        valor: @json($valorInicial),
        etiqueta: @json($etiquetaInicial),
        abierto: false,
        auto: {{ $autoSubmit ? "true" : "false" }},
        elegir(id, etiqueta) {
            this.valor = String(id);
            this.etiqueta = etiqueta;
            this.abierto = false;
            this.detener();
            this.$dispatch("option-pick-changed", { name: @json($name), value: this.valor, label: etiqueta });
            if (this.auto) { this.$el.closest("form").requestSubmit(); }
        },
        alternar() {
            this.abierto = ! this.abierto;
            if (this.abierto) {
                this.$nextTick(() => {
                    if (! this.abierto) { return; }
                    this.posicionar();
                    this._alMover = () => this.posicionar();
                    window.addEventListener("scroll", this._alMover, { passive: true, capture: true });
                    window.addEventListener("resize", this._alMover);
                });
            } else {
                this.detener();
            }
        },
        posicionar() {
            const rect = this.$refs.gatillo.getBoundingClientRect();
            const panel = this.$refs.panel;
            const alto = panel.offsetHeight;

            panel.style.left = rect.left + "px";
            panel.style.width = rect.width + "px";
            panel.style.top = (window.innerHeight - rect.bottom < alto + 8 && rect.top > alto + 8)
                ? (rect.top - 8 - alto) + "px"
                : (rect.bottom + 8) + "px";
        },
        detener() {
            if (this._alMover) {
                window.removeEventListener("scroll", this._alMover, { capture: true });
                window.removeEventListener("resize", this._alMover);
                this._alMover = null;
            }
        },
        cerrarSiFuera(event) {
            if (! this.abierto) { return; }
            const panel = this.$refs.panel;
            const gatillo = this.$refs.gatillo;
            if (gatillo?.contains(event.target) || panel?.contains(event.target)) { return; }
            this.abierto = false;
            this.detener();
        }
    }'
    @click.window="cerrarSiFuera($event)"
    @keydown.escape.window="abierto = false; detener()"
>
    <input type="hidden" name="{{ $name }}" :value="valor">

    <button
        type="button"
        x-ref="gatillo"
        @if($id) id="{{ $id }}" @endif
        @click="alternar()"
        :aria-expanded="abierto"
        aria-haspopup="listbox"
        class="theme-input flex cursor-pointer items-center justify-between gap-2 text-left"
    >
        <span
            :class="valor === '' ? 'opacity-60' : 'font-semibold'"
            x-text="etiqueta"
        ></span>
        <svg
            class="h-4 w-4 shrink-0 text-slate-400 transition-transform"
            :class="abierto && 'rotate-180'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <template x-teleport="body">
    <div
        x-ref="panel"
        x-show="abierto"
        x-cloak
        x-transition.opacity
        class="fixed z-[70] rounded-xl bg-white p-2 shadow-xl dark:border dark:border-slate-700 dark:bg-slate-900"
    >
        <div
            class="farma-pick-scroll"
            role="listbox"
            @if($label) aria-label="{{ $label }}" @endif
        >
        @forelse($options as $optValue => $optLabel)
            <button
                type="button"
                role="option"
                :aria-selected="String(valor) === '{{ $optValue }}'"
                @click="elegir('{{ $optValue }}', $el.querySelector('[data-etiqueta]').textContent)"
                :class="String(valor) === '{{ $optValue }}' ? 'border-[#0a5f56] bg-gradient-to-r from-[#0e9384] to-[#0c7569] text-white shadow-md shadow-teal-900/25 dark:border-emerald-300 dark:from-emerald-400 dark:to-emerald-500 dark:text-[#052e2b]' : 'border-slate-300 bg-white text-slate-700 hover:border-[#0e9384] hover:bg-emerald-50 hover:text-[#0b6e68] dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-emerald-500 dark:hover:bg-emerald-500/15 dark:hover:text-emerald-100'"
                class="flex min-h-11 w-full cursor-pointer items-center justify-between gap-2 rounded-xl border-[1.5px] px-3 py-2 text-left text-sm font-semibold transition active:scale-[0.99]"
            >
                <span data-etiqueta class="truncate">{{ $optLabel }}</span>
                <svg
                    x-show="String(valor) === '{{ $optValue }}'"
                    x-cloak
                    class="h-4 w-4 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </button>
        @empty
            <p class="px-3 py-4 text-center text-sm theme-subtle">Sin opciones disponibles.</p>
        @endforelse
        </div>
    </div>
    </template>
</div>
