<?php

use App\Http\Controllers\AlertasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\EntradasController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\LotesController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PresentacionController;
use App\Http\Controllers\ProveedoresController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SucursalesController;
use App\Http\Controllers\TraspasoController;
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

    Route::get('/entradas-de-almacen', [EntradasController::class, 'index'])->name('entradas-de-almacen.index')->middleware('checarPermisos:ver,entradas-de-almacen');
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index')->middleware('checarPermisos:ver,entradas-de-almacen');
    Route::get('/pedidos/create', [PedidoController::class, 'create'])->name('pedidos.create')->middleware('checarPermisos:crear,entradas-de-almacen');
    Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store')->middleware('checarPermisos:crear,entradas-de-almacen');
    Route::get('/traspasos', [TraspasoController::class, 'index'])->name('traspasos.index')->middleware('checarPermisos:ver,entradas-de-almacen');
    Route::get('/traspasos/create', [TraspasoController::class, 'create'])->name('traspasos.create')->middleware('checarPermisos:crear,entradas-de-almacen');
    Route::post('/traspasos', [TraspasoController::class, 'store'])->name('traspasos.store')->middleware('checarPermisos:crear,entradas-de-almacen');
    Route::get('/lotes-y-caducidades', [LotesController::class, 'index'])->name('lotes.index')->middleware('checarPermisos:ver,lotes-y-caducidades');
    Route::get('/lotes-y-caducidades/create', [LotesController::class, 'create'])->name('lotes.create')->middleware('checarPermisos:crear,lotes-y-caducidades');
    Route::post('/lotes-y-caducidades', [LotesController::class, 'store'])->name('lotes.store')->middleware('checarPermisos:crear,lotes-y-caducidades');
    Route::post('/lotes-y-caducidades/merma', [LotesController::class, 'merma'])->name('lotes.merma')->middleware('checarPermisos:editar,lotes-y-caducidades');
    Route::get('/lotes-y-caducidades/{lote}/edit', [LotesController::class, 'edit'])->name('lotes.edit')->middleware('checarPermisos:editar,lotes-y-caducidades');
    Route::put('/lotes-y-caducidades/{lote}', [LotesController::class, 'update'])->name('lotes.update')->middleware('checarPermisos:editar,lotes-y-caducidades');
    Route::post('/lotes-y-caducidades/{lote}/anular', [LotesController::class, 'anular'])->name('lotes.anular')->middleware('checarPermisos:eliminar,lotes-y-caducidades');

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
    Route::get('/inventario/stock', [InventarioController::class, 'stock'])->name('inventario.stock')->middleware('checarPermisos:ver,inventario');
    Route::get('/inventario/productos', [InventarioController::class, 'productos'])->name('inventario.productos')->middleware('checarPermisos:ver,inventario');
    Route::get('/inventario/create', [InventarioController::class, 'create'])->name('inventario.create')->middleware('checarPermisos:crear,inventario');
    Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store')->middleware('checarPermisos:crear,inventario');
    Route::get('/inventario/{producto}/edit', [InventarioController::class, 'edit'])->name('inventario.edit')->middleware('checarPermisos:editar,inventario');
    Route::put('/inventario/{producto}', [InventarioController::class, 'update'])->name('inventario.update')->middleware('checarPermisos:editar,inventario');
    Route::patch('/inventario/{producto}/estado', [InventarioController::class, 'cambiarEstado'])->name('inventario.estado')->middleware('checarPermisos:editar,inventario');
    Route::delete('/inventario/{producto}', [InventarioController::class, 'destroy'])->name('inventario.destroy')->middleware('checarPermisos:eliminar,inventario');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store')->middleware('checarPermisos:crear,inventario');
    Route::post('/presentaciones', [PresentacionController::class, 'store'])->name('presentaciones.store')->middleware('checarPermisos:crear,inventario');
    Route::view('/caja', 'pages.modulos.placeholder', ['tituloModulo' => 'Caja'])->name('caja.index')->middleware('checarPermisos:ver,caja');
    Route::view('/reportes', 'pages.modulos.placeholder', ['tituloModulo' => 'Reportes'])->name('reportes.index')->middleware('checarPermisos:ver,reportes');
    Route::get('/alertas', [AlertasController::class, 'index'])->name('alertas.index')->middleware('checarPermisos:ver,alertas');
    Route::get('/alertas/feed', [AlertasController::class, 'feed'])->name('alertas.feed')->middleware('checarPermisos:ver,alertas');
    Route::post('/alertas/leidas', [AlertasController::class, 'marcarLeidas'])->name('alertas.leidas')->middleware('checarPermisos:ver,alertas');
    Route::post('/alertas/leer', [AlertasController::class, 'marcarLeida'])->name('alertas.leer')->middleware('checarPermisos:ver,alertas');
    Route::post('/traspasos/{traspaso}/aceptar', [TraspasoController::class, 'aceptar'])->name('traspasos.aceptar')->middleware('checarPermisos:editar,alertas');
    Route::post('/traspasos/{traspaso}/rechazar', [TraspasoController::class, 'rechazar'])->name('traspasos.rechazar')->middleware('checarPermisos:editar,alertas');

    // Módulo: proveedores
    Route::get('/proveedores', [ProveedoresController::class, 'index'])->name('proveedores.index')->middleware('checarPermisos:ver,proveedores');
    Route::get('/proveedores/create', [ProveedoresController::class, 'create'])->name('proveedores.create')->middleware('checarPermisos:crear,proveedores');
    Route::post('/proveedores', [ProveedoresController::class, 'store'])->name('proveedores.store')->middleware('checarPermisos:crear,proveedores');
    Route::get('/proveedores/{proveedor}/edit', [ProveedoresController::class, 'edit'])->name('proveedores.edit')->middleware('checarPermisos:editar,proveedores');
    Route::put('/proveedores/{proveedor}', [ProveedoresController::class, 'update'])->name('proveedores.update')->middleware('checarPermisos:editar,proveedores');

});

require __DIR__.'/settings.php';
