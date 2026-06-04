<?php

use App\Http\Controllers\ActivoController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ProfileController;
use App\Models\Activo;
use App\Models\AlertaOperativa;
use App\Services\AlertasOperativasService;
use App\Services\AsistenteNotificacionesDiario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $alertas = app(AlertasOperativasService::class);
    app(AsistenteNotificacionesDiario::class)->revisarAgendaDelDia();
    $alertas->registrarTemporadaSiAplica();
    $estadosPrestamo = ['En Prestamo', 'No disponible'];

    $stats = [
        'total' => Activo::count(),
        'prestados' => Activo::whereIn('estado_actual', $estadosPrestamo)->count(),
        'mantenimiento' => Activo::where('estado_actual', 'Mantenimiento')->count(),
        'disponibles' => Activo::where('estado_actual', 'Disponible')->count(),
    ];

    $metricasDemanda = Activo::query()
        ->leftJoin('detalle_prestamos', 'activos.id_activo', '=', 'detalle_prestamos.id_activo')
        ->select('activos.id_activo', 'activos.nombre', 'activos.categoria')
        ->selectRaw('COUNT(detalle_prestamos.id_detalle) as total_prestamos')
        ->groupBy('activos.id_activo', 'activos.nombre', 'activos.categoria')
        ->orderByDesc('total_prestamos')
        ->orderBy('activos.nombre')
        ->limit(5)
        ->get()
        ->map(function ($item) {
            $item->total_prestamos = (int) $item->total_prestamos;

            return $item;
        });

    $maxPrestamos = max(1, (int) $metricasDemanda->max('total_prestamos'));
    $alertasOperativas = $alertas->alertasPendientes(null);

    return view('dashboard', compact('stats', 'metricasDemanda', 'maxPrestamos', 'alertasOperativas'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/inventario', [ActivoController::class, 'index'])->name('activos.index');
    Route::get('/catalogo', [ActivoController::class, 'index'])->name('catalogo');

    Route::get('/instrumentos/nuevo', [ActivoController::class, 'create'])->name('activos.create');
    Route::post('/instrumentos', [ActivoController::class, 'store'])->name('activos.store');
    Route::get('/instrumentos/{id_activo}/editar', [ActivoController::class, 'edit'])->name('activos.edit');
    Route::patch('/instrumentos/{id_activo}', [ActivoController::class, 'update'])->name('activos.update');
    Route::post('/instrumentos/{id_activo}/baja', [ActivoController::class, 'baja'])->name('activos.baja');

    Route::get('/prestamos', [PrestamoController::class, 'activos'])->name('prestamos.index');
    Route::get('/prestamos-activos', [PrestamoController::class, 'activos'])->name('prestamos.activos');
    Route::get('/prestamos/nuevo', [PrestamoController::class, 'create'])->name('prestamos.create');
    Route::post('/prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
    Route::post('/prestamos/{id_detalle}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');
    Route::get('/historial', [PrestamoController::class, 'historial'])->name('prestamos.historial');
    Route::get('/historial/reporte.csv', [PrestamoController::class, 'exportarHistorialCsv'])->name('prestamos.historial.csv');

    Route::get('/mantenimiento', [MantenimientoController::class, 'index'])->name('mantenimientos.index');
    Route::post('/mantenimiento', [MantenimientoController::class, 'store'])->name('mantenimiento.store');
    Route::post('/mantenimientos/guardar', [MantenimientoController::class, 'store'])->name('mantenimientos.store');

    Route::get('/temporadas-base', function () {
        $temporadas = DB::table('temporadas_base')
            ->orderBy('fecha_inicio')
            ->orderBy('nombre')
            ->get();

        return view('temporadas_base', compact('temporadas'));
    })->name('temporadas-base.index');

    Route::post('/temporadas-base', function (Request $request, AlertasOperativasService $alertas) {
        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:temporadas_base,nombre',
            'fecha_inicio' => 'required|date_format:m-d',
            'fecha_fin' => 'required|date_format:m-d',
            'dias_anticipacion' => 'required|integer|min:0|max:365',
            'activa' => 'nullable|boolean',
        ]);

        DB::table('temporadas_base')->insert([
            'nombre' => trim($data['nombre']),
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'dias_anticipacion' => (int) $data['dias_anticipacion'],
            'activa' => $request->boolean('activa'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $alertas->registrarTemporadaSiAplica();

        return redirect()->route('temporadas-base.index')->with('success', 'Fecha de temporada agregada correctamente.');
    })->name('temporadas-base.store');

    Route::patch('/temporadas-base/{id_temporada}', function ($id_temporada, Request $request, AlertasOperativasService $alertas) {
        $temporada = DB::table('temporadas_base')->where('id_temporada', $id_temporada)->first();
        abort_if(! $temporada, 404);

        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:temporadas_base,nombre,'.$id_temporada.',id_temporada',
            'fecha_inicio' => 'required|date_format:m-d',
            'fecha_fin' => 'required|date_format:m-d',
            'dias_anticipacion' => 'required|integer|min:0|max:365',
            'activa' => 'nullable|boolean',
        ]);

        DB::table('temporadas_base')
            ->where('id_temporada', $id_temporada)
            ->update([
                'nombre' => trim($data['nombre']),
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'dias_anticipacion' => (int) $data['dias_anticipacion'],
                'activa' => $request->boolean('activa'),
                'updated_at' => now(),
            ]);

        $alertas->registrarTemporadaSiAplica();

        return redirect()->route('temporadas-base.index')->with('success', 'Fecha de temporada actualizada correctamente.');
    })->name('temporadas-base.update');

    Route::post('/temporadas-base/{id_temporada}/estado', function ($id_temporada, AlertasOperativasService $alertas) {
        $temporada = DB::table('temporadas_base')->where('id_temporada', $id_temporada)->first();
        abort_if(! $temporada, 404);

        DB::table('temporadas_base')
            ->where('id_temporada', $id_temporada)
            ->update([
                'activa' => ! (bool) $temporada->activa,
                'updated_at' => now(),
            ]);

        $alertas->registrarTemporadaSiAplica();

        return back()->with('success', 'Estado de temporada actualizado.');
    })->name('temporadas-base.estado');

    Route::post('/temporadas-base/precargar', function (AlertasOperativasService $alertas) {
        $total = $alertas->precargarTemporadasBase();
        $alertas->registrarTemporadaSiAplica();

        return redirect()->route('temporadas-base.index')->with('success', "Fechas base de temporada actualizadas: {$total} registros.");
    })->name('temporadas-base.precargar');

    Route::post('/alertas/{id_alerta}/resolver', function ($id_alerta) {
        $alerta = AlertaOperativa::findOrFail($id_alerta);
        $alerta->estado = 'Resuelta';
        $alerta->save();

        return back()->with('success', 'Alerta marcada como resuelta.');
    })->name('alertas.resolver');
});

require __DIR__.'/auth.php';
