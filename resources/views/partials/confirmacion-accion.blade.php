<div
    x-data="{
        pendiente: null,
        abrir(evento) {
            const datos = evento.detail;

            this.pendiente = {
                ...datos,
                titulo: datos.modo === 'eliminar' ? 'Eliminar producto' : 'Desactivar producto',
                mensaje: datos.modo === 'eliminar'
                    ? `¿Eliminar «${datos.nombre}» del catálogo?`
                    : `¿Desactivar «${datos.nombre}»?`,
                detalle: datos.modo === 'eliminar'
                    ? 'Dejará de aparecer del catálogo y no se puede deshacer.'
                    : 'Dejará de aparecer en Punto de Venta y en Productos y stock.',
                accion: datos.modo === 'eliminar' ? 'Eliminar' : 'Desactivar',
                metodo: datos.modo === 'eliminar' ? 'DELETE' : 'PATCH',
            };
        },
        cerrar() {
            this.pendiente = null;
        },
    }"
    @confirmar-accion.window="abrir($event)"
    @keydown.escape.window="cerrar()"
    x-effect="if (pendiente) $nextTick(() => $refs.botonCancelar?.focus())"
    x-show="pendiente"
    x-cloak
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo-confirmacion"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="cerrar()"></div>

    <div
        class="relative w-full max-w-lg rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-2xl backdrop-blur-sm dark:border-[#1e3a5f]/60 dark:bg-[#0b1322]/95"
        @click.stop
    >
        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 id="titulo-confirmacion" class="text-lg font-bold text-slate-800 dark:text-white" x-text="pendiente?.titulo ?? ''"></h2>
            <button type="button" @click="cerrar()" class="text-slate-400 transition hover:text-slate-600 dark:hover:text-slate-200" aria-label="Cerrar">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <p class="font-medium text-slate-800 dark:text-slate-100" x-text="pendiente?.mensaje ?? ''"></p>
        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400" x-text="pendiente?.detalle ?? ''"></p>

        <form :action="pendiente?.ruta ?? ''" method="POST">
            @csrf
            <input type="hidden" name="_method" :value="pendiente?.metodo ?? 'DELETE'">
            <input type="hidden" name="es_activo" value="0" :disabled="pendiente?.modo !== 'desactivar'">

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-ref="botonCancelar" @click="cerrar()" class="theme-button theme-button-secondary">Cancelar</button>
                <button type="submit" class="theme-button bg-red-600 text-white transition hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700" x-text="pendiente?.accion ?? ''"></button>
            </div>
        </form>
    </div>
</div>
