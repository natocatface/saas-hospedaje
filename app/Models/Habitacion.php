<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Habitacion extends Model
{
    use BelongsToEmpresa;

    protected $table = 'habitaciones';

    protected $fillable = [
        'empresa_id', 'numero', 'tipo_habitacion_id', 'piso', 'precio_noche',
        'capacidad', 'estado', 'descripcion',
    ];

    protected $casts = [
        'precio_noche' => 'decimal:2',
    ];

    public const ESTADOS = ['disponible', 'ocupada', 'mantenimiento', 'limpieza'];

    public function tipo()
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipo_habitacion_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function badgeColor(): string
    {
        return match ($this->estado) {
            'disponible'    => 'success',
            'ocupada'       => 'danger',
            'mantenimiento' => 'warning',
            'limpieza'      => 'info',
            default         => 'secondary',
        };
    }
}
