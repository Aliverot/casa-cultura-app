<?php

namespace App\Services;

use App\Models\Activo;
use App\Models\AlertaOperativa;
use App\Models\DetallePrestamo;
use App\Models\Mantenimiento;
use App\Models\Prestamo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlertasOperativasService
{
    private const DAMAGE_LIMIT = 2;
    private const DAMAGE_PERIOD_DAYS = 90;
    private const SEASON_INCREASE_PERCENT = 25;
    private const SEASON_TOP_RESOURCES = 5;
    private const REPAIR_COST_PERCENT = 60;
    private const FAILURE_LIMIT = 3;
    private const FAILURE_PERIOD_DAYS = 365;

    public function requiereDatosDanioRecurrente(DetallePrestamo $detalle): bool
    {
        return $this->contarDaniosRecientes($detalle->id_activo, $detalle->id_detalle) >= self::DAMAGE_LIMIT;
    }

    public function registrarDanioRecurrente(Activo $activo): ?AlertaOperativa
    {
        $totalDanios = $this->contarDaniosRecientes($activo->id_activo);

        if ($totalDanios <= self::DAMAGE_LIMIT) {
            return null;
        }

        return $this->guardarAlerta(
            'Fragilidad/Mal Uso',
            $activo,
            'Alerta de Fragilidad/Mal Uso',
            "El recurso {$activo->nombre} suma {$totalDanios} devoluciones con dano en los ultimos ".self::DAMAGE_PERIOD_DAYS.' dias.',
            [
                'danios_recientes' => $totalDanios,
                'limite_danios' => self::DAMAGE_LIMIT,
                'periodo_dias' => self::DAMAGE_PERIOD_DAYS,
            ]
        );
    }

    public function registrarReposicionSiAplica(Activo $activo): ?AlertaOperativa
    {
        $desde = now()->subDays(self::FAILURE_PERIOD_DAYS);
        $referencia = $activo->modelo ?: $activo->nombre;
        $idsActivos = Activo::where(function ($query) use ($referencia) {
            $query->where('modelo', $referencia)
                ->orWhere(function ($subQuery) use ($referencia) {
                    $subQuery->whereNull('modelo')->where('nombre', $referencia);
                });
        })->pluck('id_activo');

        $fallas = DetallePrestamo::query()
            ->join('prestamos', 'detalle_prestamos.id_prestamo', '=', 'prestamos.id_prestamo')
            ->whereIn('detalle_prestamos.id_activo', $idsActivos)
            ->where('detalle_prestamos.estado_retorno', 'Danado')
            ->where('detalle_prestamos.fecha_devolucion_real', '>=', $desde);

        $totalFallas = (clone $fallas)->count();
        $costoIncidentes = (float) (clone $fallas)->sum('prestamos.costo_reparacion');
        $costoMantenimientos = (float) Mantenimiento::whereIn('id_activo', $idsActivos)->sum('costo_servicio');
        $costoTotal = $costoIncidentes + $costoMantenimientos;
        $valorOriginal = (float) Activo::whereIn('id_activo', $idsActivos)->avg('valor_original');
        $rebasaCosto = $valorOriginal > 0 && $costoTotal >= ($valorOriginal * self::REPAIR_COST_PERCENT / 100);
        $rebasaFrecuencia = $totalFallas >= self::FAILURE_LIMIT;

        if (! $rebasaCosto && ! $rebasaFrecuencia) {
            return null;
        }

        $motivo = $rebasaCosto
            ? 'el costo acumulado de reparaciones supera el '.self::REPAIR_COST_PERCENT.'% de su valor original'
            : 'la frecuencia de fallas afecta la disponibilidad operativa';

        return $this->guardarAlerta(
            'Baja y Adquisicion',
            $activo,
            'Informe de Baja y Adquisicion',
            "Se recomienda evaluar la compra de nuevas unidades de {$referencia} porque {$motivo}.",
            [
                'referencia_modelo' => $referencia,
                'fallas' => $totalFallas,
                'costo_reparaciones' => $costoTotal,
                'costo_incidentes' => $costoIncidentes,
                'costo_mantenimientos' => $costoMantenimientos,
                'valor_original' => $valorOriginal,
                'porcentaje_limite' => self::REPAIR_COST_PERCENT,
                'periodo_dias' => self::FAILURE_PERIOD_DAYS,
            ]
        );
    }

    public function registrarTemporadaSiAplica(): Collection
    {
        $temporadaBase = $this->temporadaBaseVigente();
        $inicioActual = now()->subDays(30);
        $inicioAnterior = now()->subDays(60);
        $prestamosActuales = Prestamo::where('fecha_salida', '>=', $inicioActual)->count();
        $prestamosAnteriores = Prestamo::where('fecha_salida', '>=', $inicioAnterior)
            ->where('fecha_salida', '<', $inicioActual)
            ->count();

        if ($prestamosAnteriores === 0 && ! $temporadaBase) {
            $this->cerrarAlertasTemporada();

            return collect();
        }

        $incremento = $prestamosAnteriores > 0
            ? (($prestamosActuales - $prestamosAnteriores) / $prestamosAnteriores) * 100
            : 0;

        if ($incremento < self::SEASON_INCREASE_PERCENT && ! $temporadaBase) {
            $this->cerrarAlertasTemporada();

            return collect();
        }

        $metricas = $this->metricasRecursosTemporada();
        $activos = Activo::whereIn('id_activo', $metricas->pluck('id_activo'))->get()->keyBy('id_activo');
        $recursos = $metricas->map(function ($metrica) use ($activos) {
            $activo = $activos->get($metrica->id_activo);

            return $activo ? [
                'id_activo' => $activo->id_activo,
                'nombre' => $activo->nombre,
                'categoria' => $activo->categoria,
                'total_prestamos' => (int) $metrica->total_prestamos,
            ] : null;
        })->filter()->values();

        if ($recursos->isEmpty()) {
            $this->cerrarAlertasTemporada();

            return collect();
        }

        $motivo = $temporadaBase
            ? "Se acerca {$temporadaBase['nombre']} ({$temporadaBase['rango']})"
            : 'La demanda reciente subio '.round($incremento, 2).'%';

        AlertaOperativa::where('tipo', 'Preparacion de Temporada')
            ->where('estado', 'Pendiente')
            ->whereNotNull('id_activo')
            ->update(['estado' => 'Resuelta']);

        AlertaOperativa::updateOrCreate(
            [
                'tipo' => 'Preparacion de Temporada',
                'id_activo' => null,
                'estado' => 'Pendiente',
            ],
            [
                'titulo' => 'Preparacion de Temporada',
                'descripcion' => "{$motivo}. Se sugieren los ".self::SEASON_TOP_RESOURCES.' recursos mas usados para mantenimiento preventivo.',
                'datos' => [
                    'incremento_porcentaje' => round($incremento, 2),
                    'prestamos_actuales' => $prestamosActuales,
                    'prestamos_anteriores' => $prestamosAnteriores,
                    'temporada_base' => $temporadaBase,
                    'limite_recursos' => self::SEASON_TOP_RESOURCES,
                    'recursos' => $recursos->all(),
                ],
                'fecha_alerta' => now(),
            ]
        );

        return $recursos;
    }

    public function alertasPendientes(int $limite = 6): Collection
    {
        return AlertaOperativa::with('activo')
            ->where('estado', 'Pendiente')
            ->orderByDesc('fecha_alerta')
            ->limit($limite)
            ->get();
    }

    private function contarDaniosRecientes(int $idActivo, ?int $exceptoDetalle = null): int
    {
        return DetallePrestamo::query()
            ->where('id_activo', $idActivo)
            ->where('estado_retorno', 'Danado')
            ->where('fecha_devolucion_real', '>=', now()->subDays(self::DAMAGE_PERIOD_DAYS))
            ->when($exceptoDetalle, fn ($query) => $query->where('id_detalle', '<>', $exceptoDetalle))
            ->count();
    }

    private function metricasRecursosTemporada(): Collection
    {
        $metricas = DetallePrestamo::query()
            ->select('id_activo')
            ->selectRaw('COUNT(*) as total_prestamos')
            ->groupBy('id_activo')
            ->orderByDesc('total_prestamos')
            ->limit(self::SEASON_TOP_RESOURCES)
            ->get();

        if ($metricas->isNotEmpty()) {
            return $metricas;
        }

        return Activo::query()
            ->select('id_activo')
            ->selectRaw('0 as total_prestamos')
            ->orderBy('nombre')
            ->limit(self::SEASON_TOP_RESOURCES)
            ->get();
    }

    private function temporadaBaseVigente(): ?array
    {
        if (! Schema::hasTable('temporadas_base')) {
            return null;
        }

        $hoy = now()->startOfDay();
        $temporadas = DB::table('temporadas_base')->where('activa', true)->get();

        foreach ($temporadas as $temporada) {
            foreach ([$hoy->year - 1, $hoy->year, $hoy->year + 1] as $year) {
                $inicio = Carbon::createFromFormat('Y-m-d', $year.'-'.$temporada->fecha_inicio)->startOfDay();
                $fin = Carbon::createFromFormat('Y-m-d', $year.'-'.$temporada->fecha_fin)->endOfDay();

                if ($fin->lt($inicio)) {
                    $fin->addYear();
                }

                $alertaDesde = $inicio->copy()->subDays((int) $temporada->dias_anticipacion);

                if ($hoy->betweenIncluded($alertaDesde, $fin)) {
                    return [
                        'nombre' => $temporada->nombre,
                        'rango' => $inicio->format('d/m').' - '.$fin->format('d/m'),
                        'dias_anticipacion' => (int) $temporada->dias_anticipacion,
                    ];
                }
            }
        }

        return null;
    }

    private function cerrarAlertasTemporada(): void
    {
        AlertaOperativa::where('tipo', 'Preparacion de Temporada')
            ->where('estado', 'Pendiente')
            ->update(['estado' => 'Resuelta']);
    }

    private function guardarAlerta(string $tipo, Activo $activo, string $titulo, string $descripcion, array $datos): AlertaOperativa
    {
        return AlertaOperativa::updateOrCreate(
            [
                'tipo' => $tipo,
                'id_activo' => $activo->id_activo,
                'estado' => 'Pendiente',
            ],
            [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'datos' => $datos,
                'fecha_alerta' => now(),
            ]
        );
    }
}
