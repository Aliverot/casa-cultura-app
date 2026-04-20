<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activo;

class ActivoController extends Controller
{
    // Esta función carga la página principal del catálogo
    public function index()
    {
        // Traemos todos los instrumentos de la base de datos
        $instrumentos = Activo::all();
        
        // Se los enviamos a una vista que llamaremos 'catalogo'
        return view('catalogo', compact('instrumentos'));
    }
}