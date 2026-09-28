<script>
    function buscadorTabla() {
        return {
            texto: '',
            filtrar(contenedor) {
                const consulta = this.normalizar(this.texto);
                let visibles = 0;

                contenedor.querySelectorAll('[data-buscar]').forEach((fila) => {
                    const coincide = consulta === '' || this.normalizar(fila.dataset.buscar).includes(consulta);

                    fila.style.display = coincide ? '' : 'none';

                    if (coincide) {
                        visibles++;
                    }
                });

                const vacio = contenedor.querySelector('[data-vacio]');

                if (vacio) {
                    vacio.style.display = visibles === 0 ? '' : 'none';
                }
            },
            normalizar(valor) {
                return String(valor ?? '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .trim()
                    .toLowerCase();
            },
        };
    }
</script>
