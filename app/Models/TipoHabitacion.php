<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class TipoHabitacion extends Model
{
    use BelongsToEmpresa;

    protected $table = 'tipo_habitaciones';

    protected $fillable = [
        'empresa_id', 'nombre', 'descripcion', 'capacidad', 'precio_base', 'activo',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class, 'tipo_habitacion_id');
    }
}
