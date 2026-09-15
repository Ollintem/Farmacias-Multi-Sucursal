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
    Route::get('/usuarios/{usuario}/edit', [UsuariosController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{usuario}', [UsuariosController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [UsuariosController::class, 'delete'])->name('usuarios.delete');
    // Módulo: Sucursales
    Route::get('/sucursales', [SucursalesController::class, 'index'])->name('sucursales.index');
    Route::get('/sucursales/create', [SucursalesController::class, 'create'])->name('sucursales.create');
    Route::post('/sucursales', [SucursalesController::class, 'store'])->name('sucursales.store');

    // Módulos del catálogo (modulos.nombre_modulo) aún sin controlador propio.
    // Se exponen con una vista placeholder para que el sidebar no apunte a "#".
    Route::view('/punto-venta', 'pages.modulos.placeholder', ['tituloModulo' => 'Punto de venta'])->name('punto-venta.index');
    Route::view('/inventario', 'pages.modulos.placeholder', ['tituloModulo' => 'Inventario'])->name('inventario.index');
    Route::view('/lotes', 'pages.modulos.placeholder', ['tituloModulo' => 'Lotes y caducidades'])->name('lotes.index');
    Route::view('/entradas', 'pages.modulos.placeholder', ['tituloModulo' => 'Entradas de almacén'])->name('entradas.index');
    Route::view('/traspasos', 'pages.modulos.placeholder', ['tituloModulo' => 'Traspasos'])->name('traspasos.index');
    Route::view('/caja', 'pages.modulos.placeholder', ['tituloModulo' => 'Caja'])->name('caja.index');
    Route::view('/reportes', 'pages.modulos.placeholder', ['tituloModulo' => 'Reportes'])->name('reportes.index');
    Route::view('/alertas', 'pages.modulos.placeholder', ['tituloModulo' => 'Alertas'])->name('alertas.index');
});

require __DIR__.'/settings.php';
