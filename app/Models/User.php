<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // 1. Le decimos cuál es su Llave Primaria real
    protected $primaryKey = 'id_usuario';

    // 2. Los campos que se pueden llenar desde un formulario
    protected $fillable = [
        'name',
        'email',
        'password',
        'identificador',
        'rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 3. Relación: Un Usuario tiene muchos Préstamos
    public function prestamos()
    {
        return $this->hasMany(Prestamo::class, 'id_usuario', 'id_usuario');
    }
}