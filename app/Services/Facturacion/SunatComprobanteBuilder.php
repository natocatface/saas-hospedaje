<?php

namespace App\Services\Facturacion;

use App\Models\Factura;
use App\Models\FacturacionConfig;
use App\Support\NumeroALetras;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Traduce una Factura del sistema de hospedaje a los documentos de Greenter
 * (Invoice para boleta/factura, Note para nota de crédito), calculando las
 * líneas (hospedaje + consumos) y los importes con/sin IGV.
 *
 * Esta clase SOLO se instancia cuando Greenter está instalado (lo garantiza
 * SunatDriver), por eso puede referenciar sus clases con seguridad.
 */
class SunatComprobanteBuilder
{
    private const IGV = 0.18;

    public function __construct(private FacturacionConfig $config)
    {
    }

    /** Construye la boleta/factura de venta. */
    public function invoice(Factura $factura): Invoice
    {
        $esFactura = $factura->tipo_comprobante === 'factura';
        [$details, $totales] = $this->detalles($factura);

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')                         // venta interna
            ->setTipoDoc($esFactura ? '01' : '03')             // 01 factura, 03 boleta
            ->setSerie($factura->serie)
            ->setCorrelativo($factura->correlativo)
            ->setFechaEmision($this->fechaEmision($factura))
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda($this->config->moneda ?: 'PEN')
            ->setCompany($this->company())
            ->setClient($this->client($factura, $esFactura))
            ->setMtoOperGravadas($totales['gravadas'])
            ->setMtoIGV($totales['igv'])
            ->setTotalImpuestos($totales['igv'])
            ->setValorVenta($totales['gravadas'])
            ->setSubTotal($totales['total'])
            ->setMtoImpVenta($totales['total'])
            ->setDetails($details)
            ->setLegends([$this->leyendaMonto($totales['total'])]);

        return $invoice;
    }

    /** Construye la nota de crédito que anula el comprobante. */
    public function note(Factura $factura, string $motivo): Note
    {
        $esFactura = $factura->tipo_comprobante === 'factura';
        [$details, $totales] = $this->detalles($factura);

        $serieNc = $esFactura
            ? ($this->config->serie_nota_credito ?: 'FC01')
            : 'B' . substr($this->config->serie_nota_credito ?: 'FC01', 1);

        $note = (new Note())
            ->setUblVersion('2.1')
            ->setTipoDoc('07')                                 // nota de crédito
            ->setSerie($serieNc)
            ->setCorrelativo($factura->correlativo)
            ->setFechaEmision($this->fechaEmision($factura))
            ->setTipDocAfectado($esFactura ? '01' : '03')
            ->setNumDocfectado("{$factura->serie}-{$factura->correlativo}")
            ->setCodMotivo('01')                               // anulación de la operación
            ->setDesMotivo(mb_strtoupper($motivo ?: 'ANULACION DE LA OPERACION'))
            ->setTipoMoneda($this->config->moneda ?: 'PEN')
            ->setCompany($this->company())
            ->setClient($this->client($factura, $esFactura))
            ->setMtoOperGravadas($totales['gravadas'])
            ->setMtoIGV($totales['igv'])
            ->setTotalImpuestos($totales['igv'])
            ->setMtoImpVenta($totales['total'])
            ->setDetails($details)
            ->setLegends([$this->leyendaMonto($totales['total'])]);

        return $note;
    }

    /**
     * Construye el Resumen Diario (RC) de un conjunto de boletas.
     *
     * @param  Collection<int,\App\Models\Factura>  $boletas
     */
    public function summary(Collection $boletas, Carbon $fechaReferencia, int $correlativo): Summary
    {
        $details = [];
        foreach ($boletas as $b) {
            $total = (float) $b->total;
            $gravadas = (float) ($b->subtotal ?: round($total / (1 + self::IGV), 2));
            $igv = (float) ($b->igv ?: round($total - $gravadas, 2));
            $huesped = $b->reserva?->huesped;

            $details[] = (new SummaryDetail())
                ->setTipoDoc('03')
                ->setSerieNro("{$b->serie}-{$b->correlativo}")
                ->setEstado('1')                       // 1 = adicionar
                ->setClienteTipo($this->mapDocTipo($huesped?->documento_tipo, false))
                ->setClienteNro($huesped?->documento_numero ?: '00000000')
                ->setTotal(round($total, 2))
                ->setMtoOperGravadas(round($gravadas, 2))
                ->setMtoIGV(round($igv, 2));
        }

        return (new Summary())
            ->setFecGeneracion($fechaReferencia->copy())
            ->setFecResumen(Carbon::now())
            ->setCorrelativo((string) $correlativo)
            ->setMoneda($this->config->moneda ?: 'PEN')
            ->setCompany($this->company())
            ->setDetails($details);
    }

    // ---------------------------------------------------------------
    // Piezas
    // ---------------------------------------------------------------

