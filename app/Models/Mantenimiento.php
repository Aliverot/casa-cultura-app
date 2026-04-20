<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    protected $primaryKey = 'id_mantenimiento';

    protected $fillable = [
        'id_activo', 
        'fecha_servicio', 
        'tipo', 
        'observaciones'
    ];

    // Relación Inversa: Un Mantenimiento se le hace a un Activo
    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }
}