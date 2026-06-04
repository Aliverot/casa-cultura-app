<?php

namespace App\Observers;

use App\Models\Mantenimiento;
use App\Services\AsistenteNotificacionesDiario;

class AsistenteNotificacionesMantenimientoObserver
{
    public function __construct(private readonly AsistenteNotificacionesDiario $asistente)
    {
    }

    public function created(Mantenimiento $mantenimiento): void
    {
        $this->asistente->mantenimientoRegistrado($mantenimiento);
    }
}
