<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use BelongsToEmpresa;

    protected $table = 'reservas';

    protected $fillable = [
        'empresa_id', 'codigo', 'huesped_id', 'habitacion_id', 'user_id',
        'fecha_checkin', 'fecha_checkout', 'noches', 'adultos', 'ninos',
        'tarifa_noche', 'total', 'estado', 'notas',
    ];

    protected $casts = [
        'fecha_checkin' => 'date',
        'fecha_checkout' => 'date',
        'tarifa_noche' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function huesped()
    {
        return $this->belongsTo(Huesped::class);
    }

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function factura()
    {
        return $this->hasOne(Factura::class);
    }

    public function consumos()
    {
        return $this->hasMany(Consumo::class);
    }

    /** Total de consumos extra (minibar, restaurante, etc.). */
    public function totalConsumos(): float
    {
        return (float) $this->consumos()->sum('subtotal');
    }

    /** Total de la cuenta = hospedaje + consumos. */
    public function totalCuenta(): float
    {
        return (float) $this->total + $this->totalConsumos();
    }

    public function badgeColor(): string
    {
        return match ($this->estado) {
            'pendiente'  => 'secondary',
            'confirmada' => 'primary',
            'checkin'    => 'success',
            'checkout'   => 'info',
            'cancelada'  => 'danger',
            default      => 'secondary',
        };
    }
}
