<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use BelongsToEmpresa;

    protected $table = 'facturas';

    protected $fillable = [
        'empresa_id', 'numero', 'reserva_id', 'fecha', 'subtotal', 'igv', 'total',
        'estado', 'metodo_pago',
        // Comprobante electrónico SUNAT
        'tipo_comprobante', 'serie', 'correlativo', 'moneda',
        'sunat_estado', 'sunat_ticket', 'sunat_hash', 'sunat_mensaje',
        'sunat_xml_path', 'sunat_cdr_path', 'sunat_enviado_at', 'resumen_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'subtotal' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
        'sunat_enviado_at' => 'datetime',
    ];

    /** Etiquetas legibles del estado ante SUNAT. */
    public const SUNAT_ESTADOS = [
        'no_emitido' => 'No emitido',
        'pendiente'  => 'Pendiente de envío',
        'enviado'    => 'Enviado a SUNAT',
        'aceptado'   => 'Aceptado por SUNAT',
        'observado'  => 'Aceptado con observaciones',
        'rechazado'  => 'Rechazado por SUNAT',
        'anulado'    => 'Anulado (nota de crédito)',
    ];

    public function sunatEstadoLabel(): string
    {
        return self::SUNAT_ESTADOS[$this->sunat_estado] ?? ($this->sunat_estado ?? 'No emitido');
    }

    public function comprobanteNumero(): ?string
    {
        return $this->serie && $this->correlativo ? "{$this->serie}-{$this->correlativo}" : null;
    }

    public function aceptadaPorSunat(): bool
    {
        return in_array($this->sunat_estado, ['aceptado', 'observado'], true);
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function resumen()
    {
        return $this->belongsTo(FacturacionResumen::class, 'resumen_id');
    }

    public function esBoleta(): bool
    {
        return $this->tipo_comprobante === 'boleta';
    }

    public function montoPagado(): float
    {
        return (float) $this->pagos()->sum('monto');
    }

    public function saldo(): float
    {
        return max(0, (float) $this->total - $this->montoPagado());
    }

    public function estaPagada(): bool
    {
        return $this->saldo() <= 0.001;
    }

    /** Recalcula el estado segun los pagos registrados. */
    public function actualizarEstadoPorPagos(): void
    {
        if ($this->estado === 'anulada') {
            return;
        }
        $this->estado = $this->estaPagada() ? 'pagada' : 'pendiente';
        $this->save();
    }
}
