<script>
    function traspasoLotes() {
        return {
            rows: {!! json_encode(collect(old('lotes', [['lote' => '', 'cantidad' => 1]]))->map(fn ($item) => ['key' => uniqid(), 'lote' => (string) ($item['lote'] ?? ''), 'cantidad' => (int) ($item['cantidad'] ?? 1)])->values()) !!},
            mapaStock: {!! json_encode(($lotesDisponibles ?? collect())->mapWithKeys(fn ($lote) => [$lote->id => (int) ($lote->inventarios->firstWhere('id_sucursal', $origenLotesId)?->stock ?? 0)])) !!},
            init() {
                // Al cambiar la sucursal elegible se recarga el formulario
                // para que el SELECT de lotes consulte el inventario de la
                // sucursal origen con la elección ya aplicada.
                document.getElementById('picker-sucursal')?.addEventListener('click', () => setTimeout(() => this.recargarPorSucursal(), 60));
            },
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
            recargarPorSucursal() {
                const picker = document.getElementById('picker-sucursal');
                const input = picker?.dataset.input || '';
                const elegido = (input && picker?.querySelector('input[name="' + input + '"]')?.value) || '';
                const actual = picker?.dataset.actual || '';
                const param = picker?.dataset.param || '';

                if (param && elegido !== actual) {
                    const base = picker?.dataset.baseUrl || window.location.pathname;
                    window.location.href = elegido ? `${base}?${param}=${encodeURIComponent(elegido)}` : base;
                }
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
