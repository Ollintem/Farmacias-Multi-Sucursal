<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión.
     *
     * Salida: resources/views/pages/auth/login.blade.php.
     */
    public function showLoginForm(): View
    {
        return view('pages::auth.login');
    }

    /**
     * Valida credenciales, regenera la sesión y redirige al destino solicitado.
     *
     * Entrada: email y password enviados por el formulario de login.
     * Salida: dashboard si la autenticación es correcta o error en email.
     */
    public function login(): RedirectResponse
    {
        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            request()->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciales inválidas.',
        ]);
    }
}
