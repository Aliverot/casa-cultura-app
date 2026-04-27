<?php

use App\Http\Controllers\ActivoController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ProfileController;
use App\Models\Activo;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $estadosPrestamo = ['En Prestamo', 'No disponible'];

    $stats = [
        'total' => Activo::count(),
        'prestados' => Activo::whereIn('estado_actual', $estadosPrestamo)->count(),
        'mantenimiento' => Activo::where('estado_actual', 'Mantenimiento')->count(),
        'disponibles' => Activo::where('estado_actual', 'Disponible')->count(),
    ];

    return view('dashboard', compact('stats'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/inventario', [ActivoController::class, 'index'])->name('activos.index');
    Route::get('/catalogo', [ActivoController::class, 'index'])->name('catalogo');

    Route::get('/instrumentos/nuevo', [ActivoController::class, 'create'])->name('activos.create');
    Route::post('/instrumentos', [ActivoController::class, 'store'])->name('activos.store');

    Route::get('/prestamos', [PrestamoController::class, 'activos'])->name('prestamos.index');
    Route::get('/prestamos-activos', [PrestamoController::class, 'activos'])->name('prestamos.activos');
    Route::get('/prestamos/nuevo', [PrestamoController::class, 'create'])->name('prestamos.create');
    Route::post('/prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
    Route::post('/prestamos/{id_detalle}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');
    Route::post('/prestamos/liquidar/{id}', [PrestamoController::class, 'liquidarPago'])->name('prestamos.liquidar');
    Route::get('/historial', [PrestamoController::class, 'historial'])->name('prestamos.historial');

    Route::get('/mantenimiento', [MantenimientoController::class, 'index'])->name('mantenimientos.index');
    Route::post('/mantenimiento', [MantenimientoController::class, 'store'])->name('mantenimiento.store');
    Route::post('/mantenimientos/guardar', [MantenimientoController::class, 'store'])->name('mantenimientos.store');
});

require __DIR__.'/auth.php';
