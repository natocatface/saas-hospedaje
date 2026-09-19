<?php

namespace App\Services\Facturacion\Contracts;

use App\Models\Factura;
use App\Models\FacturacionConfig;
use App\Services\Facturacion\ResultadoEmision;

/**
 * Contrato de un motor de emisión de comprobantes electrónicos.
 *
 * Cualquier integración (SUNAT vía Greenter, un PSE/OSE tercero, un stub de
 * pruebas, etc.) implementa esta interfaz. El manager FacturacionElectronica
 * decide qué driver instanciar según la configuración de la empresa.
 */
interface ComprobanteDriver
{
    public function __construct(FacturacionConfig $config);

    /** Clave estable del driver (coincide con FacturacionConfig::DRIVERS). */
    public function clave(): string;

    /** Verifica que la configuración/credenciales permitan operar. */
    public function probarConexion(): ResultadoEmision;

    /** Emite (envía) el comprobante de una factura ante el destino. */
    public function emitir(Factura $factura): ResultadoEmision;

    /** Emite una nota de crédito que anula el comprobante de la factura. */
    public function anular(Factura $factura, string $motivo): ResultadoEmision;
}
