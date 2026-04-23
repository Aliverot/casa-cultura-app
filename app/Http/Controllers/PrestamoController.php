<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\DetallePrestamo;
use App\Models\Activo;
use Carbon\Carbon;

class PrestamoController extends Controller
{
    // Función para ver préstamos activos y multas
    public function activos()
    {
        // Traemos detalles que no han sido devueltos O que tienen pagos pendientes
        $prestamosActivos = DetallePrestamo::with(['prestamo', 'activo'])
            ->whereNull('fecha_devolucion_real')
            ->get();

        $multasPendientes = DetallePrestamo::with(['prestamo', 'activo'])
            ->whereHas('prestamo', function($q) {
                $q->where('estado_pago', 'Pendiente');
            })->get();

        return view('prestamos_activos', compact('prestamosActivos', 'multasPendientes'));
    }

    public function store(Request $request)
    {
        // 1. EL ESCUDO: Validar que la fecha de devolución no sea en el pasado
        $request->validate([
            'fecha_devolucion_prevista' => 'required|date|after_or_equal:now',
        ], [
            'fecha_devolucion_prevista.after_or_equal' => 'Error: La fecha de devolución no puede ser en el pasado.'
        ]);

        // 2. Registro del préstamo principal
        $prestamo = new Prestamo();
        $prestamo->id_usuario = $request->id_usuario;
        $prestamo->fecha_salida = now();
        $prestamo->fecha_devolucion_prevista = $request->fecha_devolucion_prevista;
        $prestamo->nombre_solicitante = $request->nombre_solicitante;
        $prestamo->contacto_solicitante = $request->contacto_solicitante;
        $prestamo->condiciones_entrega = $request->condiciones_entrega;
        $prestamo->save();

        // 3. Registro del detalle (Aquí arreglamos el crash)
        $detalle = new DetallePrestamo();
        $detalle->id_prestamo = $prestamo->id_prestamo;
        $detalle->id_activo = $request->id_activo;
        $detalle->estado_salida = 'Buen Estado'; // <--- ESTO FALTABA PARA EVITAR EL ERROR
        $detalle->save();

        // 4. Actualizar estado del activo
        $activo = Activo::find($request->id_activo);
        $activo->estado_actual = 'En Prestamo';
        $activo->save();

        return redirect()->route('catalogo')->with('success', 'Préstamo registrado correctamente.');
    }

    public function devolver(Request $request, $id_detalle)
    {
        $detalle = DetallePrestamo::with('prestamo')->findOrFail($id_detalle);
        $activo = Activo::findOrFail($detalle->id_activo);
        $prestamo = $detalle->prestamo;

        // 1. Cálculo de horas de uso
        $fechaSalida = Carbon::parse($prestamo->fecha_salida);
        $horasUsadas = max(1, $fechaSalida->diffInHours(now()));
        $activo->horas_uso += $horasUsadas;

        // 2. Registrar condiciones y costos
        $prestamo->condiciones_devolucion = $request->condiciones_devolucion;
        $prestamo->costo_reparacion = $request->costo_reparacion ?? 0;

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

    public function liquidarPago($id_prestamo)
    {
        $prestamo = Prestamo::findOrFail($id_prestamo);
        $prestamo->estado_pago = 'Pagado';
        $prestamo->save();

        return redirect()->route('prestamos.activos')->with('success', 'El pago ha sido registrado. El folio queda liberado.');
    }
    public function historial()
    {
        // Traemos el "Log" completo de todo lo que ya regresó a la institución
        $historial = DetallePrestamo::with(['prestamo', 'activo'])
            ->whereNotNull('fecha_devolucion_real')
            ->orderBy('fecha_devolucion_real', 'desc')
            ->get();

        return view('historial', compact('historial'));
    }
}