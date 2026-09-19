<?php

namespace App\Services\Facturacion;

/**
 * Resultado inmutable de una operación de emisión / conexión con SUNAT.
 * Lo devuelven todos los drivers para que el controlador reaccione igual
 * sin importar la implementación concreta.
 */
class ResultadoEmision
{
    public function __construct(
        public bool $ok,
        public string $mensaje,
        public ?string $estado = null,   // sunat_estado sugerido para la factura
        public ?string $ticket = null,
        public ?string $hash = null,
        public array $extra = [],
    ) {
    }

    public static function ok(string $mensaje, ?string $estado = null, array $extra = []): self
    {
        return new self(true, $mensaje, $estado, $extra['ticket'] ?? null, $extra['hash'] ?? null, $extra);
    }

    public static function error(string $mensaje, ?string $estado = null, array $extra = []): self
    {
        return new self(false, $mensaje, $estado, null, null, $extra);
    }
}
