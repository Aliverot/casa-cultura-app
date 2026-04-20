<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    protected $primaryKey = 'id_prestamo';

    protected $fillable = [
        'id_usuario', 
        'fecha_salida', 
        'fecha_devolucion_prevista'
    ];

    // Relación Inversa: Un Préstamo pertenece a un Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    // Relación: Un Préstamo tiene muchos Detalles (los instrumentos que se llevaron)
    public function detalles()
    {
        return $this->hasMany(DetallePrestamo::class, 'id_prestamo', 'id_prestamo');
    }
}