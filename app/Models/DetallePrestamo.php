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
        'contexto_incidente',
        'entorno_uso',
        'accesorios_proteccion',
        'fecha_devolucion_real',
    ];

    protected $casts = [
        'fecha_devolucion_real' => 'datetime',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'id_prestamo', 'id_prestamo');
    }

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }
}
