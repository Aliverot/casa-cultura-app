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
                    ->orWhere('modelo', 'ilike', "%{$buscar}%")
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
        $estadosCondicion = Activo::ESTADOS_CONDICION;

        return view('instrumentos_nuevos', compact('estadosCondicion'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'categoria' => 'required|string|max:255',
            'valor_original' => 'nullable|numeric|min:0',
            'limite_mantenimiento' => 'required|numeric|min:1',
            'estado_condicion' => 'nullable|in:' . implode(',', Activo::ESTADOS_CONDICION),
        ]);

        Activo::create([
            'nombre' => trim($request->nombre),
            'modelo' => $request->filled('modelo') ? trim($request->modelo) : null,
            'categoria' => $request->categoria,
            'valor_original' => $request->filled('valor_original') ? $request->valor_original : null,
            'limite_mantenimiento' => $request->limite_mantenimiento,
            'estado_actual' => match ($request->input('estado_condicion', 'Excelente')) {
                'En reparacion' => 'Mantenimiento',
                'Baja definitiva' => 'Baja',
                default => 'Disponible',
            },
            'estado_condicion' => $request->input('estado_condicion', 'Excelente'),
            'horas_uso' => 0,
            'codigo_qr' => 'QR-' . rand(100000, 999999),
        ]);

        return redirect()->route('activos.index')->with('success', 'Instrumento agregado correctamente.');
    }

    public function edit($id_activo)
    {
        $activo = Activo::findOrFail($id_activo);
        $estadosCondicion = Activo::ESTADOS_CONDICION;

        return view('activos.edit', compact('activo', 'estadosCondicion'));
    }

    public function update(Request $request, $id_activo)
    {
        $activo = Activo::findOrFail($id_activo);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'categoria' => 'required|string|max:255',
            'valor_original' => 'nullable|numeric|min:0',
            'limite_mantenimiento' => 'required|numeric|min:1',
            'estado_condicion' => 'nullable|in:' . implode(',', Activo::ESTADOS_CONDICION),
        ]);

        $estadoCondicion = $request->input('estado_condicion', $activo->estado_condicion ?: 'Excelente');

        if ($activo->estado_actual === 'No disponible' && in_array($estadoCondicion, ['En reparacion', 'Baja definitiva'], true)) {
            throw ValidationException::withMessages([
                'estado_condicion' => 'No puedes mandar a reparacion o baja un instrumento mientras tiene un prestamo activo.',
            ]);
        }

        $activo->nombre = trim($request->nombre);
        $activo->modelo = $request->filled('modelo') ? trim($request->modelo) : null;
        $activo->categoria = $request->categoria;
        $activo->valor_original = $request->filled('valor_original') ? $request->valor_original : null;
        $activo->limite_mantenimiento = $request->limite_mantenimiento;
        $activo->estado_condicion = $estadoCondicion;

        if ($activo->estado_actual !== 'No disponible') {
            $activo->estado_actual = match ($activo->estado_condicion) {
                'En reparacion' => 'Mantenimiento',
                'Baja definitiva' => 'Baja',
                default => $activo->estado_actual === 'Baja' ? 'Baja' : 'Disponible',
            };
        }

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
        $activo->estado_condicion = 'Baja definitiva';
        $activo->save();

        return redirect()->route('activos.index')->with('success', 'Instrumento dado de baja correctamente.');
    }
}
