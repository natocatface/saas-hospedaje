<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/**
 * Configuración de Facturación Electrónica (SUNAT · Perú) por empresa.
 */
class FacturacionConfig extends Model
{
    use BelongsToEmpresa;

    protected $table = 'facturacion_configs';

    protected $fillable = [
        'empresa_id',
        'habilitado', 'emitir_automatico', 'driver', 'entorno',
        'ruc', 'razon_social', 'nombre_comercial', 'direccion_fiscal',
        'ubigeo', 'departamento', 'provincia', 'distrito',
        'usuario_sol', 'clave_sol', 'certificado_ruta', 'certificado_clave',
        'serie_boleta', 'serie_factura', 'serie_nota_credito', 'moneda',
    ];

    protected $casts = [
        'habilitado' => 'boolean',
        'emitir_automatico' => 'boolean',
        'clave_sol' => 'encrypted',
        'certificado_clave' => 'encrypted',
    ];

    /** Drivers de emisión disponibles (clave => etiqueta para el selector). */
    public const DRIVERS = [
        'ninguno' => 'Ninguno (no emite, deja pendiente)',
        'sunat'   => 'SUNAT (Greenter · UBL 2.1)',
    ];

    /** Entornos SUNAT (clave => etiqueta). */
    public const ENTORNOS = [
        'beta'       => 'Beta (homologación / pruebas)',
        'produccion' => 'Producción',
    ];

    /** Obtiene (o crea) la configuración de la empresa autenticada. */
    public static function actual(): self
    {
        $empresaId = auth()->user()?->empresa_id;

        return static::firstOrCreate(
            ['empresa_id' => $empresaId],
            ['driver' => 'ninguno', 'entorno' => 'beta', 'moneda' => 'PEN']
        );
    }

    /** ¿Existe físicamente el certificado en la ruta indicada? */
    public function certificadoExiste(): bool
    {
        return ! empty($this->certificado_ruta) && File::exists($this->certificado_ruta);
    }

    /** Etiqueta legible del driver activo. */
    public function driverEtiqueta(): string
    {
        return self::DRIVERS[$this->driver] ?? $this->driver;
    }

    /** ¿La configuración mínima está completa para intentar emitir? */
    public function listoParaEmitir(): bool
    {
        return $this->habilitado
            && $this->driver !== 'ninguno'
            && ! empty($this->ruc)
            && ! empty($this->usuario_sol)
            && ! empty($this->clave_sol);
    }
}
