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
        'costo_servicio',
        'es_preventivo',
        'observaciones',
    ];

    protected $casts = [
        'fecha_servicio' => 'datetime',
        'costo_servicio' => 'decimal:2',
        'es_preventivo' => 'boolean',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }
}
