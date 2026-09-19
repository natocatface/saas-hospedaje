<?php

namespace App\Services\Facturacion\Drivers;

use App\Models\Factura;
use App\Models\FacturacionConfig;
use App\Services\Facturacion\Contracts\ComprobanteDriver;
use App\Services\Facturacion\ResultadoEmision;
use App\Services\Facturacion\SunatComprobanteBuilder;
use Illuminate\Support\Facades\Storage;

/**
 * Driver de emisión ante SUNAT (Perú · UBL 2.1) usando la librería Greenter.
 *
 * Firma el XML con el certificado configurado y lo envía al web service de
 * SUNAT (beta o producción). Guarda el XML firmado y el CDR de respuesta en
 * storage/app/facturacion. Si Greenter no está instalado, numera el
 * comprobante y lo deja pendiente sin perder información.
 *
 * Activar envío real:
 *   1) composer require greenter/greenter
 *   2) coloca el certificado .pem en la ruta configurada
 *   3) prueba en beta con RUC 20000000001 y usuario/clave MODDATOS
 */
class SunatDriver implements ComprobanteDriver
{
    public function __construct(protected FacturacionConfig $config)
    {
    }

    public function clave(): string
    {
        return 'sunat';
    }

    public function probarConexion(): ResultadoEmision
    {
        $faltantes = $this->camposFaltantes();
        if ($faltantes) {
            return ResultadoEmision::error(
                'Faltan datos para conectar con SUNAT: ' . implode(', ', $faltantes) . '.'
            );
        }

        if (! $this->config->certificadoExiste()) {
            return ResultadoEmision::error(
                'No se encontró el certificado en la ruta indicada: ' . $this->config->certificado_ruta
            );
        }

        if (! $this->greenterDisponible()) {
            return ResultadoEmision::ok(
                'Configuración válida. Certificado encontrado. Para el envío real a SUNAT '
                . 'instala la librería con: composer require greenter/greenter'
            );
        }

        $entorno = $this->config->entorno === 'produccion' ? 'Producción' : 'Beta (homologación)';

        return ResultadoEmision::ok("Credenciales y certificado listos · entorno {$entorno}. Ya puedes emitir comprobantes.");
    }

    public function emitir(Factura $factura): ResultadoEmision
    {
        if ($factura->aceptadaPorSunat()) {
            return ResultadoEmision::error('El comprobante ya fue aceptado por SUNAT.');
        }
        if ($msg = $this->validarPrevio()) {
            return ResultadoEmision::error($msg, 'pendiente');
        }

        [$serie, $correlativo] = $this->siguienteNumero($factura);
        $factura->serie = $serie;
        $factura->correlativo = $correlativo;

        // Las boletas (03) se declaran a SUNAT de forma agrupada mediante el
        // Resumen Diario (RC). Aquí solo se numeran y quedan en cola.
        if ($factura->tipo_comprobante === 'boleta') {
            return ResultadoEmision::ok(
                "Boleta {$serie}-{$correlativo} emitida al cliente y en cola. "
                . 'Declárala ante SUNAT desde el Resumen diario de boletas.',
                'pendiente',
                ['serie' => $serie, 'correlativo' => $correlativo]
            );
        }

        if (! $this->greenterDisponible()) {
            return ResultadoEmision::ok(
                "Comprobante numerado {$serie}-{$correlativo} y en cola. "
                . 'Instala Greenter (composer require greenter/greenter) para el envío real a SUNAT.',
                'pendiente',
                ['serie' => $serie, 'correlativo' => $correlativo]
            );
        }

        try {
            $see = $this->crearSee();
            $doc = (new SunatComprobanteBuilder($this->config))->invoice($factura);

            return $this->enviar($see, $doc, $factura, $serie, $correlativo);
        } catch (\Throwable $e) {
            return ResultadoEmision::error('Error al emitir: ' . $e->getMessage(), 'pendiente',
                ['serie' => $serie, 'correlativo' => $correlativo]);
        }
    }

