<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\DetallePrestamo;
use App\Models\Prestamo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrestamoController extends Controller
{
    public function activos()
    {
        $prestamosActivos = DetallePrestamo::with(['prestamo', 'activo'])
            ->whereNull('fecha_devolucion_real')
            ->orderByDesc('created_at')
            ->get();

        $multasPendientes = DetallePrestamo::with(['prestamo', 'activo'])
            ->whereNotNull('fecha_devolucion_real')
            ->whereHas('prestamo', function ($query) {
                $query->where('estado_pago', 'Pendiente');
            })
            ->orderByDesc('fecha_devolucion_real')
            ->get();

        return view('prestamos_activos', compact('prestamosActivos', 'multasPendientes'));
    }

    public function create(Request $request)
    {
        $activos = Activo::where('estado_actual', 'Disponible')
            ->orderBy('nombre')
            ->get();
        $activoSeleccionado = $request->integer('id_activo');

        return view('prestamos.create', compact('activos', 'activoSeleccionado'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_devolucion_prevista' => 'required|date',
            'id_activo' => 'required|exists:activos,id_activo',
            'nombre_solicitante' => 'required|string|max:255',
            'contacto_solicitante' => 'required|string|max:255',
            'condiciones_entrega' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $activo = Activo::lockForUpdate()->findOrFail($request->id_activo);

            if ($activo->estado_actual !== 'Disponible') {
                throw ValidationException::withMessages([
                    'id_activo' => 'El instrumento ya no esta disponible para prestamo.',
                ]);
            }

            $fechaSalida = now();
            $fechaDevolucionPrevista = Carbon::parse($request->fecha_devolucion_prevista);

            if ($fechaDevolucionPrevista->lt($fechaSalida->copy()->startOfMinute())) {
                throw ValidationException::withMessages([
                    'fecha_devolucion_prevista' => 'La fecha prevista debe ser igual o posterior a la hora actual.',
                ]);
            }

            $prestamo = Prestamo::create([
                'id_usuario' => Auth::id(),
                'fecha_salida' => $fechaSalida,
                'fecha_devolucion_prevista' => $fechaDevolucionPrevista,
                'nombre_solicitante' => $request->nombre_solicitante,
                'contacto_solicitante' => $request->contacto_solicitante,
                'condiciones_entrega' => trim($request->condiciones_entrega),
            ]);

            DetallePrestamo::create([
                'id_prestamo' => $prestamo->id_prestamo,
                'id_activo' => $activo->id_activo,
                'estado_salida' => 'Prestado',
            ]);

            $activo->estado_actual = 'No disponible';
            $activo->save();
        });

        return redirect()->route('activos.index')->with('success', 'Prestamo registrado con exito.');
    }

    public function devolver(Request $request, $id_detalle)
    {
        $request->validate([
            'condiciones_devolucion' => 'required|string',
            'estado_equipo' => 'required|in:Buen estado,Danado,Extraviado,Perdida total',
            'costo_reparacion' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $id_detalle) {
            $detalle = DetallePrestamo::with('prestamo')->lockForUpdate()->findOrFail($id_detalle);

            if ($detalle->fecha_devolucion_real) {
                throw ValidationException::withMessages([
                    'devolucion' => 'Este prestamo ya fue procesado anteriormente.',
                ]);
            }

            $activo = Activo::lockForUpdate()->findOrFail($detalle->id_activo);
            $prestamo = $detalle->prestamo;
            $fechaSalida = Carbon::parse($prestamo->fecha_salida);
            $fechaDevolucionReal = now();
            $horasUsadas = round($fechaSalida->diffInSeconds($fechaDevolucionReal) / 3600, 2);
            $instrumentoDanado = $request->estado_equipo === 'Danado';
            $instrumentoExtraviado = $request->estado_equipo === 'Extraviado';
            $instrumentoPerdidaTotal = $request->estado_equipo === 'Perdida total';
            $costoReparacion = $instrumentoDanado ? (float) ($request->costo_reparacion ?? 0) : 0.0;
            $costoReposicion = ($instrumentoExtraviado || $instrumentoPerdidaTotal)
                ? (float) ($request->costo_reparacion ?? 0)
                : 0.0;
            $entregaATiempo = $fechaDevolucionReal->lessThanOrEqualTo(Carbon::parse($prestamo->fecha_devolucion_prevista));

            $activo->horas_uso = round(((float) $activo->horas_uso) + max(0, $horasUsadas), 2);
            $activo->estado_actual = match (true) {
                $instrumentoPerdidaTotal => 'Baja',
                $instrumentoExtraviado => 'Extraviado',
                $instrumentoDanado => 'Mantenimiento',
                default => 'Disponible',
            };

            $prestamo->condiciones_devolucion = trim($request->condiciones_devolucion);
            $prestamo->costo_reparacion = $instrumentoDanado ? $costoReparacion : $costoReposicion;
            $prestamo->estado_pago = $prestamo->costo_reparacion > 0 ? 'Pendiente' : 'Sin cargos';

            $detalle->fecha_devolucion_real = $fechaDevolucionReal;
            $detalle->estado_retorno = match (true) {
                $instrumentoPerdidaTotal => 'Perdida total',
                $instrumentoExtraviado => 'Extraviado',
                $instrumentoDanado => 'Danado',
                default => ($entregaATiempo ? 'En tiempo y forma' : 'Con atraso'),
            };

            $prestamo->save();
            $activo->save();
            $detalle->save();
        });

        return redirect()->route('prestamos.activos')->with('success', 'Devolucion procesada correctamente.');
    }

    public function liquidarPago($id_prestamo)
    {
        $prestamo = Prestamo::findOrFail($id_prestamo);

        if ($prestamo->estado_pago !== 'Pendiente') {
            return redirect()->route('prestamos.activos')->with('success', 'Ese cargo ya no esta pendiente.');
        }

        $prestamo->estado_pago = 'Pagado';
        $prestamo->save();

        return redirect()->route('prestamos.activos')->with('success', 'El pago ha sido registrado.');
    }

    public function historial()
    {
        $historial = DetallePrestamo::with(['prestamo', 'activo'])
            ->whereNotNull('fecha_devolucion_real')
            ->orderByDesc('fecha_devolucion_real')
            ->get();

        return view('historial', compact('historial'));
    }
}
