<?php

namespace App\Models;

use App\States\Activo\ActivoEstado;
use App\States\Activo\ActivoEstadoFactory;
use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    public const ESTADO_DISPONIBLE = 'Disponible';
    public const ESTADO_PRESTADO = 'Prestado';
    public const ESTADO_PRESTADO_LEGACY = 'No disponible';
    public const ESTADO_MANTENIMIENTO = 'Mantenimiento';
    public const ESTADO_EN_REPARACION = 'En reparacion';
    public const ESTADO_DANADO = 'Danado';
    public const ESTADO_BAJA = 'Baja';
    public const ESTADO_EXTRAVIADO = 'Extraviado';
    public const ESTADO_CONDICION_EN_REPARACION = 'En reparacion';

    public const ESTADOS_CONDICION = [
        'Excelente',
        'Funcional con detalles',
        self::ESTADO_CONDICION_EN_REPARACION,
        self::ESTADO_DANADO,
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

    public function estado(): ActivoEstado
    {
        return ActivoEstadoFactory::desdeActivo($this);
    }

    public function registrarPrestamo(): void
    {
        $this->estado()->registrarPrestamo($this);
    }

    public static function etiquetaEstadoCondicion(?string $estado): string
    {
        return match ($estado) {
            'En reparacion', 'En reparación' => 'En reparación',
            'Danado', 'Dañado' => 'Dañado',
            default => $estado ?: 'Excelente',
        };
    }

    public function estadoCondicionLegible(): string
    {
        return self::etiquetaEstadoCondicion($this->estado_condicion);
    }
}
