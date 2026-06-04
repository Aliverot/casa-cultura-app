<?php

namespace App\States\Activo;

use App\Models\Activo;
use Illuminate\Validation\ValidationException;

class PrestadoState implements ActivoEstado
{
    public function nombre(): string
    {
        return Activo::ESTADO_PRESTADO;
    }

    public function registrarPrestamo(Activo $activo): void
    {
        throw ValidationException::withMessages([
            'id_activo' => 'El instrumento ya tiene un préstamo activo.',
        ]);
    }
}
