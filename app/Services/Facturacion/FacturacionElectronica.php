<?php

namespace App\Services\Facturacion;

use App\Models\Factura;
use App\Models\FacturacionConfig;
use App\Services\Facturacion\Contracts\ComprobanteDriver;
use App\Services\Facturacion\Drivers\NingunoDriver;
use App\Services\Facturacion\Drivers\SunatDriver;

/**
 * Fachada / manager de la Facturación Electrónica.
 *
 * Resuelve la configuración de la empresa, instancia el driver adecuado y
 * expone operaciones de alto nivel (probar, emitir, anular) que además
 * persisten el resultado en la factura. El resto del sistema solo necesita:
 *
 *   FacturacionElectronica::actual()->emitir($factura);
 */
class FacturacionElectronica
{
    /** Mapa de drivers disponibles. */
    protected const DRIVERS = [
        'ninguno' => NingunoDriver::class,
        'sunat'   => SunatDriver::class,
    ];

    public function __construct(public FacturacionConfig $config)
    {
    }

    /** Instancia el manager con la configuración de la empresa actual. */
    public static function actual(): self
    {
        return new self(FacturacionConfig::actual());
    }

    /** Driver de emisión según la configuración. */
    public function driver(): ComprobanteDriver
    {
        $clase = self::DRIVERS[$this->config->driver] ?? NingunoDriver::class;

        return new $clase($this->config);
    }

    /** ¿Está la facturación electrónica activa y con driver real? */
    public function activa(): bool
    {
        return $this->config->habilitado && $this->config->driver !== 'ninguno';
    }

    /** Prueba la conexión/credenciales con el destino configurado. */
    public function probarConexion(): ResultadoEmision
    {
        return $this->driver()->probarConexion();
    }

    /** Emite el comprobante de una factura y guarda el resultado. */
    public function emitir(Factura $factura): ResultadoEmision
    {
        if (! $this->config->habilitado) {
            return ResultadoEmision::error('La facturación electrónica está deshabilitada.');
        }

        // Determina tipo de comprobante si no está definido: RUC => factura, si no boleta.
        if (empty($factura->tipo_comprobante)) {
            $doc = optional($factura->reserva?->huesped)->documento_tipo;
            $factura->tipo_comprobante = ($doc === 'RUC') ? 'factura' : 'boleta';
        }

        $resultado = $this->driver()->emitir($factura);
        $this->persistir($factura, $resultado);

        return $resultado;
    }

    /** Emite una nota de crédito de anulación y guarda el resultado. */
    public function anular(Factura $factura, string $motivo): ResultadoEmision
    {
        $resultado = $this->driver()->anular($factura, $motivo);
        $this->persistir($factura, $resultado);

        // Si SUNAT aceptó la nota de crédito, la factura queda anulada localmente.
        if ($resultado->ok && $resultado->estado === 'anulado') {
            $factura->update(['estado' => 'anulada']);
        }

        return $resultado;
    }

    /** Vuelca el resultado de la operación en la factura. */
    protected function persistir(Factura $factura, ResultadoEmision $r): void
    {
        $cambios = [];

        if (! empty($r->extra['serie']))       $cambios['serie'] = $r->extra['serie'];
        if (! empty($r->extra['correlativo'])) $cambios['correlativo'] = $r->extra['correlativo'];
        if (! empty($r->extra['xml_path']))    $cambios['sunat_xml_path'] = $r->extra['xml_path'];
        if (! empty($r->extra['cdr_path']))    $cambios['sunat_cdr_path'] = $r->extra['cdr_path'];
        if ($r->estado)  $cambios['sunat_estado'] = $r->estado;
        if ($r->ticket)  $cambios['sunat_ticket'] = $r->ticket;
        if ($r->hash)    $cambios['sunat_hash'] = $r->hash;
        $cambios['sunat_mensaje'] = $r->mensaje;

        if (in_array($r->estado, ['enviado', 'aceptado', 'observado'], true)) {
            $cambios['sunat_enviado_at'] = now();
        }

        if ($cambios) {
            $factura->fill($cambios)->save();
        }
    }
}
