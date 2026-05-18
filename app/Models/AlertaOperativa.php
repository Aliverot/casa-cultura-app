<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertaOperativa extends Model
{
    protected $table = 'alertas_operativas';

    protected $primaryKey = 'id_alerta';

    protected $fillable = [
        'id_activo',
        'tipo',
        'titulo',
        'descripcion',
        'datos',
        'estado',
        'fecha_alerta',
    ];

    protected $casts = [
        'datos' => 'array',
        'fecha_alerta' => 'datetime',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }
}
