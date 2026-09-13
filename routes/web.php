<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FarmaciasController;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación
Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');
Route::post('/', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // CRUD de farmacias
    Route::resource('farmacias', FarmaciasController::class);
});

require __DIR__.'/settings.php';
