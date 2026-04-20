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
    // Función para MOSTRAR la pantalla de Préstamos Activos
    public function activos()
    {
        // Traemos todos los detalles que NO tienen fecha de devolución (siguen prestados)
        // y cargamos la información del activo y del usuario que lo pidió
        $prestamosActivos = DetallePrestamo::with(['prestamo.usuario', 'activo'])
                            ->whereNull('fecha_devolucion_real')
                            ->get();

        return view('prestamos_activos', compact('prestamosActivos'));
    }

    public function devolver(Request $request, $id_detalle)
    {
        // 1. Encontrar el detalle específico
        $detalle = DetallePrestamo::findOrFail($id_detalle);

        // 2. Registrar cómo lo regresan y la fecha/hora exacta
        $detalle->estado_retorno = $request->estado_retorno;
        $detalle->fecha_devolucion_real = now(); 
        $detalle->save();

        // 3. Lógica inteligente para el estado del activo
        $activo = Activo::findOrFail($detalle->id_activo);

        if ($request->estado_retorno === 'Dañado') {
            // Si está dañado, el sistema lo marca como "Mantenimiento" y NO aparecerá como disponible
            $activo->estado_actual = 'Mantenimiento';
        } else {
            // Si está bien o con desgaste menor, lo regresamos a "Disponible"
            $activo->estado_actual = 'Disponible';
        }
        
        $activo->save();

        return redirect()->back()->with('success', 'Devolución procesada. El sistema ha ajustado el estado del activo.');
    }
}