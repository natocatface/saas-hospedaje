<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'planes';

    protected $fillable = [
        'nombre', 'slug', 'precio_mensual', 'max_habitaciones', 'max_usuarios',
        'permite_reportes', 'permite_temporadas', 'descripcion', 'activo',
    ];

    protected $casts = [
        'precio_mensual' => 'decimal:2',
        'permite_reportes' => 'boolean',
        'permite_temporadas' => 'boolean',
        'activo' => 'boolean',
    ];

    public function empresas()
    {
        return $this->hasMany(Empresa::class);
    }

    public function limiteHabitaciones(): string
    {
        return $this->max_habitaciones ? (string) $this->max_habitaciones : 'Ilimitadas';
    }

    public function limiteUsuarios(): string
    {
        return $this->max_usuarios ? (string) $this->max_usuarios : 'Ilimitados';
    }
}
