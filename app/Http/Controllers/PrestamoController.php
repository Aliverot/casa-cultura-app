<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\DetallePrestamo;
use App\Models\Prestamo;
use App\Services\AlertasOperativasService;
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

        return view('prestamos_activos', compact('prestamosActivos'));
    }

    public function create(Request $request)
    {
        $activos = Activo::where('estado_actual', Activo::ESTADO_DISPONIBLE)
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
                'estado_salida' => Activo::ESTADO_PRESTADO,
            ]);

            $activo->registrarPrestamo();
            $activo->save();
        });

        return redirect()->route('activos.index')->with('success', 'Préstamo registrado con éxito.');
    }

    public function devolver(Request $request, $id_detalle)
    {
        $detalleValidacion = DetallePrestamo::findOrFail($id_detalle);
        $alertas = app(AlertasOperativasService::class);
        $requiereDatosDanio = $request->estado_equipo === 'Danado'
            && $alertas->requiereDatosDanioRecurrente($detalleValidacion);

        $request->validate([
            'condiciones_devolucion' => 'required|string',
            'estado_equipo' => 'required|in:Buen estado,Danado,Extraviado,Perdida total',
            'contexto_incidente' => [$requiereDatosDanio ? 'required' : 'nullable', 'string', 'max:2000'],
            'entorno_uso' => [$requiereDatosDanio ? 'required' : 'nullable', 'string', 'max:255'],
            'accesorios_proteccion' => [$requiereDatosDanio ? 'required' : 'nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $id_detalle, $alertas) {
            $detalle = DetallePrestamo::with('prestamo')->lockForUpdate()->findOrFail($id_detalle);

            if ($detalle->fecha_devolucion_real) {
                throw ValidationException::withMessages([
                    'devolucion' => 'Este préstamo ya fue procesado anteriormente.',
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
            $entregaATiempo = $fechaDevolucionReal->lessThanOrEqualTo(Carbon::parse($prestamo->fecha_devolucion_prevista));

            $activo->horas_uso = round(((float) $activo->horas_uso) + max(0, $horasUsadas), 2);
            $activo->estado_actual = match (true) {
                $instrumentoPerdidaTotal => Activo::ESTADO_BAJA,
                $instrumentoExtraviado => Activo::ESTADO_EXTRAVIADO,
                $instrumentoDanado => Activo::ESTADO_MANTENIMIENTO,
                default => Activo::ESTADO_DISPONIBLE,
            };
            $activo->estado_condicion = match (true) {
                $instrumentoPerdidaTotal || $instrumentoExtraviado => 'Baja definitiva',
                $instrumentoDanado => Activo::ESTADO_CONDICION_EN_REPARACION,
                default => 'Excelente',
            };

            $prestamo->condiciones_devolucion = trim($request->condiciones_devolucion);
            $prestamo->costo_reparacion = 0;
            $prestamo->estado_pago = 'Sin cargos';

            $detalle->fecha_devolucion_real = $fechaDevolucionReal;
            $detalle->estado_retorno = match (true) {
                $instrumentoPerdidaTotal => 'Perdida total',
                $instrumentoExtraviado => 'Extraviado',
                $instrumentoDanado => 'Danado',
                default => ($entregaATiempo ? 'En tiempo y forma' : 'Con atraso'),
            };
            $detalle->contexto_incidente = $instrumentoDanado && $request->filled('contexto_incidente') ? trim($request->contexto_incidente) : null;
            $detalle->entorno_uso = $instrumentoDanado && $request->filled('entorno_uso') ? trim($request->entorno_uso) : null;
            $detalle->accesorios_proteccion = $instrumentoDanado && $request->filled('accesorios_proteccion') ? trim($request->accesorios_proteccion) : null;

            $prestamo->save();
            $activo->save();
            $detalle->save();

            if ($instrumentoDanado) {
                $alertas->registrarDanioRecurrente($activo);
                $alertas->registrarReposicionSiAplica($activo);
            }
        });

        return redirect()->route('prestamos.activos')->with('success', 'Devolución procesada correctamente.');
    }

    public function historial(Request $request)
    {
        $filtros = $this->filtrosHistorial($request);
        $historial = $this->consultaHistorial($filtros)->get();
        $activosFiltro = Activo::orderBy('nombre')->get(['id_activo', 'nombre', 'codigo_qr']);
        $resumen = $this->resumenHistorial($historial);

        return view('historial', compact('historial', 'activosFiltro', 'filtros', 'resumen'));
    }

    public function exportarHistorialCsv(Request $request)
    {
        $filtros = $this->filtrosHistorial($request, true);
        $historial = $this->consultaHistorial($filtros)->get();
        $nombreArchivo = 'historial-prestamos-'.$filtros['desde'].'_'.$filtros['hasta'].'.csv';

        return response()->streamDownload(function () use ($historial) {
            $archivo = fopen('php://output', 'w');
            fwrite($archivo, "\xEF\xBB\xBF");
            fputcsv($archivo, [
                'Préstamo registrado',
                'Fecha límite de devolución',
                'Devolución recibida',
                'Instrumento',
                'Código QR',
                'Solicitante',
                'Contacto',
                'Resultado',
                'Tiempo de uso (horas)',
                'Condiciones de retorno',
                'Contexto',
                'Entorno',
                'Accesorios de protección',
            ]);

            foreach ($historial as $log) {
                fputcsv($archivo, [
                    $log->prestamo->fecha_salida?->format('d/m/Y H:i'),
                    $log->prestamo->fecha_devolucion_prevista?->format('d/m/Y H:i'),
                    $log->fecha_devolucion_real?->format('d/m/Y H:i'),
                    $log->activo?->nombre,
                    $log->activo?->codigo_qr,
                    $log->prestamo->nombre_solicitante,
                    $log->prestamo->contacto_solicitante,
                    $this->estadoRetornoLegible($log->estado_retorno),
                    number_format($this->horasDePrestamo($log), 2, '.', ''),
                    $log->prestamo->condiciones_devolucion ?: 'Sin condiciones registradas',
                    $log->contexto_incidente ?: '',
                    $log->entorno_uso ?: '',
                    $log->accesorios_proteccion ?: '',
                ]);
            }

            fclose($archivo);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filtrosHistorial(Request $request, bool $requierePeriodo = false): array
    {
        $reglasHasta = [$requierePeriodo ? 'required' : 'nullable', 'date'];

        if ($request->filled('desde')) {
            $reglasHasta[] = 'after_or_equal:desde';
        }

        $data = $request->validate([
            'desde' => [$requierePeriodo ? 'required' : 'nullable', 'date'],
            'hasta' => $reglasHasta,
            'id_activo' => ['nullable', 'integer', 'exists:activos,id_activo'],
            'solicitante' => ['nullable', 'string', 'max:255'],
        ]);

        return [
            'desde' => $data['desde'] ?? null,
            'hasta' => $data['hasta'] ?? null,
            'id_activo' => $data['id_activo'] ?? null,
            'solicitante' => isset($data['solicitante']) ? trim($data['solicitante']) : null,
        ];
    }

    private function consultaHistorial(array $filtros)
    {
        return DetallePrestamo::with(['prestamo', 'activo'])
            ->whereNotNull('fecha_devolucion_real')
            ->when($filtros['desde'], fn ($query, $desde) => $query->whereDate('fecha_devolucion_real', '>=', $desde))
            ->when($filtros['hasta'], fn ($query, $hasta) => $query->whereDate('fecha_devolucion_real', '<=', $hasta))
            ->when($filtros['id_activo'], fn ($query, $idActivo) => $query->where('id_activo', $idActivo))
            ->when($filtros['solicitante'], function ($query, $solicitante) {
                $query->whereHas('prestamo', function ($prestamoQuery) use ($solicitante) {
                    $prestamoQuery->whereRaw('LOWER(nombre_solicitante) LIKE ?', ['%'.strtolower($solicitante).'%']);
                });
            })
            ->orderByDesc('fecha_devolucion_real');
    }

    private function resumenHistorial($historial): array
    {
        return [
            'registros' => $historial->count(),
            'horas' => $historial->sum(fn ($log) => $this->horasDePrestamo($log)),
            'incidencias' => $historial->filter(fn ($log) => in_array($log->estado_retorno, ['Danado', 'Dañado', 'Extraviado', 'Perdida total'], true))->count(),
            'atrasos' => $historial->filter(fn ($log) => $log->estado_retorno === 'Con atraso')->count(),
        ];
    }

    private function horasDePrestamo(DetallePrestamo $log): float
    {
        if (! $log->prestamo->fecha_salida || ! $log->fecha_devolucion_real) {
            return 0.0;
        }

        return round($log->prestamo->fecha_salida->diffInSeconds($log->fecha_devolucion_real) / 3600, 2);
    }

    private function estadoRetornoLegible(?string $estado): string
    {
        return match ($estado) {
            'Danado', 'Dañado' => 'Dañado',
            'Perdida total' => 'Pérdida total',
            default => $estado ?: 'Sin registro',
        };
    }
}
