<script>
    function compraLotes() {
        const presentaciones = {!! json_encode($presentacionPorProducto) !!};
        const filasIniciales = {!! json_encode(collect(old('lotes', [['id_producto' => '', 'cantidad' => 1, 'fecha_entrada' => now()->toDateString(), 'fecha_caducidad' => '']]))->map(fn ($item) => ['key' => uniqid(), 'producto' => (string) ($item['id_producto'] ?? ''), 'cantidad' => (int) ($item['cantidad'] ?? 1), 'fecha_entrada' => (string) ($item['fecha_entrada'] ?? now()->toDateString()), 'fecha_caducidad' => (string) ($item['fecha_caducidad'] ?? '')])->values()) !!};

        return {
            rows: filasIniciales,
            presentacion(producto) {
                const info = presentaciones[producto] ?? null;

                return info ? `${info.nombre} · ${info.unidades} unidad(es)` : '';
            },
            agregar() {
                this.rows.push({ key: `${Date.now()}-${this.rows.length}`, producto: '', cantidad: 1, fecha_entrada: '{{ now()->toDateString() }}', fecha_caducidad: '' });
            },
            eliminar(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
            totalUnidades() {
                const total = this.rows.reduce((sum, row) => sum + (Number(row.cantidad) || 0), 0);

                return this.rows.length === 0 ? '' : `${total} ${total === 1 ? 'unidad' : 'unidades'} en total.`;
            },
        };
    }

    if (window.Alpine) {
        window.Alpine.data('compraLotes', compraLotes);
    }

    document.addEventListener('alpine:init', () => {
        window.Alpine.data('compraLotes', compraLotes);
    });
</script>
