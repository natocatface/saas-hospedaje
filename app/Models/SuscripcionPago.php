<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class SuscripcionPago extends Model
{
    use BelongsToEmpresa;

    protected $table = 'suscripcion_pagos';

    protected $fillable = [
        'empresa_id', 'plan_id', 'plan_nombre', 'monto',
        'periodo_inicio', 'periodo_fin', 'fecha_pago', 'metodo', 'estado',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'periodo_inicio' => 'date',
        'periodo_fin' => 'date',
        'fecha_pago' => 'date',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
