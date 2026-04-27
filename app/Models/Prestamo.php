<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    protected $primaryKey = 'id_prestamo';

    protected $fillable = [
        'id_usuario',
        'fecha_salida',
        'fecha_devolucion_prevista',
        'nombre_solicitante',
        'contacto_solicitante',
        'condiciones_entrega',
        'condiciones_devolucion',
        'costo_reparacion',
        'estado_pago',
    ];

    protected $casts = [
        'fecha_salida' => 'datetime',
        'fecha_devolucion_prevista' => 'datetime',
        'costo_reparacion' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePrestamo::class, 'id_prestamo', 'id_prestamo');
    }
}
