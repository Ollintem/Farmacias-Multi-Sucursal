<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SucursalesController;
use App\Http\Controllers\UsuariosController;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación
Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');
Route::post('/', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', [
            'sucursales' => Sucursal::orderBy('nombre_sucursal')->get(),
        ]);
    })->name('dashboard');

    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/create', [UsuariosController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');

    Route::get('/sucursales/create', [SucursalesController::class, 'create'])->name('sucursales.create');
    Route::post('/sucursales', [SucursalesController::class, 'store'])->name('sucursales.store');
});

require __DIR__.'/settings.php';
