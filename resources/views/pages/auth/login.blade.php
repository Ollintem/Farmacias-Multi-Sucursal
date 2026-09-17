<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('Iniciar sesión') }} - {{ config('app.name', 'FarmaERP') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-slate-50 antialiased">
    <div class="flex min-h-screen w-full">
        <!-- Panel Izquierdo - 31% -->
        <div class="hidden lg:flex lg:w-[31%] flex-col bg-slate-950 text-white p-8 md:p-12 xl:p-14">
            <!-- Branding FarmaERP -->
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-500">
                    <span class="text-xl font-bold text-white">Rx</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-bold tracking-tight">FarmaERP</span>
                    <p class="text-sm text-slate-400">Sistema de Gestión Farmacéutica</p>
                </div>
            </div>

            <!-- Texto Principal -->
            <div class="flex flex-1 flex-col justify-center py-12">
                <h1 class="text-4xl md:text-5xl font-bold leading-[1.1] tracking-tight">
                    Control total<br />
                    de tu cadena<br />
                    <span class="text-teal-400">de farmacias.</span>
                </h1>
                <p class="mt-6 max-w-md text-lg leading-relaxed text-slate-300">
                    Administra sucursales, inventario, ventas y reportes desde un solo lugar.
                    Seguro, rápido y confiable.
                </p>
            </div>

            <!-- Características + Copyright -->
            <div class="flex flex-col gap-8">
                <ul class="flex flex-col gap-4 border-t border-slate-800 pt-8">
                    <li class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-800/60">
                            <svg class="h-5 w-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <span class="font-medium text-slate-200">Multi-sucursal en tiempo real</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-800/60">
                            <svg class="h-5 w-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="font-medium text-slate-200">Control de lotes y caducidades</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-800/60">
                            <svg class="h-5 w-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <span class="font-medium text-slate-200">POS optimizado para cajeros</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-800/60">
                            <svg class="h-5 w-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="font-medium text-slate-200">Reportes administrativos completos</span>
                    </li>
                </ul>

                <p class="text-sm text-slate-500">
                    &copy; 2026 FarmaERP &middot; Todos los derechos reservados
                </p>
            </div>
        </div>

        <!-- Panel Derecho - 69% -->
        <div class="flex flex-1 flex-col justify-center bg-slate-50 px-6 py-10 sm:px-10 lg:px-16 xl:px-20">
            <div class="mx-auto w-full max-w-md">
                <!-- Branding móvil -->
                <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-500">
                        <span class="text-lg font-bold text-white">Rx</span>
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-lg font-bold tracking-tight text-slate-900">FarmaERP</span>
                        <p class="text-xs text-slate-500">Sistema de Gestión Farmacéutica</p>
                    </div>
                </div>

                <!-- Header del formulario -->
                <div class="mb-8 text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">Iniciar sesión</h2>
                    <p class="mt-2 text-slate-500">Ingresa tus credenciales para acceder al sistema</p>
                </div>

                <!-- Formulario de Login -->
                <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
                    @csrf

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-3" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Correo electrónico
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="correo@ejemplo.com"
                            class="h-12 w-full rounded-lg border border-slate-300 bg-white px-4 text-base text-slate-900 placeholder:text-slate-400 transition-all duration-200 hover:border-slate-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                            aria-describedby="email-error"
                        />
                        @error('email')
                            <p id="email-error" class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="h-12 w-full rounded-lg border border-slate-300 bg-white px-4 pr-12 text-base text-slate-900 placeholder:text-slate-400 transition-all duration-200 hover:border-slate-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                                aria-describedby="password-error"
                            />
                            <button
                                type="button"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition-colors hover:text-slate-600"
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
                            <p id="password-error" class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 bg-white text-teal-600 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                            />
                            <label for="remember" class="cursor-pointer text-sm text-slate-600">
                                Recordarme
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-teal-600 hover:text-teal-700 hover:underline">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="h-12 w-full rounded-lg bg-teal-600 text-base font-semibold text-white transition-colors duration-200 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                    >
                        Iniciar sesión
                    </button>
                </form>

                <!-- Texto inferior -->
                <p class="mt-8 text-center text-xs text-slate-400">
                    Acceso restringido &middot; Solo personal autorizado
                </p>
            </div>
        </div>
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
