<?php

namespace App\Services\Facturacion;

use App\Models\Factura;
use App\Models\FacturacionConfig;
use App\Models\FacturacionResumen;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Declaración de boletas ante SUNAT mediante Resumen Diario (RC).
 *
 * Flujo asíncrono en dos pasos:
 *   1) generar(): agrupa las boletas del día, envía el resumen y guarda el TICKET.
 *   2) consultar(): con el ticket, pregunta el estado y guarda el CDR cuando SUNAT acepta.
 */
class ResumenDiarioService
{
    public function __construct(public FacturacionConfig $config)
    {
    }

    public static function actual(): self
    {
        return new self(FacturacionConfig::actual());
    }

    /** Boletas emitidas en una fecha que aún no han sido declaradas. */
    public function boletasPendientes(Carbon $fecha)
    {
        return Factura::with('reserva.huesped')
            ->where('tipo_comprobante', 'boleta')
            ->whereNotNull('serie')
            ->whereNotNull('correlativo')
            ->whereNull('resumen_id')
            ->whereIn('sunat_estado', ['pendiente'])
            ->whereDate('fecha', $fecha)
            ->orderBy('correlativo')
            ->get();
    }

    /** Genera y envía el resumen diario de las boletas de la fecha indicada. */
    public function generar(Carbon $fecha): ResultadoEmision
    {
        if (! $this->config->habilitado || $this->config->driver !== 'sunat') {
            return ResultadoEmision::error('Activa la facturación electrónica con driver SUNAT.');
        }
        if (! class_exists(\Greenter\See::class)) {
            return ResultadoEmision::error('Instala Greenter para declarar el resumen (composer require greenter/greenter).');
        }
        if (! $this->config->certificadoExiste()) {
            return ResultadoEmision::error('Certificado no encontrado. Revisa la ruta configurada.');
        }

        $boletas = $this->boletasPendientes($fecha);
        if ($boletas->isEmpty()) {
            return ResultadoEmision::error('No hay boletas pendientes de declarar en esa fecha.');
        }

        $correlativo = FacturacionResumen::whereDate('created_at', Carbon::now())->count() + 1;
        $identificador = 'RC-' . Carbon::now()->format('Ymd') . '-' . $correlativo;

        try {
            $see = SunatSeeFactory::make($this->config);
            $summary = (new SunatComprobanteBuilder($this->config))->summary($boletas, $fecha, $correlativo);

            $res = $see->send($summary);
            if (! $res->isSuccess()) {
                $err = $res->getError();
                $msg = $err ? "[{$err->getCode()}] {$err->getMessage()}" : 'SUNAT rechazó el resumen.';

                return ResultadoEmision::error("No se pudo enviar el resumen: {$msg}");
            }

            $ticket = $res->getTicket();

            $resumen = FacturacionResumen::create([
                'identificador'   => $identificador,
                'correlativo'     => $correlativo,
                'fecha_referencia' => $fecha->toDateString(),
                'ticket'          => $ticket,
                'estado'          => 'enviado',
                'total_boletas'   => $boletas->count(),
                'mensaje'         => 'Resumen enviado. Consulta el estado para obtener el CDR.',
                'enviado_at'      => now(),
            ]);

            Factura::whereIn('id', $boletas->pluck('id'))
                ->update(['resumen_id' => $resumen->id, 'sunat_estado' => 'enviado', 'sunat_ticket' => $ticket]);

            return ResultadoEmision::ok(
                "Resumen {$identificador} enviado con {$boletas->count()} boleta(s). Ticket: {$ticket}.",
                'enviado',
                ['resumen_id' => $resumen->id]
            );
        } catch (\Throwable $e) {
            return ResultadoEmision::error('Error al generar el resumen: ' . $e->getMessage());
        }
    }

    /** Consulta el estado del ticket y procesa el CDR si SUNAT ya respondió. */
    public function consultar(FacturacionResumen $resumen): ResultadoEmision
    {
        if (! $resumen->ticket) {
            return ResultadoEmision::error('El resumen no tiene ticket para consultar.');
        }
        if (! class_exists(\Greenter\See::class)) {
            return ResultadoEmision::error('Instala Greenter para consultar el estado.');
        }

        try {
            $see = SunatSeeFactory::make($this->config);
            $status = $see->getStatus($resumen->ticket);

            if ($status->isSuccess()) {
                $cdrPath = null;
                if ($zip = $status->getCdrZip()) {
                    $cdrPath = "facturacion/resumen/R-{$resumen->identificador}.zip";
                    Storage::disk('local')->put($cdrPath, $zip);
                }
                $cdr = $status->getCdrResponse();
                $desc = $cdr?->getDescription() ?: 'Resumen aceptado por SUNAT.';

                $resumen->update([
                    'estado'      => 'aceptado',
                    'cdr_path'    => $cdrPath,
                    'mensaje'     => $desc,
                    'aceptado_at' => now(),
                ]);
                $resumen->boletas()->update([
                    'sunat_estado'  => 'aceptado',
                    'sunat_cdr_path' => $cdrPath,
                ]);

                return ResultadoEmision::ok("Resumen {$resumen->identificador} aceptado. {$desc}", 'aceptado');
            }

            $err = $status->getError();
            $code = (string) ($err?->getCode());

            // 98 = en proceso, 126 = existe un proceso pendiente
            if (in_array($code, ['98', '126'], true)) {
                $resumen->update(['estado' => 'procesando', 'mensaje' => 'SUNAT aún está procesando el resumen. Intenta más tarde.']);

                return ResultadoEmision::ok('El resumen sigue en proceso en SUNAT. Vuelve a consultar en unos minutos.', 'procesando');
            }

            // Rechazado: liberamos las boletas para re-declararlas
            $msg = $err ? "[{$code}] {$err->getMessage()}" : 'Resumen rechazado por SUNAT.';
            $resumen->update(['estado' => 'rechazado', 'mensaje' => $msg]);
            $resumen->boletas()->update(['sunat_estado' => 'pendiente', 'resumen_id' => null, 'sunat_ticket' => null]);

            return ResultadoEmision::error("Resumen rechazado: {$msg}", 'rechazado');
        } catch (\Throwable $e) {
            return ResultadoEmision::error('Error al consultar el estado: ' . $e->getMessage());
        }
    }

    public function descargarCdr(FacturacionResumen $resumen)
    {
        abort_unless($resumen->cdr_path && Storage::disk('local')->exists($resumen->cdr_path), 404);

        return Storage::disk('local')->download($resumen->cdr_path, "CDR-{$resumen->identificador}.zip");
    }
}
