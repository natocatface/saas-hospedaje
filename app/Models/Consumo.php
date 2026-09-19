<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Consumo extends Model
{
    use BelongsToEmpresa;

    protected $table = 'consumos';

    protected $fillable = [
        'empresa_id', 'reserva_id', 'producto_id', 'descripcion',
        'cantidad', 'precio_unit', 'subtotal', 'fecha', 'user_id',
    ];

    protected $casts = [
        'precio_unit' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
