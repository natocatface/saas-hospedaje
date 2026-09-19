<?php

namespace App\Support;

class Permisos
{
    /** Catálogo de permisos agrupados (clave => etiqueta). */
    public const CATALOGO = [
        'Operaciones' => [
            'calendario.ver'         => 'Ver calendario',
            'reservas.gestionar'     => 'Gestionar reservas y consumos',
            'huespedes.gestionar'    => 'Gestionar huéspedes',
            'facturacion.ver'        => 'Ver facturas y registrar cobros',
            'facturacion.anular'     => 'Anular facturas',
            'caja.gestionar'         => 'Abrir / cerrar caja',
            'housekeeping.gestionar' => 'Gestionar housekeeping',
        ],
        'Administración' => [
            'habitaciones.gestionar' => 'Gestionar habitaciones',
            'productos.gestionar'    => 'Gestionar productos',
            'tarifas.gestionar'      => 'Gestionar tarifas de temporada',
            'reportes.ver'           => 'Ver reportes',
        ],
    ];

    /** Defaults por rol (admin tiene todos por bypass). */
    public const DEFAULTS = [
        'recepcionista' => [
            'calendario.ver', 'reservas.gestionar', 'huespedes.gestionar',
            'facturacion.ver', 'caja.gestionar', 'housekeeping.gestionar',
            'habitaciones.gestionar', 'productos.gestionar', 'reportes.ver',
        ],
        'housekeeping' => [
            'calendario.ver', 'housekeeping.gestionar',
        ],
    ];

    /** Lista plana de todas las claves de permiso. */
    public static function todos(): array
    {
        $out = [];
        foreach (self::CATALOGO as $grupo) {
            $out = array_merge($out, array_keys($grupo));
        }
        return $out;
    }

    /** Permisos por defecto de un rol. */
    public static function porDefecto(string $rol): array
    {
        return self::DEFAULTS[$rol] ?? [];
    }

    /** Roles editables (admin se gestiona por bypass). */
    public static function rolesEditables(): array
    {
        return ['recepcionista' => 'Recepcionista', 'housekeeping' => 'Housekeeping'];
    }
}
