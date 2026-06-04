<?php

namespace App\States\Activo;

use App\Models\Activo;
use Illuminate\Validation\ValidationException;

class DanadoState implements ActivoEstado
{
    public function nombre(): string
    {
        return Activo::ESTADO_DANADO;
    }

    public function registrarPrestamo(Activo $activo): void
    {
        throw ValidationException::withMessages([
            'id_activo' => 'El activo esta danado y requiere atencion antes de prestarse.',
        ]);
    }
}
