<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePrestamo extends Model
{
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_prestamo', 
        'id_activo', 
        'estado_salida', 
        'estado_retorno', 
        'fecha_devolucion_real'
    ];

    // Relación Inversa: Este detalle pertenece a un Préstamo padre
    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_prestamo', 'id_prestamo');
    }

    // Relación Inversa: Este detalle pertenece a un Activo específico
    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }
}