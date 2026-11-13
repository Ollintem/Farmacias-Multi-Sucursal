<div
    x-show="abierto"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="display:none;"
    role="dialog"
    aria-modal="true"
    aria-labelledby="permisos-modal-titulo"
    @keydown.escape.window="abierto = false"
>
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="abierto = false"></div>
    <div class="relative flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900" @click.stop>
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-zinc-800">
            <div>
                <h3 id="permisos-modal-titulo" class="text-sm font-bold text-slate-800 dark:text-white">Permisos para <span x-text="nombreRol()"></span></h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Se guardan en <code>permisos_activados</code> y puedes cambiarlos después en “Permisos”.</p>
            </div>
            <button type="button" @click="abierto = false" class="rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-zinc-800" aria-label="Cerrar ventana de permisos">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex items-center justify-between gap-2 bg-slate-50 px-5 py-2.5 dark:bg-zinc-800/50">
            <span class="text-xs font-medium text-slate-600 dark:text-zinc-300" x-text="resumenPermisos()"></span>
            <button type="button" @click="toggleTodos()" class="text-xs font-semibold text-[#0c9f9c] hover:text-[#0a8582]" x-text="todosActivos() ? 'Desactivar todo' : 'Activar todo'"></button>
        </div>
        <div class="overflow-auto p-5">
            <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-zinc-800">
                        <tr class="text-left text-xs font-semibold text-slate-600 dark:text-zinc-300">
                            <th class="px-3 py-2.5">Módulo</th>
                            <th class="w-[72px] px-2 py-2.5 text-center">Ver</th>
                            <th class="w-[72px] px-2 py-2.5 text-center">Crear</th>
                            <th class="w-[72px] px-2 py-2.5 text-center">Editar</th>
                            <th class="w-[72px] px-2 py-2.5 text-center">Borrar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-700">
                        @foreach($modulos as $modulo)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40">
                                <td class="px-3 py-2.5 font-medium text-slate-700 dark:text-zinc-200">{{ $modulo->nombre_modulo }}</td>
                                @foreach(['ver','crear','editar','borrar'] as $accion)
                                    <td class="px-2 py-2.5 text-center">
                                        <input
                                            type="checkbox"
                                            name="permisos[{{ $modulo->id }}][{{ $accion }}]"
                                            value="1"
                                            aria-label="{{ $modulo->nombre_modulo }}: {{ $accion }}"
                                            :checked="tiene('{{ $modulo->id }}','{{ $accion }}')"
                                            @change="estado['{{ $modulo->id }}']['{{ $accion }}'] = $el.checked"
                                            class="h-4 w-4 rounded border-slate-300 text-[#0c9f9c] focus:ring-[#0c9f9c]"
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-900">
            <button type="button" @click="abierto = false" class="theme-button theme-button-secondary !py-2 !text-sm">Listo</button>
        </div>
    </div>
</div>
