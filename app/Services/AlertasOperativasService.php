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
    private const SEASON_MIN_PREVIOUS_LOANS = 2;
    private const SEASON_MIN_CURRENT_LOANS = 3;
    private const REPAIR_COST_PERCENT = 60;
    private const FAILURE_LIMIT = 3;
    private const FAILURE_PERIOD_DAYS = 365;

    public function precargarTemporadasBase(): int
    {
        if (! Schema::hasTable('temporadas_base')) {
            return 0;
        }

        $temporadas = $this->fechasBaseTemporada();

        foreach ($temporadas as $temporada) {
            $existe = DB::table('temporadas_base')
                ->where('nombre', $temporada['nombre'])
                ->exists();

            if ($existe) {
                DB::table('temporadas_base')
                    ->where('nombre', $temporada['nombre'])
                    ->update(array_merge($temporada, [
                        'activa' => true,
                        'updated_at' => now(),
                    ]));

                continue;
            }

            DB::table('temporadas_base')->insert(array_merge($temporada, [
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        return count($temporadas);
    }

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
            "El recurso {$activo->nombre} suma {$totalDanios} devoluciones con daño en los últimos ".self::DAMAGE_PERIOD_DAYS.' días.',
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
        $costoMantenimientosPreventivos = (float) Mantenimiento::whereIn('id_activo', $idsActivos)
            ->where('es_preventivo', true)
            ->sum('costo_servicio');
        $mantenimientosPreventivos = Mantenimiento::whereIn('id_activo', $idsActivos)
            ->where('es_preventivo', true)
            ->count();
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
            'Informe de baja y adquisición',
            "Se recomienda evaluar la compra de nuevas unidades de {$referencia} porque {$motivo}.",
            [
                'referencia_modelo' => $referencia,
                'fallas' => $totalFallas,
                'costo_reparaciones' => $costoTotal,
                'costo_incidentes' => $costoIncidentes,
                'costo_mantenimientos' => $costoMantenimientos,
                'costo_mantenimientos_preventivos' => $costoMantenimientosPreventivos,
                'mantenimientos_preventivos' => $mantenimientosPreventivos,
                'valor_original' => $valorOriginal,
                'porcentaje_limite' => self::REPAIR_COST_PERCENT,
                'periodo_dias' => self::FAILURE_PERIOD_DAYS,
            ]
        );
    }

    public function registrarTemporadaSiAplica(): Collection
    {
        $temporadaBase = $this->temporadaBaseVigente();
        $datosHistoricos = $this->datosIncrementoHistorico();
        $metricas = $this->metricasRecursosTemporada();
        $recursos = $this->recursosDesdeMetricas($metricas);

        if ($recursos->isEmpty()) {
            $this->cerrarAlertasTemporada();
            $this->cerrarAlertasIncrementoHistorico();

            return collect();
        }

        $this->registrarTemporadaBase($temporadaBase, $recursos);
        $this->registrarIncrementoHistorico($datosHistoricos, $recursos);

        return $recursos;
    }

    private function registrarTemporadaBase(?array $temporadaBase, Collection $recursos): void
    {
        if (! $temporadaBase) {
            $this->cerrarAlertasTemporada();

            return;
        }

        $motivo = "Se acerca {$temporadaBase['nombre']} ({$temporadaBase['rango']})";

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
                'titulo' => 'Preparación de temporada',
                'descripcion' => "{$motivo}. Se sugieren los ".self::SEASON_TOP_RESOURCES.' recursos más usados para mantenimiento preventivo.',
                'datos' => [
                    'temporada_base' => $temporadaBase,
                    'limite_recursos' => self::SEASON_TOP_RESOURCES,
                    'recursos' => $recursos->all(),
                ],
                'fecha_alerta' => now(),
            ]
        );
    }

    private function registrarIncrementoHistorico(array $datosHistoricos, Collection $recursos): void
    {
        if (! $datosHistoricos['aplica_por_incremento']) {
            $this->cerrarAlertasIncrementoHistorico();

            return;
        }

        AlertaOperativa::updateOrCreate(
            [
                'tipo' => 'Incremento Historico de Prestamos',
                'id_activo' => null,
                'estado' => 'Pendiente',
            ],
            [
                'titulo' => 'Incremento histórico de préstamos',
                'descripcion' => "La demanda reciente subió {$datosHistoricos['incremento_porcentaje']}% frente al periodo anterior. Se sugieren los ".self::SEASON_TOP_RESOURCES.' recursos más usados para mantenimiento preventivo.',
                'datos' => array_merge($datosHistoricos, [
                    'limite_recursos' => self::SEASON_TOP_RESOURCES,
                    'recursos' => $recursos->all(),
                ]),
                'fecha_alerta' => now(),
            ]
        );
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

    private function datosIncrementoHistorico(): array
    {
        $ahora = now();
        $inicioActual = $ahora->copy()->subDays(30);
        $inicioAnterior = $ahora->copy()->subDays(60);
        $prestamosActuales = Prestamo::where('fecha_salida', '>=', $inicioActual)->count();
        $prestamosAnteriores = Prestamo::where('fecha_salida', '>=', $inicioAnterior)
            ->where('fecha_salida', '<', $inicioActual)
            ->count();
        $diferencia = $prestamosActuales - $prestamosAnteriores;
        $incremento = $prestamosAnteriores > 0
            ? ($diferencia / $prestamosAnteriores) * 100
            : 0;
        $factor = $prestamosAnteriores > 0
            ? $prestamosActuales / $prestamosAnteriores
            : null;
        $cumpleMuestra = $prestamosAnteriores >= self::SEASON_MIN_PREVIOUS_LOANS
            && $prestamosActuales >= self::SEASON_MIN_CURRENT_LOANS;

        return [
            'incremento_porcentaje' => round($incremento, 2),
            'prestamos_actuales' => $prestamosActuales,
            'prestamos_anteriores' => $prestamosAnteriores,
            'diferencia_prestamos' => $diferencia,
            'factor_crecimiento' => $factor ? round($factor, 2) : null,
            'umbral_incremento' => self::SEASON_INCREASE_PERCENT,
            'minimo_prestamos_anteriores' => self::SEASON_MIN_PREVIOUS_LOANS,
            'minimo_prestamos_actuales' => self::SEASON_MIN_CURRENT_LOANS,
            'cumple_muestra_minima' => $cumpleMuestra,
            'aplica_por_incremento' => $cumpleMuestra && $incremento >= self::SEASON_INCREASE_PERCENT,
            'periodo_actual' => [
                'inicio' => $inicioActual->toDateString(),
                'fin' => $ahora->toDateString(),
            ],
            'periodo_anterior' => [
                'inicio' => $inicioAnterior->toDateString(),
                'fin' => $inicioActual->toDateString(),
            ],
            'formula' => '((prestamos_actuales - prestamos_anteriores) / prestamos_anteriores) * 100',
        ];
    }

    private function recursosDesdeMetricas(Collection $metricas): Collection
    {
        $activos = Activo::whereIn('id_activo', $metricas->pluck('id_activo'))->get()->keyBy('id_activo');

        return $metricas->map(function ($metrica) use ($activos) {
            $activo = $activos->get($metrica->id_activo);

            return $activo ? [
                'id_activo' => $activo->id_activo,
                'nombre' => $activo->nombre,
                'categoria' => $activo->categoria,
                'total_prestamos' => (int) $metrica->total_prestamos,
            ] : null;
        })->filter()->values();
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

    private function fechasBaseTemporada(): array
    {
        return [
            ['nombre' => 'Año Nuevo', 'fecha_inicio' => '01-01', 'fecha_fin' => '01-01', 'dias_anticipacion' => 30],
            ['nombre' => 'Día de la Constitución', 'fecha_inicio' => '02-05', 'fecha_fin' => '02-05', 'dias_anticipacion' => 30],
            ['nombre' => 'Natalicio de Benito Juárez', 'fecha_inicio' => '03-21', 'fecha_fin' => '03-21', 'dias_anticipacion' => 30],
            ['nombre' => 'Día del Trabajo', 'fecha_inicio' => '05-01', 'fecha_fin' => '05-01', 'dias_anticipacion' => 30],
            ['nombre' => 'Independencia de México', 'fecha_inicio' => '09-16', 'fecha_fin' => '09-16', 'dias_anticipacion' => 30],
            ['nombre' => 'Día de Muertos', 'fecha_inicio' => '11-01', 'fecha_fin' => '11-02', 'dias_anticipacion' => 30],
            ['nombre' => 'Revolución Mexicana', 'fecha_inicio' => '11-20', 'fecha_fin' => '11-20', 'dias_anticipacion' => 30],
            ['nombre' => 'Temporada decembrina', 'fecha_inicio' => '12-12', 'fecha_fin' => '01-06', 'dias_anticipacion' => 30],
        ];
    }

    private function cerrarAlertasTemporada(): void
    {
        AlertaOperativa::where('tipo', 'Preparacion de Temporada')
            ->where('estado', 'Pendiente')
            ->update(['estado' => 'Resuelta']);
    }

    private function cerrarAlertasIncrementoHistorico(): void
    {
        AlertaOperativa::where('tipo', 'Incremento Historico de Prestamos')
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
