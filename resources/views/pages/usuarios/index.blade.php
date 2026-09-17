<x-layouts::app :title="__('Usuarios')">
    <div id="theme-shell" class="theme-light">
        <div class="theme-shell-inner">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-emerald-500">Personal</p>
                    <h1 class="mt-2 text-3xl font-bold">Usuarios</h1>
                </div>

                <div class="theme-tools">
                    <a href="{{ route('dashboard') }}" class="theme-button theme-button-secondary">Dashboard</a>
                    <a href="{{ route('usuarios.create') }}" class="theme-button theme-button-primary">Nuevo usuario</a>
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
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Nombre</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Usuario</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Email</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Rol</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sucursal</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Estado</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500" colspan="3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($usuarios as $usuario)
                                <tr class="bg-transparent hover:bg-emerald-50/50">
                                    <td class="px-4 py-4 text-sm">{{ $usuario->nombre }} {{ $usuario->apellido }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $usuario->nombre_usuario }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $usuario->email }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $usuario->rol?->tipo_rol ?? 'Sin rol' }}</td>
                                    <td class="px-4 py-4 text-sm">{{ $usuario->sucursal?->nombre_sucursal ?? 'Sin sucursal' }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        @if($usuario->es_activo)
                                            <span class="inline-flex rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-600">Activo</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-red-500/15 px-2.5 py-1 text-xs font-semibold text-red-600">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        <a href="{{ route('usuarios.show', $usuario) }}" class="text-slate-600 hover:text-slate-800">Ver</a>
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        <a href="{{ route('usuarios.edit', $usuario) }}" class="text-emerald-600 hover:text-emerald-800">Editar</a>
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        @if($usuario->id === auth()->id() || $usuario->rol?->tipo_rol === 'SuperAdmin')
                                            <span class="text-gray-400">No se puede eliminar</span>
                                        @else
                                        <form action="{{ route('usuarios.delete', $usuario) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Eliminar</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-10 text-center text-sm text-slate-500">No hay usuarios registrados aún.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
