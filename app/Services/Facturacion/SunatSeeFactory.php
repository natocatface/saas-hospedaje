<?php

namespace App\Services\Facturacion;

use App\Models\FacturacionConfig;

/**
 * Crea y configura el objeto See de Greenter a partir de la configuración de
 * la empresa (certificado, clave SOL y endpoint según el entorno). Se instancia
 * solo cuando Greenter está instalado.
 */
class SunatSeeFactory
{
    public static function make(FacturacionConfig $config)
    {
        $seeClass = \Greenter\See::class;
        $see = new $seeClass();
        $see->setCertificate(file_get_contents($config->certificado_ruta));
        $see->setClaveSOL(
            $config->ruc,
            $config->usuario_sol,
            (string) $config->clave_sol
        );

        $endpoints = \Greenter\Ws\Services\SunatEndpoints::class;
        $see->setService($config->entorno === 'produccion'
            ? $endpoints::FE_PRODUCCION
            : $endpoints::FE_BETA);

        return $see;
    }
}
