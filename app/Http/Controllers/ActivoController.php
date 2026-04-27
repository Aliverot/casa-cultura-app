<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ActivoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $instrumentos = Activo::when($buscar, function ($query) use ($buscar) {
            return $query->where(function ($subQuery) use ($buscar) {
                $subQuery->where('nombre', 'ilike', "%{$buscar}%")
                    ->orWhere('codigo_qr', 'ilike', "%{$buscar}%");
            });
        })
            ->orderByRaw("CASE WHEN estado_actual = 'Disponible' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN estado_actual = 'Baja' THEN 1 ELSE 0 END")
            ->orderBy('nombre')
            ->get();

        return view('catalogo', compact('instrumentos'));
    }

    public function create()
    {
        return view('instrumentos_nuevos');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'limite_mantenimiento' => 'required|numeric|min:1',
        ]);

        Activo::create([
            'nombre' => trim($request->nombre),
            'categoria' => $request->categoria,
            'limite_mantenimiento' => $request->limite_mantenimiento,
            'estado_actual' => 'Disponible',
            'horas_uso' => 0,
            'codigo_qr' => 'QR-' . rand(100000, 999999),
        ]);

        return redirect()->route('activos.index')->with('success', 'Instrumento agregado correctamente.');
    }

    public function edit($id_activo)
    {
        $activo = Activo::findOrFail($id_activo);

        return view('activos.edit', compact('activo'));
    }

    public function update(Request $request, $id_activo)
    {
        $activo = Activo::findOrFail($id_activo);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'limite_mantenimiento' => 'required|numeric|min:1',
        ]);

        $activo->nombre = trim($request->nombre);
        $activo->categoria = $request->categoria;
        $activo->limite_mantenimiento = $request->limite_mantenimiento;
        $activo->save();

        return redirect()->route('activos.index')->with('success', 'Instrumento actualizado correctamente.');
    }

    public function baja($id_activo)
    {
        $activo = Activo::findOrFail($id_activo);

        if ($activo->estado_actual === 'Baja') {
            return redirect()->route('activos.edit', $activo->id_activo)->with('success', 'El instrumento ya estaba marcado como baja.');
        }

        if ($activo->estado_actual === 'No disponible') {
            throw ValidationException::withMessages([
                'activo' => 'No puedes dar de baja un instrumento mientras tiene un prestamo activo.',
            ]);
        }

        $activo->estado_actual = 'Baja';
        $activo->save();

        return redirect()->route('activos.index')->with('success', 'Instrumento dado de baja correctamente.');
    }
}
