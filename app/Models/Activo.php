<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    protected $primaryKey = 'id_activo';

    protected $fillable = [
        'codigo_qr', 
        'nombre', 
        'categoria', 
        'estado_actual', 
        'horas_uso', 
        'limite_mantenimiento'
    ];

    // Relación: Un Activo tiene muchos Detalles de Préstamo
    public function detallesPrestamo()
    {
        return $this->hasMany(DetallePrestamo::class, 'id_activo', 'id_activo');
    }

    // Relación: Un Activo tiene muchos Mantenimientos
    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'id_activo', 'id_activo');
    }
}