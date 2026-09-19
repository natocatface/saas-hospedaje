<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

/**
 * Resumen diario de boletas (RC) declarado ante SUNAT.
 */
class FacturacionResumen extends Model
{
    use BelongsToEmpresa;

    protected $table = 'facturacion_resumenes';

    protected $fillable = [
        'empresa_id', 'identificador', 'correlativo', 'fecha_referencia',
        'ticket', 'estado', 'total_boletas', 'mensaje', 'cdr_path',
        'enviado_at', 'aceptado_at',
    ];

    protected $casts = [
        'fecha_referencia' => 'date',
        'enviado_at' => 'datetime',
        'aceptado_at' => 'datetime',
    ];

    public const ESTADOS = [
        'generado'   => 'Generado',
        'enviado'    => 'Enviado a SUNAT',
        'procesando' => 'En proceso',
        'aceptado'   => 'Aceptado',
        'rechazado'  => 'Rechazado',
    ];

    public function boletas()
    {
        return $this->hasMany(Factura::class, 'resumen_id');
    }

    public function estadoLabel(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function enProceso(): bool
    {
        return in_array($this->estado, ['enviado', 'procesando'], true);
    }
}
