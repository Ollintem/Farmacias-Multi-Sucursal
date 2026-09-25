<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\LotesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SucursalesController;
use App\Http\Controllers\UsuariosController;
use App\Livewire\PuntoDeVenta;
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

    Route::view('/entradas-de-almacen', 'pages.modulos.placeholder', ['tituloModulo' => 'Entradas de almacén'])->name('entradas-de-almacen.index')->middleware('checarPermisos:ver,entradas-de-almacen');
    Route::get('/lotes-y-caducidades/create', [LotesController::class, 'create'])->name('lotes.create')->middleware('checarPermisos:crear,lotes-y-caducidades');
    Route::post('/lotes-y-caducidades', [LotesController::class, 'store'])->name('lotes.store')->middleware('checarPermisos:crear,lotes-y-caducidades');

    /*
    Modulo de usuarios y roles
    Este modulo es el encargado de crear, editar, inhablitar usuarios y roles
    */
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index')->middleware('checarPermisos:ver,usuarios');
    Route::get('/usuarios/create', [UsuariosController::class, 'create'])->name('usuarios.create')->middleware('checarPermisos:crear,usuarios');
    Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store')->middleware('checarPermisos:crear,usuarios');
    Route::get('/usuarios/{usuario}/permisos', [UsuariosController::class, 'permisos'])->name('usuarios.permisos')->middleware('checarPermisos:ver,usuarios');
    Route::put('/usuarios/{usuario}/permisos', [UsuariosController::class, 'updatePermisos'])->name('usuarios.updatePermisos')->middleware('checarPermisos:editar,usuarios');
    Route::get('/usuarios/{usuario}/edit', [UsuariosController::class, 'edit'])->name('usuarios.edit')->middleware('checarPermisos:editar,usuarios');
    Route::put('/usuarios/{usuario}', [UsuariosController::class, 'update'])->name('usuarios.update')->middleware('checarPermisos:editar,usuarios');
    Route::delete('/usuarios/{usuario}', [UsuariosController::class, 'delete'])->name('usuarios.delete')->middleware('checarPermisos:eliminar,usuarios');
    // Roles
    Route::post('/roles', [RolesController::class, 'store'])->name('roles.store')->middleware('checarPermisos:crear,roles');
    Route::put('/roles/{rol}', [RolesController::class, 'update'])->name('roles.update')->middleware('checarPermisos:editar,roles');
    Route::delete('/roles/{rol}', [RolesController::class, 'destroy'])->name('roles.destroy')->middleware('checarPermisos:eliminar,roles');

    // Módulo: Sucursales
    Route::get('/sucursales', [SucursalesController::class, 'index'])->name('sucursales.index')->middleware('checarPermisos:ver,sucursales');
    Route::get('/sucursales/create', [SucursalesController::class, 'create'])->name('sucursales.create')->middleware('checarPermisos:crear,sucursales');
    Route::post('/sucursales', [SucursalesController::class, 'store'])->name('sucursales.store')->middleware('checarPermisos:crear,sucursales');
    Route::get('/sucursales/{sucursal}/edit', [SucursalesController::class, 'edit'])->name('sucursales.edit')->middleware('checarPermisos:editar,sucursales');
    Route::put('/sucursales/{sucursal}', [SucursalesController::class, 'update'])->name('sucursales.update')->middleware('checarPermisos:editar,sucursales');

    // Punto de Venta
    Route::get('/punto-venta', PuntoDeVenta::class)
        ->name('punto-venta.index')
        ->middleware('checarPermisos:ver,punto-venta');
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index')->middleware('checarPermisos:ver,inventario');
    Route::get('/inventario/create', [InventarioController::class, 'create'])->name('inventario.create')->middleware('checarPermisos:crear,inventario');
    Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store')->middleware('checarPermisos:crear,inventario');
    Route::view('/caja', 'pages.modulos.placeholder', ['tituloModulo' => 'Caja'])->name('caja.index')->middleware('checarPermisos:ver,caja');
    Route::view('/reportes', 'pages.modulos.placeholder', ['tituloModulo' => 'Reportes'])->name('reportes.index')->middleware('checarPermisos:ver,reportes');
    Route::view('/alertas', 'pages.modulos.placeholder', ['tituloModulo' => 'Alertas'])->name('alertas.index')->middleware('checarPermisos:ver,alertas');
    Route::view('/traspasos', 'pages.modulos.placeholder', ['tituloModulo' => 'Traspasos'])->name('traspasos.index')->middleware('checarPermisos:ver,traspasos');
});

require __DIR__.'/settings.php';
