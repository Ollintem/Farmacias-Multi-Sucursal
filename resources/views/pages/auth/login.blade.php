<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head', ['title' => __('Iniciar sesión')])
</head>
<body class="min-h-screen bg-[#e9efec] text-[#22332f] antialiased dark:bg-[#1a2a32] dark:text-[#e6efec]">
    <div class="flex min-h-screen w-full">
        <aside class="hidden w-[42%] flex-col justify-between bg-[#20444c] px-10 py-12 text-white lg:flex xl:w-[39%] xl:px-14">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0e9384] shadow-[0_10px_30px_rgba(14,147,132,0.35)]">
                    <span class="text-2xl font-black text-white">Rx</span>
                </div>
                <div>
                    <div class="text-3xl font-black tracking-tight text-white">FarmaERP</div>
                    <div class="text-sm text-slate-300">Sistema de Gestión Farmacéutica</div>
                </div>
            </div>

            <div class="mt-12">
                <h1 class="max-w-md text-5xl font-black leading-none tracking-[-0.06em] text-white xl:text-6xl">
                    Control total
                    <br />
                    de tu cadena
                    <br />
                    <span class="text-[#5ec8b4]">de farmacias.</span>
                </h1>

                <p class="mt-8 max-w-md text-2xl leading-relaxed text-[#c3d4cf]">
                    Administra sucursales, inventario,
                    <br />
                    ventas y reportes desde un solo
                    <br />
                    lugar. Seguro, rápido y confiable.
                </p>
            </div>

            <div class="mt-10 space-y-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-[#5ec8b4] ring-1 ring-white/15">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12h10M12 7v10m7-5a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-xl font-medium text-white">Multi-sucursal en tiempo real</span>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-[#0e9384] ring-1 ring-white/15">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-xl font-medium text-white">Control de lotes y caducidades</span>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-[#0e9384] ring-1 ring-white/15">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M7 11h10M9 15h6M5 7l1 12h12l1-12" /></svg>
                    </div>
                    <span class="text-xl font-medium text-white">POS optimizado para cajeros</span>
                </div>
            </div>
        </aside>

        <main class="flex flex-1 items-center justify-center bg-[#e9efec] px-6 py-10 sm:px-10 lg:px-16 xl:px-20">
            <div class="w-full max-w-135">
                <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#0e9384] shadow-[0_12px_28px_rgba(14,147,132,0.32)]">
                        <span class="text-lg font-black text-slate-900">Rx</span>
                    </div>
                    <div class="text-left">
                        <div class="text-2xl font-black tracking-tight text-slate-900">FarmaERP</div>
                        <div class="text-xs text-slate-500">Gestión farmacéutica</div>
                    </div>
                </div>

                <div class="rounded-[28px] bg-[#e9efec] px-0 py-0 sm:px-0">
                    <div class="mb-8 text-center">
                        <h2 class="text-5xl font-black tracking-tighter text-slate-900">Iniciar sesión</h2>
                        <p class="mt-4 text-xl text-slate-500">Ingresa tus credenciales para acceder al sistema</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        @if (session('status'))
                            <div class="rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-700" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="space-y-2">
                            <label for="email" class="block text-base font-medium text-slate-700">Correo electrónico</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="correo@ejemplo.com"
                                class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-4 text-base text-slate-900 placeholder:text-slate-400 transition-all duration-200 focus:border-[#0e9384] focus:outline-none focus:ring-4 focus:ring-[#0e9384]/15"
                                aria-describedby="email-error"
                            />
                            @error('email')
                                <p id="email-error" class="mt-1 text-sm text-red-500" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="block text-base font-medium text-slate-700">Contraseña</label>
                            <div class="relative">
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="h-14 w-full rounded-2xl border border-slate-300 bg-white px-4 pr-12 text-base text-slate-900 placeholder:text-slate-400 transition-all duration-200 focus:border-[#0e9384] focus:outline-none focus:ring-4 focus:ring-[#0e9384]/15"
                                    aria-describedby="password-error"
                                />
                                <button
                                    type="button"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 transition-colors hover:text-[#0e9384]"
                                    onclick="togglePasswordVisibility()"
                                    aria-label="Mostrar/ocultar contraseña"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-visible aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-hidden aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L3 3m8.278 8.278a2 2 0 012.829 0" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p id="password-error" class="mt-1 text-sm text-red-500" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between gap-3 pt-1">
                            <label for="remember" class="flex cursor-pointer items-center gap-3 text-base text-slate-600">
                                <input
                                    id="remember"
                                    name="remember"
                                    type="checkbox"
                                    value="1"
                                    {{ old('remember') ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-slate-300 bg-white text-[#0e9384] focus:ring-2 focus:ring-[#0e9384]/30 focus:ring-offset-0"
                                />
                                <span>Recordarme</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-base font-semibold text-[#0e9384] transition hover:text-[#0c7569] hover:underline">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif
                        </div>

                        <button
                            type="submit"
                            class="mt-2 inline-flex h-14 w-full items-center justify-center rounded-2xl bg-[#0e9384] text-lg font-bold text-white shadow-[0_16px_30px_rgba(14,147,132,0.28)] transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#0c7569] focus:outline-none focus:ring-4 focus:ring-[#0e9384]/20"
                        >
                            Iniciar sesión
                        </button>
                    </form>

                    <p class="mt-8 text-center text-sm text-slate-500">
                        Acceso restringido &middot; Solo personal autorizado
                    </p>
                </div>
            </div>
        </main>
    </div>

    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            const visibleIcon = document.querySelector('[data-visible]');
            const hiddenIcon = document.querySelector('[data-hidden]');

            if (input.type === 'password') {
                input.type = 'text';
                visibleIcon.classList.add('hidden');
                hiddenIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                visibleIcon.classList.remove('hidden');
                hiddenIcon.classList.add('hidden');
            }
        }
    </script>

    @fluxScripts
</body>
</html>
