<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Huesped extends Model
{
    use BelongsToEmpresa;

    protected $table = 'huespedes';

    protected $fillable = [
        'empresa_id', 'nombres', 'apellidos', 'documento_tipo', 'documento_numero',
        'telefono', 'email', 'nacionalidad', 'direccion',
        'fecha_nacimiento', 'notas',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }
}
