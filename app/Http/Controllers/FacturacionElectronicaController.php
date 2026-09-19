<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturacionConfig;
use App\Models\FacturacionResumen;
use App\Services\Facturacion\FacturacionElectronica;
use App\Services\Facturacion\ResumenDiarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class FacturacionElectronicaController extends Controller
{
    /** Pantalla de configuración de la facturación electrónica. */
    public function edit()
    {
        $config = FacturacionConfig::actual();

        // Prefill inteligente en el primer acceso (sin sobrescribir lo guardado).
        if (! $config->exists || empty($config->ruc)) {
            $empresa = Empresa::find(auth()->user()->empresa_id);
            $config->ruc            = $config->ruc            ?: ($empresa->ruc ?? Configuracion::get('ruc'));
            $config->razon_social   = $config->razon_social   ?: ($empresa->nombre ?? Configuracion::get('nombre_hotel'));
            $config->nombre_comercial = $config->nombre_comercial ?: Configuracion::get('nombre_hotel');
            $config->direccion_fiscal = $config->direccion_fiscal ?: ($empresa->direccion ?? Configuracion::get('direccion'));
        }

        return view('facturacion_electronica.configuracion', compact('config'));
    }

    /** Guarda la configuración. */
    public function update(Request $request)
    {
        $data = $request->validate([
            'habilitado'         => 'nullable|boolean',
            'emitir_automatico'  => 'nullable|boolean',
            'driver'             => 'required|in:' . implode(',', array_keys(FacturacionConfig::DRIVERS)),
            'entorno'            => 'required|in:' . implode(',', array_keys(FacturacionConfig::ENTORNOS)),

            'ruc'                => 'required|string|max:15',
            'razon_social'       => 'required|string|max:200',
            'nombre_comercial'   => 'nullable|string|max:200',
            'direccion_fiscal'   => 'nullable|string|max:200',
            'ubigeo'             => 'nullable|string|max:10',
            'departamento'       => 'nullable|string|max:60',
            'provincia'          => 'nullable|string|max:60',
            'distrito'           => 'nullable|string|max:60',

            'usuario_sol'        => 'nullable|string|max:60',
            'clave_sol'          => 'nullable|string|max:100',
            'certificado_ruta'   => 'nullable|string|max:255',
            'certificado_clave'  => 'nullable|string|max:100',

            'serie_boleta'       => 'nullable|string|max:6',
            'serie_factura'      => 'nullable|string|max:6',
            'serie_nota_credito' => 'nullable|string|max:6',
            'moneda'             => 'nullable|string|max:5',
        ]);

        $config = FacturacionConfig::actual();

        $data['habilitado']        = $request->boolean('habilitado');
        $data['emitir_automatico'] = $request->boolean('emitir_automatico');

        // No sobrescribir secretos si el usuario dejó el campo en blanco.
        if (blank($data['clave_sol'] ?? null)) {
            unset($data['clave_sol']);
        }
        if (blank($data['certificado_clave'] ?? null)) {
            unset($data['certificado_clave']);
        }

        $config->fill($data)->save();

        return redirect()->route('facturacion.config')->with('ok', 'Configuración de facturación electrónica guardada.');
    }

    /** Ejecuta la prueba de conexión con SUNAT. */
    public function probar()
    {
        $resultado = FacturacionElectronica::actual()->probarConexion();

        return back()->with($resultado->ok ? 'ok' : 'error', $resultado->mensaje);
    }

    /** Emite (envía a SUNAT) el comprobante de una factura. */
    public function emitir(Factura $factura)
    {
        $fe = FacturacionElectronica::actual();

        if (! $fe->config->habilitado) {
            return back()->with('error', 'Activa la facturación electrónica en su configuración antes de emitir.');
        }

        $resultado = $fe->emitir($factura);

        return back()->with($resultado->ok ? 'ok' : 'error', $resultado->mensaje);
    }

    /** Emite una nota de crédito que anula el comprobante. */
    public function notaCredito(Request $request, Factura $factura)
    {
        $request->validate(['motivo' => 'required|string|max:250']);

        $resultado = FacturacionElectronica::actual()->anular($factura, $request->motivo);

        return back()->with($resultado->ok ? 'ok' : 'error', $resultado->mensaje);
    }

    /** Descarga el XML firmado del comprobante. */
    public function descargarXml(Factura $factura)
    {
        abort_unless($factura->sunat_xml_path && Storage::disk('local')->exists($factura->sunat_xml_path), 404);

        return Storage::disk('local')->download($factura->sunat_xml_path, "{$factura->serie}-{$factura->correlativo}.xml");
    }

    /** Descarga el CDR (constancia de recepción) de SUNAT. */
    public function descargarCdr(Factura $factura)
    {
        abort_unless($factura->sunat_cdr_path && Storage::disk('local')->exists($factura->sunat_cdr_path), 404);

        return Storage::disk('local')->download($factura->sunat_cdr_path, "CDR-{$factura->serie}-{$factura->correlativo}.zip");
    }

    // ---------------------------------------------------------------
    // Resumen Diario de Boletas (RC)
    // ---------------------------------------------------------------

    /** Pantalla del resumen diario: boletas del día y resúmenes generados. */
    public function resumenIndex(Request $request)
    {
        $fecha = $request->filled('fecha') ? Carbon::parse($request->fecha) : Carbon::today();

        $servicio = ResumenDiarioService::actual();
        $boletas  = $servicio->boletasPendientes($fecha);
        $config   = $servicio->config;

        $resumenes = FacturacionResumen::orderByDesc('id')->limit(20)->get();

        return view('facturacion_electronica.resumen', compact('fecha', 'boletas', 'resumenes', 'config'));
    }

    /** Genera y envía el resumen diario de la fecha indicada. */
    public function resumenGenerar(Request $request)
    {
        $request->validate(['fecha' => 'required|date']);

        $resultado = ResumenDiarioService::actual()->generar(Carbon::parse($request->fecha));

        return redirect()->route('facturacion.resumen', ['fecha' => $request->fecha])
            ->with($resultado->ok ? 'ok' : 'error', $resultado->mensaje);
    }

    /** Consulta el estado (ticket) de un resumen y procesa el CDR. */
    public function resumenConsultar(FacturacionResumen $resumen)
    {
        $resultado = ResumenDiarioService::actual()->consultar($resumen);

        return back()->with($resultado->ok ? 'ok' : 'error', $resultado->mensaje);
    }

    /** Descarga el CDR del resumen. */
    public function resumenCdr(FacturacionResumen $resumen)
    {
        return ResumenDiarioService::actual()->descargarCdr($resumen);
    }
}
