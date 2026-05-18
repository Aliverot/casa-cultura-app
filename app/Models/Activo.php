<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    public const ESTADOS_CONDICION = [
        'Excelente',
        'Funcional con detalles',
        'En reparacion',
        'Baja definitiva',
    ];

    protected $primaryKey = 'id_activo';

    protected $fillable = [
        'codigo_qr',
        'nombre',
        'modelo',
        'categoria',
        'valor_original',
        'estado_actual',
        'estado_condicion',
        'horas_uso',
        'limite_mantenimiento',
    ];

    protected $casts = [
        'valor_original' => 'decimal:2',
        'horas_uso' => 'float',
        'limite_mantenimiento' => 'float',
    ];

    public function detallesPrestamo()
    {
        return $this->hasMany(DetallePrestamo::class, 'id_activo', 'id_activo');
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'id_activo', 'id_activo');
    }

    public function alertasOperativas()
    {
        return $this->hasMany(AlertaOperativa::class, 'id_activo', 'id_activo');
    }
}
