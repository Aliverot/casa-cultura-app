<?php

namespace App\States\Activo;

use App\Models\Activo;

class ActivoEstadoFactory
{
    public static function desdeActivo(Activo $activo): ActivoEstado
    {
        return match (true) {
            in_array($activo->estado_actual, [Activo::ESTADO_PRESTADO, Activo::ESTADO_PRESTADO_LEGACY], true) => new PrestadoState(),
            in_array($activo->estado_actual, [Activo::ESTADO_MANTENIMIENTO, Activo::ESTADO_EN_REPARACION], true)
                || in_array($activo->estado_condicion, [Activo::ESTADO_CONDICION_EN_REPARACION, 'En reparación'], true) => new EnReparacionState(),
            in_array($activo->estado_actual, [Activo::ESTADO_DANADO, 'Dañado'], true)
                || in_array($activo->estado_condicion, [Activo::ESTADO_DANADO, 'Dañado'], true) => new DanadoState(),
            default => new DisponibleState(),
        };
    }
}
