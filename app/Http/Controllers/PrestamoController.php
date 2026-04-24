<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\DetallePrestamo;
use App\Models\Activo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PrestamoController extends Controller
{
    /**
     * 1. CAMBIO CLAVE: Renombrado de 'index' a 'activos'
     * Esto soluciona el error "Call to undefined method"
     */
    public function activos()
    {
        // Traemos detalles que no han sido devueltos
        $prestamosActivos = DetallePrestamo::with(['prestamo', 'activo'])
            ->whereNull('fecha_devolucion_real')
            ->get();

        // Traemos aquellos con pagos pendientes (multas)
        $multasPendientes = DetallePrestamo::with(['prestamo', 'activo'])
            ->whereHas('prestamo', function($q) {
                $q->where('estado_pago', 'Pendiente');
            })->get();

        // IMPORTANTE: Según tu imagen, el archivo está suelto en la carpeta views
        return view('prestamos_activos', compact('prestamosActivos', 'multasPendientes'));
    }

    /**
     * 2. Muestra el formulario de creación (Tarea: Botón de préstamo)
     * Busca el archivo dentro de la carpeta 'prestamos' que creaste
     */
    public function create()
    {
        $activos = Activo::where('estado_actual', 'Disponible')->get();
        return view('prestamos.create', compact('activos'));
    }

    /**
     * 3. Guarda el préstamo en la base de datos
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_devolucion_prevista' => 'required|date|after_or_equal:now',
            'id_activo' => 'required|exists:activos,id_activo',
            'nombre_solicitante' => 'required|string|max:255',
            'condiciones_entrega' => 'required|string',
        ]);

        // Registro en la tabla 'prestamos'
        $prestamo = new Prestamo();
        $prestamo->id_usuario = Auth::id();
        $prestamo->fecha_salida = now();
        $prestamo->fecha_devolucion_prevista = $request->fecha_devolucion_prevista;
        $prestamo->nombre_solicitante = $request->nombre_solicitante;
        $prestamo->contacto_solicitante = $request->contacto_solicitante;
        $prestamo->condiciones_entrega = $request->condiciones_entrega;
        $prestamo->save();

        // Registro en la tabla 'detalle_prestamos'
        $detalle = new DetallePrestamo();
        $detalle->id_prestamo = $prestamo->id_prestamo;
        $detalle->id_activo = $request->id_activo;
        $detalle->estado_salida = 'Buen Estado';
        $detalle->save();

        // Actualizar estado del instrumento
        $activo = Activo::find($request->id_activo);
        $activo->estado_actual = 'En Prestamo';
        $activo->save();

        return redirect()->route('activos.index')->with('success', '¡Préstamo registrado con éxito!');
    }

    /**
     * 4. Procesa la devolución y calcula horas de uso
     */
    public function devolver(Request $request, $id_detalle)
    {
        $detalle = DetallePrestamo::with('prestamo')->findOrFail($id_detalle);
        $activo = Activo::findOrFail($detalle->id_activo);
        $prestamo = $detalle->prestamo;

        // Cálculo de horas de uso acumuladas
        $fechaSalida = Carbon::parse($prestamo->fecha_salida);
        $horasUsadas = max(1, $fechaSalida->diffInHours(now()));
        $activo->horas_uso += $horasUsadas;

        $prestamo->condiciones_devolucion = $request->condiciones_devolucion;
        $prestamo->costo_reparacion = $request->costo_reparacion ?? 0;

        // Si hay costo, el instrumento va a mantenimiento
        if ($request->costo_reparacion > 0) {
            $prestamo->estado_pago = 'Pendiente';
            $activo->estado_actual = 'Mantenimiento';
        } else {
            $prestamo->estado_pago = 'Sin cargos';
            $activo->estado_actual = 'Disponible';
        }

        $prestamo->save();
        $activo->save();

        $detalle->fecha_devolucion_real = now();
        $detalle->estado_retorno = $request->costo_reparacion > 0 ? 'Dañado' : 'Buen Estado';
        $detalle->save();

        return redirect()->route('prestamos.activos')->with('success', 'Devolución procesada correctamente.');
    }

    /**
     * 5. Limpia multas pendientes
     */
    public function liquidarPago($id_prestamo)
    {
        $prestamo = Prestamo::findOrFail($id_prestamo);
        $prestamo->estado_pago = 'Pagado';
        $prestamo->save();

        return redirect()->route('prestamos.activos')->with('success', 'El pago ha sido registrado.');
    }

    /**
     * 6. Ver el historial completo
     */
    public function historial()
    {
        $historial = DetallePrestamo::with(['prestamo', 'activo'])
            ->whereNotNull('fecha_devolucion_real')
            ->orderBy('fecha_devolucion_real', 'desc')
            ->get();

        return view('historial', compact('historial'));
    }
}
