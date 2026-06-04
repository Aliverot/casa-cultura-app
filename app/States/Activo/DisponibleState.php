<?php

namespace App\States\Activo;

use App\Models\Activo;

class DisponibleState implements ActivoEstado
{
    public function nombre(): string
    {
        return Activo::ESTADO_DISPONIBLE;
    }

    public function registrarPrestamo(Activo $activo): void
    {
        $activo->estado_actual = Activo::ESTADO_PRESTADO_LEGACY;
    }
}
