<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activo;

class ActivoController extends Controller
{
    // Esta función carga la página principal del catálogo
    public function index(Request $request)
    {
        // Obtenemos lo que el usuario escribió en el buscador
        $buscar = $request->input('buscar');

        // Buscamos por nombre O por código QR
        $instrumentos = Activo::when($buscar, function ($query) use ($buscar) {
            return $query->where('nombre', 'ilike', "%{$buscar}%")
                         ->orWhere('codigo_qr', 'ilike', "%{$buscar}%");
        })->get();

        return view('catalogo', compact('instrumentos'));
    }
    // Mostrar el formulario
    public function create()
    {
        return view('instrumentos_nuevos');
    }

    // Guardar los datos en PostgreSQL
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'limite_mantenimiento' => 'required|numeric'
        ]);

        Activo::create([
            'nombre' => $request->nombre,
            'categoria' => $request->categoria,
            'limite_mantenimiento' => $request->limite_mantenimiento,
            'estado_actual' => 'Disponible',
            'horas_uso' => 0,
            // Generamos un código único temporal de 6 números
            'codigo_qr' => 'QR-' . rand(100000, 999999) 
        ]);

        return redirect()->route('catalogo')->with('success', 'Instrumento agregado correctamente.');
    }

}