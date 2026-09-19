<?php

namespace App\Support;

/**
 * Convierte un importe a su representación en letras para la leyenda 1000
 * del comprobante electrónico (formato SUNAT):
 *   1234.50  ->  "MIL DOSCIENTOS TREINTA Y CUATRO CON 50/100"
 */
class NumeroALetras
{
    private const UNIDADES = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    private const ESPECIALES = [
        10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE',
        15 => 'QUINCE', 16 => 'DIECISEIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE',
        20 => 'VEINTE', 21 => 'VEINTIUNO', 22 => 'VEINTIDOS', 23 => 'VEINTITRES', 24 => 'VEINTICUATRO',
        25 => 'VEINTICINCO', 26 => 'VEINTISEIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE',
    ];
    private const DECENAS = [
        2 => 'VEINTE', 3 => 'TREINTA', 4 => 'CUARENTA', 5 => 'CINCUENTA',
        6 => 'SESENTA', 7 => 'SETENTA', 8 => 'OCHENTA', 9 => 'NOVENTA',
    ];
    private const CENTENAS = [
        1 => 'CIENTO', 2 => 'DOSCIENTOS', 3 => 'TRESCIENTOS', 4 => 'CUATROCIENTOS', 5 => 'QUINIENTOS',
        6 => 'SEISCIENTOS', 7 => 'SETECIENTOS', 8 => 'OCHOCIENTOS', 9 => 'NOVECIENTOS',
    ];

    public static function convertir(float $monto): string
    {
        $entero = (int) floor($monto);
        $centavos = (int) round(($monto - $entero) * 100);
        $centavos = str_pad((string) $centavos, 2, '0', STR_PAD_LEFT);

        $letras = $entero === 0 ? 'CERO' : trim(self::seccionMillones($entero));

        return "{$letras} CON {$centavos}/100";
    }

    private static function seccionMillones(int $n): string
    {
        if ($n < 1000) {
            return self::seccionCentenas($n);
        }
        if ($n < 1000000) {
            $miles = intdiv($n, 1000);
            $resto = $n % 1000;
            $prefijo = $miles === 1 ? 'MIL' : trim(self::seccionCentenas($miles)) . ' MIL';

            return trim($prefijo . ' ' . self::seccionCentenas($resto));
        }

        $millones = intdiv($n, 1000000);
        $resto = $n % 1000000;
        $prefijo = $millones === 1 ? 'UN MILLON' : trim(self::seccionCentenas($millones)) . ' MILLONES';

        return trim($prefijo . ' ' . self::seccionMillones($resto));
    }

    private static function seccionCentenas(int $n): string
    {
        if ($n === 0) {
            return '';
        }
        if ($n === 100) {
            return 'CIEN';
        }

        $centena = intdiv($n, 100);
        $resto = $n % 100;

        $texto = $centena > 0 ? self::CENTENAS[$centena] . ' ' : '';

        return trim($texto . self::seccionDecenas($resto));
    }

    private static function seccionDecenas(int $n): string
    {
        if ($n === 0) {
            return '';
        }
        if ($n < 10) {
            return self::UNIDADES[$n];
        }
        if (isset(self::ESPECIALES[$n])) {
            return self::ESPECIALES[$n];
        }

        $decena = intdiv($n, 10);
        $unidad = $n % 10;

        if ($unidad === 0) {
            return self::DECENAS[$decena];
        }

        return self::DECENAS[$decena] . ' Y ' . self::UNIDADES[$unidad];
    }
}
