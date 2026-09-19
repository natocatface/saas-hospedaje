<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use BelongsToEmpresa;

    protected $table = 'productos';

    protected $fillable = [
        'empresa_id', 'nombre', 'categoria', 'precio', 'descripcion', 'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public const CATEGORIAS = ['minibar', 'restaurante', 'lavanderia', 'servicios', 'otros'];

    public function consumos()
    {
        return $this->hasMany(Consumo::class);
    }

    public function badgeColor(): string
    {
        return match ($this->categoria) {
            'minibar'     => 'info',
            'restaurante' => 'warning',
            'lavanderia'  => 'primary',
            'servicios'   => 'success',
            default       => 'secondary',
        };
    }
}
