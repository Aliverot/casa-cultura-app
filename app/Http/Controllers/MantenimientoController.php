<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\AlertaOperativa;
use App\Models\Mantenimiento;
use App\Services\AlertasOperativasService;
use Illuminate\Http\Request;

class MantenimientoController extends Controller
{
    public function index()
    {
        $alertas = app(AlertasOperativasService::class);
        $alertas->registrarTemporadaSiAplica();

        $activosEnMantenimiento = Activo::with(['mantenimientos' => function ($query) {
            $query->latest('fecha_servicio');
        }])
            ->where('estado_actual', 'Mantenimiento')
            ->orderBy('nombre')
            ->get();

        $activosPorAtender = Activo::with(['mantenimientos' => function ($query) {
            $query->latest('fecha_servicio');
        }])
            ->where('estado_actual', 'Disponible')
            ->whereRaw('horas_uso >= limite_mantenimiento')
            ->orderByDesc('horas_uso')
            ->get();

        $activosCandidatos = $activosEnMantenimiento
            ->concat($activosPorAtender)
            ->unique('id_activo')
            ->values();

        $historialMantenimiento = Mantenimiento::with('activo')
            ->orderByDesc('fecha_servicio')
            ->limit(10)
            ->get();
        $alertasOperativas = $alertas->alertasPendientes();
        $estadosCondicion = Activo::ESTADOS_CONDICION;

        return view('mantenimiento', compact(
            'activosEnMantenimiento',
            'activosPorAtender',
            'activosCandidatos',
            'historialMantenimiento',
            'alertasOperativas',
            'estadosCondicion'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_activo' => 'required|exists:activos,id_activo',
            'tipo' => 'required|string|max:255',
            'costo_servicio' => 'nullable|numeric|min:0',
            'estado_condicion' => 'nullable|in:' . implode(',', Activo::ESTADOS_CONDICION),
            'es_preventivo' => 'nullable|boolean',
            'observaciones' => 'nullable|string',
        ]);

        Mantenimiento::create([
            'id_activo' => $request->id_activo,
            'fecha_servicio' => now(),
            'tipo' => trim($request->tipo),
            'costo_servicio' => $request->filled('costo_servicio') ? $request->costo_servicio : 0,
            'es_preventivo' => $request->boolean('es_preventivo'),
            'observaciones' => $request->filled('observaciones') ? trim($request->observaciones) : null,
        ]);

        $activo = Activo::findOrFail($request->id_activo);
        $activo->estado_actual = 'Disponible';
        $activo->estado_condicion = $request->input('estado_condicion', $request->filled('observaciones') ? 'Funcional con detalles' : 'Excelente');
        $activo->horas_uso = 0;
        $activo->save();

        AlertaOperativa::where('id_activo', $activo->id_activo)
            ->where('tipo', 'Fragilidad/Mal Uso')
            ->where('estado', 'Pendiente')
            ->update(['estado' => 'Resuelta']);

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento registrado. El instrumento vuelve a estar disponible.');
    }
}
