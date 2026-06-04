<?php

namespace App\Observers;

use App\Models\Activo;
use App\Services\AsistenteNotificacionesDiario;

class AsistenteNotificacionesActivoObserver
{
    public function __construct(private readonly AsistenteNotificacionesDiario $asistente)
    {
    }

    public function created(Activo $activo): void
    {
        $this->asistente->activoActualizado($activo);
    }

    public function updated(Activo $activo): void
    {
        if ($activo->wasChanged(['estado_actual', 'estado_condicion', 'horas_uso'])) {
            $this->asistente->activoActualizado($activo);
        }
    }
}
