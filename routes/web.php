<?php

use App\Http\Controllers\ProfileController;
// 1. IMPORTANTE: Que estas líneas estén hasta arriba
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\MantenimientoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // 2. TUS RUTAS DE LA CASA DE LA CULTURA DEBEN ESTAR AQUÍ ADENTRO
    Route::get('/catalogo', [ActivoController::class, 'index'])->name('catalogo');
    Route::post('/prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
});

require __DIR__.'/auth.php';