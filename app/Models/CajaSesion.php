<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class CajaSesion extends Model
{
    use BelongsToEmpresa;

    protected $table = 'caja_sesiones';

    protected $fillable = [
        'empresa_id', 'user_id', 'monto_inicial', 'monto_esperado',
        'monto_final', 'diferencia', 'estado', 'abierta_at', 'cerrada_at', 'notas',
    ];

    protected $casts = [
        'monto_inicial' => 'decimal:2',
        'monto_esperado' => 'decimal:2',
        'monto_final' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'abierta_at' => 'datetime',
        'cerrada_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    /** Total cobrado en efectivo durante la sesión. */
    public function efectivoCobrado(): float
    {
        return (float) $this->pagos()->where('metodo_pago', 'efectivo')->sum('monto');
    }

    /** Efectivo esperado en caja al cierre. */
    public function efectivoEsperado(): float
    {
        return (float) $this->monto_inicial + $this->efectivoCobrado();
    }
}