    private function company(): Company
    {
        $address = (new Address())
            ->setUbigueo($this->config->ubigeo ?: '150101')
            ->setDepartamento($this->config->departamento ?: 'LIMA')
            ->setProvincia($this->config->provincia ?: 'LIMA')
            ->setDistrito($this->config->distrito ?: 'LIMA')
            ->setUrbanizacion('-')
            ->setDireccion($this->config->direccion_fiscal ?: '-')
            ->setCodLocal('0000');

        return (new Company())
            ->setRuc($this->config->ruc)
            ->setRazonSocial($this->config->razon_social)
            ->setNombreComercial($this->config->nombre_comercial ?: $this->config->razon_social)
            ->setAddress($address);
    }

    private function client(Factura $factura, bool $esFactura): Client
    {
        $huesped = $factura->reserva?->huesped;

        $tipoDoc = $this->mapDocTipo($huesped?->documento_tipo, $esFactura);
        $numDoc  = $huesped?->documento_numero ?? ($esFactura ? '' : '00000000');
        $nombre  = $huesped
            ? trim(($huesped->nombres ?? '') . ' ' . ($huesped->apellidos ?? ''))
            : 'CLIENTE VARIOS';

        return (new Client())
            ->setTipoDoc($tipoDoc)
            ->setNumDoc($numDoc ?: '00000000')
            ->setRznSocial($nombre !== '' ? mb_strtoupper($nombre) : 'CLIENTE VARIOS');
    }

    /** @return array{0: SaleDetail[], 1: array{gravadas:float,igv:float,total:float}} */
    private function detalles(Factura $factura): array
    {
        $reserva = $factura->reserva;
        $lineasBrutas = [];   // [descripcion, cantidad, precioUnitarioBruto]

        // Línea de hospedaje
        $noches = (int) ($reserva->noches ?? 1) ?: 1;
        $tarifa = (float) ($reserva->tarifa_noche ?? 0);
        if ($tarifa > 0) {
            $hab = $reserva->habitacion?->numero ?? '';
            $lineasBrutas[] = [
                "Servicio de hospedaje" . ($hab ? " - Hab. {$hab}" : '') . " ({$noches} noche" . ($noches > 1 ? 's' : '') . ')',
                $noches,
                $tarifa,
            ];
        }

        // Consumos
        foreach (($reserva->consumos ?? []) as $c) {
            $lineasBrutas[] = [
                $c->descripcion ?: ($c->producto?->nombre ?? 'Consumo'),
                (float) $c->cantidad ?: 1,
                (float) $c->precio_unit,
            ];
        }

        // Fallback: si no hay líneas, usar el total de la factura como servicio único
        if (empty($lineasBrutas)) {
            $lineasBrutas[] = ['Servicio de hospedaje', 1, (float) $factura->total];
        }

        $details = [];
        $sumGravadas = 0.0;
        $sumIgv = 0.0;
        $i = 0;

        foreach ($lineasBrutas as [$desc, $cant, $precioBruto]) {
            $i++;
            $cant = $cant > 0 ? $cant : 1;
            $valorUnit = round($precioBruto / (1 + self::IGV), 2);
            $valorVenta = round($valorUnit * $cant, 2);
            $igv = round($valorVenta * self::IGV, 2);
            $precioUnit = round($valorUnit * (1 + self::IGV), 2);

            $sumGravadas += $valorVenta;
            $sumIgv += $igv;

            $details[] = (new SaleDetail())
                ->setCodProducto('ITEM' . str_pad((string) $i, 3, '0', STR_PAD_LEFT))
                ->setUnidad('NIU')
                ->setCantidad($cant)
                ->setDescripcion(mb_substr($desc, 0, 250))
                ->setMtoValorUnitario($valorUnit)
                ->setMtoValorVenta($valorVenta)
                ->setMtoBaseIgv($valorVenta)
                ->setPorcentajeIgv(18.0)
                ->setIgv($igv)
                ->setTipAfeIgv('10')                    // gravado - operación onerosa
                ->setTotalImpuestos($igv)
                ->setMtoPrecioUnitario($precioUnit);
        }

        $sumGravadas = round($sumGravadas, 2);
        $sumIgv = round($sumIgv, 2);

        return [$details, [
            'gravadas' => $sumGravadas,
            'igv'      => $sumIgv,
            'total'    => round($sumGravadas + $sumIgv, 2),
        ]];
    }

    private function leyendaMonto(float $total): Legend
    {
        $moneda = ($this->config->moneda ?: 'PEN') === 'USD' ? 'DOLARES AMERICANOS' : 'SOLES';

        return (new Legend())
            ->setCode('1000')
            ->setValue('SON ' . NumeroALetras::convertir($total) . ' ' . $moneda);
    }

    private function fechaEmision(Factura $factura): \DateTime
    {
        try {
            return new \DateTime(($factura->fecha ?? now())->format('Y-m-d') . ' ' . now()->format('H:i:s'));
        } catch (\Throwable $e) {
            return new \DateTime();
        }
    }

    /** Mapea el tipo de documento del huésped al catálogo 06 de SUNAT. */
    private function mapDocTipo(?string $tipo, bool $esFactura): string
    {
        if ($esFactura) {
            return '6'; // factura exige RUC
        }

        return match (strtoupper((string) $tipo)) {
            'RUC'                 => '6',
            'CE', 'CARNET'        => '4',
            'PASAPORTE', 'PASS'   => '7',
            default               => '1', // DNI
        };
    }
}
