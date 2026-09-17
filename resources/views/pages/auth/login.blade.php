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
<body class="min-h-screen bg-white antialiased">
    <div class="flex h-screen w-full overflow-hidden">
        <!-- Panel Izquierdo - 31% -->
        <div class="hidden lg:flex lg:w-[31%] flex-col justify-between bg-slate-950 text-white p-8 md:p-12 xl:p-16">
            <!-- Branding FarmaERP -->
            <div class="flex flex-col items-start gap-2">
                <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-teal-500">
                    <span class="text-2xl font-bold text-white">Rx</span>
                </div>
                <span class="text-2xl font-bold tracking-tight">FarmaERP</span>
                <p class="text-slate-400 text-sm">Sistema de Gestión Farmacéutica</p>
            </div>

            <!-- Texto Principal -->
            <div class="flex-1 flex flex-col justify-center">
                <h1 class="text-4xl md:text-5xl xl:text-6xl font-bold leading-tight tracking-tight">
                    Control total<br />
                    de tu cadena<br />
                    <span class="text-teal-400">de farmacias.</span>
                </h1>
                <p class="mt-6 text-lg text-slate-300 max-w-md leading-relaxed">
                    Administra sucursales, inventario, ventas y reportes desde un solo lugar.
                    Seguro, rápido y confiable.
                </p>
            </div>

            <!-- Características -->
            <div class="space-y-4 border-t border-slate-800 pt-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800/50">
                        <svg class="h-5 w-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <span class="text-slate-200 font-medium">Multi-sucursal en tiempo real</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800/50">
                        <svg class="h-5 w-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-slate-200 font-medium">Control de lotes y caducidades</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800/50">
                        <svg class="h-5 w-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <span class="text-slate-200 font-medium">POS optimizado para cajeros</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800/50">
                        <svg class="h-5 w-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-slate-200 font-medium">Reportes administrativos completos</span>
                </div>
            </div>

            <!-- Copyright -->
            <p class="text-slate-500 text-sm text-center">
                &copy; 2026 FarmaERP &middot; Todos los derechos reservados
            </p>
        </div>

        <!-- Panel Derecho - 69% -->
        <div class="flex flex-1 flex-col justify-center bg-slate-50 p-6 md:p-10 lg:p-16 xl:p-20">
            <div class="w-full max-w-md mx-auto px-4">
                <!-- Header del formulario -->
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-slate-900">Iniciar sesión</h2>
                    <p class="mt-2 text-slate-500">Ingresa tus credenciales para acceder al sistema</p>
                </div>

                <!-- Formulario de Login -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 rounded-lg p-3" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
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
                            class="w-full h-12 px-4 text-base text-slate-900 bg-white border border-slate-300 rounded-lg placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:border-transparent transition-all duration-200 hover:border-slate-400"
                            aria-describedby="email-error"
                        />
                        @error('email')
                            <p id="email-error" class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-medium text-slate-700">
                                Contraseña
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm text-teal-600 hover:text-teal-700 hover:underline">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full h-12 px-4 text-base text-slate-900 bg-white border border-slate-300 rounded-lg pr-12 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:border-transparent transition-all duration-200 hover:border-slate-400"
                                aria-describedby="password-error"
                            />
                            <button
                                type="button"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                onclick="togglePasswordVisibility(this)"
                                aria-label="Mostrar/ocultar contraseña"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-visible>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-hidden>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L3 3m8.278 8.278a2 2 0 012.829 0" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p id="password-error" class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            value="1"
                            {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-2 border-slate-900 bg-white text-teal-600 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
                        />
                        <label for="remember" class="ml-3 text-sm text-slate-600 cursor-pointer">
                            Recordarme
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full h-12 bg-teal-600 text-white font-semibold text-base rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-colors duration-200"
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
        function togglePasswordVisibility(button) {
            const input = button.previousElementSibling;
            const visibleIcon = button.querySelector('[data-visible]');
            const hiddenIcon = button.querySelector('[data-hidden]');

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