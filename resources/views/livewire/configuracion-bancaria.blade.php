<div class="mx-auto max-w-2xl py-8">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Configuracion bancaria</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Datos bancarios para pagos por transferencia</p>
    </div>

    @if($guardado)
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-800 dark:bg-emerald-900/30">
            <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Configuracion guardada correctamente.</p>
        </div>
    @endif

    <form wire:submit="guardar" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="space-y-5">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-zinc-300">Banco</label>
                <input
                    type="text"
                    wire:model="banco"
                    placeholder="BBVA Bancomer"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                >
                @error('banco') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-zinc-300">CLABE interbancaria</label>
                <input
                    type="text"
                    wire:model="clabe"
                    maxlength="18"
                    placeholder="012 180 0123456789 01"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                >
                @error('clabe') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-zinc-300">Beneficiario</label>
                <input
                    type="text"
                    wire:model="beneficiario"
                    placeholder="Farmacia Ixtapaluca S.A."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                >
                @error('beneficiario') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-zinc-300">Numero de cuenta</label>
                <input
                    type="text"
                    wire:model="numeroCuenta"
                    placeholder="0123456789"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                >
                @error('numeroCuenta') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-zinc-300">Correo de contacto <span class="font-normal text-slate-400">(opcional)</span></label>
                <input
                    type="email"
                    wire:model="correoContacto"
                    placeholder="contacto@farmacia.com"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-[#0c9f9c] focus:ring-2 focus:ring-[#0c9f9c]/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500"
                >
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button
                type="submit"
                class="flex items-center gap-2 rounded-xl bg-[#0c9f9c] px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-[#0c9f9c]/20 transition hover:bg-[#0a8582] active:scale-[0.98]"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Guardar cambios
            </button>
        </div>
    </form>
</div>
