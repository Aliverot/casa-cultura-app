<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mantenimiento;
use App\Models\Activo;

class MantenimientoController extends Controller
{
    // Mostrar la pantalla con la lista de instrumentos que necesitan atención
    public function index()
    {
        // Traemos los que están en "Mantenimiento" o los que ya pasaron su límite de horas
        $activosCandidatos = Activo::where('estado_actual', 'Mantenimiento')
                                    ->orWhereRaw('horas_uso >= limite_mantenimiento')
                                    ->get();

        return view('mantenimiento', compact('activosCandidatos'));
    }

    // Registrar la sesión de mantenimiento y liberar el instrumento
    public function store(Request $request)
    {
        $request->validate([
            'id_activo' => 'required|exists:activos,id_activo',
            'tipo' => 'required|string',
            'observaciones' => 'nullable|string'
        ]);

        // 1. Crear el registro en la bitácora de mantenimientos
        Mantenimiento::create([
            'id_activo' => $request->id_activo,
            'fecha_servicio' => now(),
            'tipo' => $request->tipo,
            'observaciones' => $request->observaciones
        ]);

        // 2. Resetear el instrumento
        $activo = Activo::findOrFail($request->id_activo);
        $activo->estado_actual = 'Disponible';
        $activo->horas_uso = 0; // Reiniciamos el contador de desgaste
        $activo->save();

        return redirect()->route('mantenimientos.index')->with('success', 'Mantenimiento registrado. El instrumento vuelve a estar disponible.');
    }
}
