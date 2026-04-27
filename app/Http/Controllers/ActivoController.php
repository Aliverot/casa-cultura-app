<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use Illuminate\Http\Request;

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
            'limite_mantenimiento' => 'required|numeric',
        ]);

        Activo::create([
            'nombre' => $request->nombre,
            'categoria' => $request->categoria,
            'limite_mantenimiento' => $request->limite_mantenimiento,
            'estado_actual' => 'Disponible',
            'horas_uso' => 0,
            'codigo_qr' => 'QR-' . rand(100000, 999999),
        ]);

        return redirect()->route('activos.index')->with('success', 'Instrumento agregado correctamente.');
    }
}
