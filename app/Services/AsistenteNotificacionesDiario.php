<?php

namespace App\Services;

use App\Models\Activo;
use App\Models\Mantenimiento;
use Illuminate\Support\Collection;

class AsistenteNotificacionesDiario
{
    public function __construct(private readonly AlertasOperativasService $gestorAlertas)
    {
    }

    public function activoActualizado(Activo $activo): void
    {
        if ($this->requiereAtencionPorDanio($activo)) {
            $this->gestorAlertas->registrarAtencionDanio($activo);

            return;
        }

        $this->gestorAlertas->resolverAtencionDanio($activo);
    }

    public function mantenimientoRegistrado(Mantenimiento $mantenimiento): void
    {
        $activo = $mantenimiento->activo()->first();

        if (! $activo) {
            return;
        }

        $this->gestorAlertas->resolverAtencionDanio($activo);
        $this->gestorAlertas->registrarReposicionSiAplica($activo);
    }

    public function revisarAgendaDelDia(): Collection
    {
        return $this->gestorAlertas->registrarAgendaDiaria();
    }

    private function requiereAtencionPorDanio(Activo $activo): bool
    {
        return in_array($activo->estado_actual, [
            Activo::ESTADO_DANADO,
            Activo::ESTADO_MANTENIMIENTO,
            Activo::ESTADO_EN_REPARACION,
        ], true)
            || in_array($activo->estado_condicion, [
                Activo::ESTADO_DANADO,
                Activo::ESTADO_CONDICION_EN_REPARACION,
            ], true);
    }
}
