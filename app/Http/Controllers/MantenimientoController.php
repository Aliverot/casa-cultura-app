<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\Mantenimiento;
use Illuminate\Http\Request;

class MantenimientoController extends Controller
{
    public function index()
    {
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

        return view('mantenimiento', compact(
            'activosEnMantenimiento',
            'activosPorAtender',
            'activosCandidatos',
            'historialMantenimiento'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_activo' => 'required|exists:activos,id_activo',
            'tipo' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        Mantenimiento::create([
            'id_activo' => $request->id_activo,
            'fecha_servicio' => now(),
            'tipo' => trim($request->tipo),
            'observaciones' => $request->filled('observaciones') ? trim($request->observaciones) : null,
        ]);

        $activo = Activo::findOrFail($request->id_activo);
        $activo->estado_actual = 'Disponible';
        $activo->horas_uso = 0;
        $activo->save();

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento registrado. El instrumento vuelve a estar disponible.');
    }
}
