<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    //
    public function showLoginForm(): View
    {
        return view('pages::auth.login');
    }

    public function login()
    {

        $credentials = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            request()->session()->regenerate();

            return redirect()->intended('/dashboard');
        } else {
            return back()->withErrors([
                'email' => 'Credenciales inválidas.',
            ]);
        }

    }
}
