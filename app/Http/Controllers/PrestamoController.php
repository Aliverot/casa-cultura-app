<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\DetallePrestamo;
use App\Models\Activo;
use App\Models\User;

class PrestamoController extends Controller
{
    // Función para guardar un nuevo préstamo en la base de datos
    public function store(Request $request)
    {
        // 1. Validar que los datos que envíe el formulario sean correctos
        $request->validate([
            'id_usuario' => 'required|exists:users,id_usuario',
            'id_activo' => 'required|exists:activos,id_activo',
            'fecha_devolucion_prevista' => 'required|date'
        ]);

        // 2. Buscar el instrumento en la base de datos
        $activo = Activo::findOrFail($request->id_activo);

        // 3. REGLA DE NEGOCIO: Verificar disponibilidad
        if ($activo->estado_actual !== 'Disponible') {
            // Escenario de fallo (Failure en tu diagrama)
            return back()->with('error', 'El artículo se encuentra ocupado o en mantenimiento.');
        }

        // 4. Escenario de éxito: Crear el Préstamo
        $prestamo = Prestamo::create([
            'id_usuario' => $request->id_usuario,
            'fecha_devolucion_prevista' => $request->fecha_devolucion_prevista,
        ]);

        // 5. Crear el Detalle del préstamo
        DetallePrestamo::create([
            'id_prestamo' => $prestamo->id_prestamo,
            'id_activo' => $activo->id_activo,
            'estado_salida' => 'Buen estado', // Más adelante esto vendrá del formulario
        ]);

        // 6. Actualizar el estado del instrumento para que nadie más lo pida
        $activo->estado_actual = 'En Prestamo';
        $activo->save();

        return redirect()->back()->with('success', 'Préstamo registrado correctamente.');
    }
}