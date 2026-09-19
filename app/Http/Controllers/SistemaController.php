<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SistemaController extends Controller
{
    public function index()
    {
        $info = [
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'base_datos' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host').':'.config('database.connections.mysql.port'),
            'entorno' => app()->environment(),
        ];

        $conteos = [
            'Usuarios' => User::count(),
            'Habitaciones' => Habitacion::count(),
            'Huéspedes' => Huesped::count(),
            'Reservas' => Reserva::count(),
            'Facturas' => Factura::count(),
        ];

        return view('sistema.index', compact('info', 'conteos'));
    }

    /** Genera y descarga un respaldo .sql completo (estructura + datos) usando PHP. */
    public function backup(): StreamedResponse
    {
        $database = config('database.connections.mysql.database');
        $nombre = 'backup_'.$database.'_'.now()->format('Ymd_His').'.sql';

        return response()->streamDownload(function () use ($database) {
            $pdo = DB::getPdo();
            echo "-- Respaldo de la base de datos `{$database}`\n";
            echo '-- Generado: '.now()->toDateTimeString()."\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

            $tablas = array_map('current', DB::select('SHOW TABLES'));
            foreach ($tablas as $tabla) {
                $create = DB::select("SHOW CREATE TABLE `{$tabla}`")[0];
                $createSql = $create->{'Create Table'} ?? array_values((array) $create)[1];

                echo "DROP TABLE IF EXISTS `{$tabla}`;\n";
                echo $createSql.";\n\n";

                $filas = DB::table($tabla)->get();
                if ($filas->isNotEmpty()) {
                    foreach ($filas as $fila) {
                        $cols = array_keys((array) $fila);
                        $vals = array_map(function ($v) use ($pdo) {
                            return $v === null ? 'NULL' : $pdo->quote((string) $v);
                        }, array_values((array) $fila));
                        $colList = '`'.implode('`,`', $cols).'`';
                        echo "INSERT INTO `{$tabla}` ({$colList}) VALUES (".implode(',', $vals).");\n";
                    }
                    echo "\n";
                }
            }
            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $nombre, ['Content-Type' => 'application/sql']);
    }

    public function limpiarCache()
    {
        Artisan::call('optimize:clear');
        return back()->with('ok', 'Caché del sistema limpiada correctamente.');
    }
}
