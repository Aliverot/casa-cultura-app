<?php

namespace App\States\Activo;

use App\Models\Activo;
use Illuminate\Validation\ValidationException;

class EnReparacionState implements ActivoEstado
{
    public function nombre(): string
    {
        return Activo::ESTADO_EN_REPARACION;
    }

    public function registrarPrestamo(Activo $activo): void
    {
        throw ValidationException::withMessages([
            'id_activo' => 'El activo está en reparación y no puede prestarse.',
        ]);
    }
}
