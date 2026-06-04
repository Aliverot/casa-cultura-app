<?php

namespace App\States\Activo;

use App\Models\Activo;

interface ActivoEstado
{
    public function nombre(): string;

    public function registrarPrestamo(Activo $activo): void;
}