    public function anular(Factura $factura, string $motivo): ResultadoEmision
    {
        if (! $factura->aceptadaPorSunat()) {
            return ResultadoEmision::error(
                'Solo se puede anular con nota de crédito un comprobante aceptado por SUNAT.'
            );
        }
        if ($msg = $this->validarPrevio()) {
            return ResultadoEmision::error($msg);
        }
        if (! $this->greenterDisponible()) {
            return ResultadoEmision::error('Instala Greenter para emitir la nota de crédito ante SUNAT.');
        }

        try {
            $see = $this->crearSee();
            $doc = (new SunatComprobanteBuilder($this->config))->note($factura, $motivo);

            return $this->enviar($see, $doc, $factura, $doc->getSerie(), $doc->getCorrelativo(), true);
        } catch (\Throwable $e) {
            return ResultadoEmision::error('Error al anular: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // Envío / Greenter
    // ---------------------------------------------------------------

    /** Firma, envía a SUNAT y procesa el CDR. */
    protected function enviar($see, $doc, Factura $factura, string $serie, string $correlativo, bool $esNota = false): ResultadoEmision
    {
        // XML firmado (se guarda siempre, aceptado o no)
        $xml = $see->getXmlSigned($doc);
        $nombre = $this->nombreArchivo($doc);
        $xmlPath = "facturacion/{$nombre}.xml";
        $this->guardar($xmlPath, $xml);

        $result = $see->send($doc);

        $hash = $this->extraerHash($xml);

        if (! $result->isSuccess()) {
            $error = $result->getError();
            $msg = $error ? "[{$error->getCode()}] {$error->getMessage()}" : 'SUNAT rechazó el envío.';

            return ResultadoEmision::error("Rechazado por SUNAT: {$msg}", 'rechazado', [
                'serie' => $serie, 'correlativo' => $correlativo, 'hash' => $hash, 'xml_path' => $xmlPath,
            ]);
        }

        // Guardar CDR
        $cdrPath = null;
        $cdrZip = $result->getCdrZip();
        if ($cdrZip) {
            $cdrPath = "facturacion/R-{$nombre}.zip";
            $this->guardar($cdrPath, $cdrZip);
        }

        $cdr = $result->getCdrResponse();
        $code = $cdr?->getCode();
        $desc = $cdr?->getDescription() ?: 'Comprobante aceptado.';
        $notas = $cdr ? $cdr->getNotes() : [];
        $observado = ! empty($notas);

        $estado = $esNota ? 'anulado' : ($observado ? 'observado' : 'aceptado');
        $prefijo = $esNota ? "Nota de crédito {$serie}-{$correlativo}" : "Comprobante {$serie}-{$correlativo}";
        $mensaje = "{$prefijo} aceptado por SUNAT" . ($code !== null ? " (CDR {$code})" : '') . ". {$desc}";
        if ($observado) {
            $mensaje .= ' Observaciones: ' . implode(' | ', $notas);
        }

        return ResultadoEmision::ok($mensaje, $estado, [
            'serie' => $serie, 'correlativo' => $correlativo, 'hash' => $hash,
            'xml_path' => $xmlPath, 'cdr_path' => $cdrPath,
        ]);
    }

    /** Instancia y configura el objeto See de Greenter. */
    protected function crearSee()
    {
        return \App\Services\Facturacion\SunatSeeFactory::make($this->config);
    }

    protected function nombreArchivo($doc): string
    {
        $tipo = method_exists($doc, 'getTipoDoc') ? $doc->getTipoDoc() : '01';

        return sprintf('%s-%s-%s-%s', $this->config->ruc, $tipo, $doc->getSerie(), $doc->getCorrelativo());
    }

    protected function guardar(string $ruta, string $contenido): void
    {
        Storage::disk('local')->put($ruta, $contenido);
    }

    protected function extraerHash(string $xml): ?string
    {
        if (preg_match('/<ds:DigestValue>([^<]+)<\/ds:DigestValue>/', $xml, $m)) {
            return $m[1];
        }

        return null;
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    protected function validarPrevio(): ?string
    {
        $faltantes = $this->camposFaltantes();
        if ($faltantes) {
            return 'Configuración incompleta: ' . implode(', ', $faltantes) . '.';
        }
        if (! $this->config->certificadoExiste()) {
            return 'Certificado no encontrado. Revisa la ruta configurada.';
        }

        return null;
    }

    /** Determina la serie y el siguiente correlativo del comprobante. */
    protected function siguienteNumero(Factura $factura): array
    {
        if ($factura->serie && $factura->correlativo) {
            return [$factura->serie, $factura->correlativo];
        }

        $esFactura = $factura->tipo_comprobante === 'factura';
        $serie = $esFactura
            ? ($this->config->serie_factura ?: 'F001')
            : ($this->config->serie_boleta ?: 'B001');

        $ultimo = Factura::query()
            ->where('serie', $serie)
            ->whereNotNull('correlativo')
            ->orderByDesc('id')
            ->value('correlativo');

        $siguiente = str_pad((string) (((int) $ultimo) + 1), 8, '0', STR_PAD_LEFT);

        return [$serie, $siguiente];
    }

    protected function camposFaltantes(): array
    {
        $faltan = [];
        if (empty($this->config->ruc))          $faltan[] = 'RUC';
        if (empty($this->config->razon_social)) $faltan[] = 'razón social';
        if (empty($this->config->usuario_sol))  $faltan[] = 'usuario SOL';
        if (empty($this->config->clave_sol))    $faltan[] = 'clave SOL';

        return $faltan;
    }

    protected function greenterDisponible(): bool
    {
        return class_exists(\Greenter\See::class);
    }
}
