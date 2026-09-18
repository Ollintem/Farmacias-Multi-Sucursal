<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\LotesController;
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
    })->name('dashboard')->middleware('checarPermisos:ver,dashboard');

    Route::get('/lotes-y-caducidades', [LotesController::class, 'index'])->name('lotes.index')->middleware('checarPermisos:ver,lotes-y-caducidades');
    Route::get('/lotes-y-caducidades/create', [LotesController::class, 'create'])->name('lotes.create')->middleware('checarPermisos:crear,lotes-y-caducidades');
    Route::post('/lotes-y-caducidades', [LotesController::class, 'store'])->name('lotes.store')->middleware('checarPermisos:crear,lotes-y-caducidades');

    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index')->middleware('checarPermisos:ver,usuarios')->middleware('checarPermisos:ver,usuarios');
    Route::get('/usuarios/create', [UsuariosController::class, 'create'])->name('usuarios.create')->middleware('checarPermisos:crear,usuarios')->middleware('checarPermisos:crear,usuarios');
    Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store')->middleware('checarPermisos:crear,usuarios')->middleware('checarPermisos:crear,usuarios');
    Route::get('/usuarios/{usuario}', [UsuariosController::class, 'show'])->name('usuarios.show')->middleware('checarPermisos:ver,usuarios')->middleware('checarPermisos:ver,usuarios');
    Route::get('/usuarios/{usuario}/edit', [UsuariosController::class, 'edit'])->name('usuarios.edit')->middleware('checarPermisos:editar,usuarios')->middleware('checarPermisos:editar,usuarios');
    Route::put('/usuarios/{usuario}', [UsuariosController::class, 'update'])->name('usuarios.update')->middleware('checarPermisos:editar,usuarios')->middleware('checarPermisos:editar,usuarios');
    Route::delete('/usuarios/{usuario}', [UsuariosController::class, 'delete'])->name('usuarios.delete')->middleware('checarPermisos:eliminar,usuarios')->middleware('checarPermisos:eliminar,usuarios');
    // Módulo: Sucursales
    Route::get('/sucursales', [SucursalesController::class, 'index'])->name('sucursales.index')->middleware('checarPermisos:ver,sucursales');
    Route::get('/sucursales/create', [SucursalesController::class, 'create'])->name('sucursales.create')->middleware('checarPermisos:crear,sucursales');
    Route::post('/sucursales', [SucursalesController::class, 'store'])->name('sucursales.store')->middleware('checarPermisos:crear,sucursales');

    // Módulos del catálogo (modulos.nombre_modulo) aún sin controlador propio.
    // Se exponen con una vista placeholder para que el sidebar no apunte a "#".
    Route::view('/punto-venta', 'pages.modulos.placeholder', ['tituloModulo' => 'Punto de venta'])->name('punto-venta.index')->middleware('checarPermisos:ver,punto-venta');
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index')->middleware('checarPermisos:ver,inventario');
    Route::get('/inventario/create', [InventarioController::class, 'create'])->name('inventario.create')->middleware('checarPermisos:crear,inventario');
    Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store')->middleware('checarPermisos:crear,inventario');
    Route::view('/caja', 'pages.modulos.placeholder', ['tituloModulo' => 'Caja'])->name('caja.index')->middleware('checarPermisos:ver,caja');
    Route::view('/reportes', 'pages.modulos.placeholder', ['tituloModulo' => 'Reportes'])->name('reportes.index')->middleware('checarPermisos:ver,reportes');
    Route::view('/alertas', 'pages.modulos.placeholder', ['tituloModulo' => 'Alertas'])->name('alertas.index')->middleware('checarPermisos:ver,alertas');
});

require __DIR__.'/settings.php';
