<div class="module-card p-5">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Acceso rápido</h2>
        <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-600">Superadmin</span>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <a href="{{ route('usuarios.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#0c9f9c] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800/60 dark:hover:border-[#0c9f9c]">
            <p class="text-base font-semibold">Usuarios</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Gestión de personal</p>
        </a>

        <a href="#" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#0c9f9c] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800/60 dark:hover:border-[#0c9f9c]">
            <p class="text-base font-semibold">Inventario</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Productos y stock</p>
        </a>

        <a href="#" class="rounded-2xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#0c9f9c] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800/60 dark:hover:border-[#0c9f9c]">
            <p class="text-base font-semibold">Sucursales</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Control por tienda</p>
        </a>
    </div>
</div>
