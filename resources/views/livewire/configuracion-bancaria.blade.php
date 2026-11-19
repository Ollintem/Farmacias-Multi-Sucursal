<div class="module-page noti-page">
    <div class="module-page-inner noti-full noti-formal">
    @if($guardado)
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 2500)"
            x-show="show"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-4 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200"
            role="status"
        >
            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
            Configuración guardada correctamente.
        </div>
    @endif

    <section class="module-card noti-listado" aria-label="Datos bancarios">
        <header class="noti-listado-cabecera noti-listado-cabecera-empresa">
            <div>
                <h2>Datos bancarios</h2>
                <p class="theme-subtle text-sm">Datos bancarios para pagos por transferencia</p>
            </div>
        </header>
        <div class="p-5 sm:p-6">
    <form wire:submit="guardar" class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-7 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Banco</label>
                <input
                    type="text"
                    wire:model="banco"
                    placeholder="BBVA Bancomer"
                    class="theme-input"
                >
                @error('banco') <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">CLABE interbancaria</label>
                <input
                    type="text"
                    wire:model="clabe"
                    maxlength="18"
                    placeholder="012 180 0123456789 01"
                    class="theme-input tracking-wider"
                >
                @error('clabe') <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Beneficiario</label>
                <input
                    type="text"
                    wire:model="beneficiario"
                    placeholder="Farmacia Ixtapaluca S.A."
                    class="theme-input"
                >
                @error('beneficiario') <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Número de cuenta</label>
                <input
                    type="text"
                    wire:model="numeroCuenta"
                    placeholder="0123456789"
                    class="theme-input tracking-wider"
                >
                @error('numeroCuenta') <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Correo de contacto <span class="font-normal text-slate-400">(opcional)</span></label>
                <input
                    type="email"
                    wire:model="correoContacto"
                    placeholder="contacto@farmacia.com"
                    class="theme-input"
                >
            </div>
        </div>

        <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a
                href="{{ url()->previous() }}"
                wire:navigate
                class="theme-button theme-button-secondary justify-center"
            >
                Cancelar
            </a>
            <button
                type="submit"
                class="theme-button theme-button-primary justify-center"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Guardar cambios
            </button>
        </div>
    </form>
        </div>
    </section>
    </div>
</div>
