<?php

use App\Http\Controllers\ProfileController;
// 1. IMPORTANTE: Que estas líneas estén hasta arriba
use App\Http\Controllers\ActivoController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\MantenimientoController;
use Illuminate\Support\Facades\Route;
use App\Models\Activo;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $stats = [
        'total' => Activo::count(),
        'prestados' => Activo::where('estado_actual', 'En Prestamo')->count(),
        'mantenimiento' => Activo::where('estado_actual', 'Mantenimiento')->count(),
        'disponibles' => Activo::where('estado_actual', 'Disponible')->count(),
    ];
    return view('dashboard', compact('stats'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- TUS RUTAS DE LA CASA DE LA CULTURA ---
    
    // 1. Catálogo
    Route::get('/catalogo', [ActivoController::class, 'index'])->name('catalogo');
    
    // 2. Instrumentos Nuevos (ESTA ES LA QUE MARCABA ERROR)
    Route::get('/instrumentos/nuevo', [ActivoController::class, 'create'])->name('activos.create');
    Route::post('/instrumentos', [ActivoController::class, 'store'])->name('activos.store');
    
    // 3. Préstamos
    Route::post('/prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
    Route::get('/prestamos-activos', [PrestamoController::class, 'activos'])->name('prestamos.activos');
    Route::post('/prestamos/{id_detalle}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');
    
    // 4. Mantenimiento 
    // Rutas reales de Mantenimiento
    Route::get('/mantenimiento', [MantenimientoController::class, 'index'])->name('mantenimiento.index');
    Route::post('/mantenimiento', [MantenimientoController::class, 'store'])->name('mantenimiento.store');

    Route::post('/prestamos/liquidar/{id}', [App\Http\Controllers\PrestamoController::class, 'liquidarPago'])->name('prestamos.liquidar');

    Route::get('/historial', [App\Http\Controllers\PrestamoController::class, 'historial'])->name('prestamos.historial');
});

require __DIR__.'/auth.php';