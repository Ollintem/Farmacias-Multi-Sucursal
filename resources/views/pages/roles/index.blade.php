<x-layouts::app :title="__('Roles')">
    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Usuarios y roles</p>
                    <h1 class="mt-2 text-3xl font-bold">Roles</h1>
                </div>

                <div class="theme-tools">
                    <a href="{{ route('usuarios.index') }}" class="theme-button theme-button-secondary">Usuarios</a>
                    <a href="{{ route('roles.create') }}" class="theme-button theme-button-primary">Nuevo rol</a>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-600">
                    {{ session('error') }}
                </div>
            @endif

            <div class="theme-table">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left">
                        <thead class="bg-emerald-50/80">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Rol</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Descripción</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Usuarios</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500" colspan="3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($roles as $rol)
                                <tr class="bg-transparent hover:bg-emerald-50/50">
                                    <td class="px-4 py-4 text-sm font-medium">{{ $rol->tipo_rol }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $rol->descripcion ?? 'Sin descripción' }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $rol->usuarios_count ?? $rol->usuarios()->count() }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        <a href="{{ route('roles.show', $rol) }}" class="text-slate-600 hover:text-slate-800">Ver</a>
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        @if($rol->tipo_rol === 'SuperAdmin')
                                            <span class="text-gray-400">No se puede editar</span>
                                        @else
                                            <a href="{{ route('roles.edit', $rol) }}" class="text-emerald-600 hover:text-emerald-800">Editar</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        @if($rol->tipo_rol === 'SuperAdmin')
                                            <span class="text-gray-400">No se puede eliminar</span>
                                        @else
                                        <form action="{{ route('roles.destroy', $rol) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este rol?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No hay roles registrados aún.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
