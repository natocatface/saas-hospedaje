<?php

namespace App\Services\Facturacion\Drivers;

use App\Models\Factura;
use App\Models\FacturacionConfig;
use App\Services\Facturacion\Contracts\ComprobanteDriver;
use App\Services\Facturacion\ResultadoEmision;

/**
 * Driver nulo: no envía nada a SUNAT. Deja el comprobante en estado
 * "pendiente" para que pueda emitirse manualmente más tarde. Es el valor
 * por defecto y permite usar el sistema sin facturación electrónica activa.
 */
class NingunoDriver implements ComprobanteDriver
{
    public function __construct(protected FacturacionConfig $config)
    {
    }

    public function clave(): string
    {
        return 'ninguno';
    }

    public function probarConexion(): ResultadoEmision
    {
        return ResultadoEmision::error(
            'No hay driver de emisión configurado. Selecciona "SUNAT" para emitir comprobantes electrónicos.'
        );
    }

    public function emitir(Factura $factura): ResultadoEmision
    {
        return ResultadoEmision::ok(
            'Comprobante registrado como pendiente (sin driver de emisión).',
            'pendiente'
        );
    }

    public function anular(Factura $factura, string $motivo): ResultadoEmision
    {
        return ResultadoEmision::ok('Factura marcada como anulada localmente.', 'anulado');
    }
}
