<?php

namespace Database\Seeders;

use App\Models\CajaSesion;
use App\Models\Consumo;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\TarifaTemporada;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder de demostración para el DASHBOARD.
 *
 * Genera ~10 registros por módulo con fechas ancladas a "hoy" para que los
 * paneles e indicadores (ocupación últimos 7 días, reservas por estado del mes,
 * ingresos, ADR/RevPAR, check-ins/outs de hoy, caja, housekeeping) muestren
 * información realista.
 *
 * Es RE-EJECUTABLE: antes de insertar borra los datos que él mismo creó
 * (identificados por el prefijo/marcador "DEMO"), así no se duplican.
 *
 * Uso:  php artisan db:seed --class=DemoDashboardSeeder
 */
class DemoDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $hoy = Carbon::today();

        // Empresa tenant principal (la que se ve en el dashboard).
        $empresa = Empresa::where('slug', 'hotel-demo')->first() ?? Empresa::orderBy('id')->first();
        if (! $empresa) {
            $this->command?->error('No existe ninguna empresa. Ejecuta primero: php artisan db:seed');
            return;
        }
        $eid   = $empresa->id;
        $admin = User::where('empresa_id', $eid)->orderBy('id')->first();

        // ---------------------------------------------------------------
        // 0) Limpieza de datos DEMO previos (para poder re-ejecutar)
        // ---------------------------------------------------------------
        $reservasDemo = Reserva::where('empresa_id', $eid)->where('codigo', 'like', 'DEMO-%')->pluck('id');
        if ($reservasDemo->isNotEmpty()) {
            Consumo::whereIn('reserva_id', $reservasDemo)->delete();
            $facturasDemo = Factura::whereIn('reserva_id', $reservasDemo)->pluck('id');
            Pago::whereIn('factura_id', $facturasDemo)->delete();
            Factura::whereIn('id', $facturasDemo)->delete();
            Reserva::whereIn('id', $reservasDemo)->delete();
        }
        Pago::where('empresa_id', $eid)->where('nota', 'DEMO')->delete();
        CajaSesion::where('empresa_id', $eid)->where('notas', 'like', 'DEMO%')->delete();
        Huesped::where('empresa_id', $eid)->where('email', 'like', 'demo.%@correo.com')->delete();
        Producto::where('empresa_id', $eid)->where('descripcion', 'DEMO')->delete();
        TarifaTemporada::where('empresa_id', $eid)->where('nombre', 'like', 'DEMO %')->delete();
        User::where('empresa_id', $eid)->where('email', 'like', 'demo.staff%@hospedaje.com')->delete();

        // ---------------------------------------------------------------
        // 1) HUÉSPEDES (10)
        // ---------------------------------------------------------------
        $nombres   = ['Carlos', 'María', 'José', 'Ana', 'Luis', 'Rosa', 'Pedro', 'Carmen', 'Jorge', 'Lucía', 'Miguel', 'Elena'];
        $apellidos = ['Quispe', 'Mamani', 'Flores', 'Rojas', 'Vargas', 'Torres', 'Huamán', 'Castro', 'Ramos', 'Díaz', 'Chávez', 'Sánchez'];
        $huespedes = [];
        for ($i = 0; $i < 10; $i++) {
            $huespedes[] = Huesped::create([
                'empresa_id'       => $eid,
                'nombres'          => $nombres[array_rand($nombres)],
                'apellidos'        => $apellidos[array_rand($apellidos)] . ' ' . $apellidos[array_rand($apellidos)],
                'documento_tipo'   => 'DNI',
                'documento_numero' => (string) random_int(40000000, 79999999),
                'telefono'         => '9' . random_int(10000000, 99999999),
                'email'            => 'demo.' . $i . '@correo.com',
                'nacionalidad'     => 'Peru',
                'direccion'        => 'Av. Siempre Viva ' . random_int(100, 999),
            ]);
        }
        // Aseguramos poder mezclar con huéspedes ya existentes.
        $poolHuespedes = Huesped::where('empresa_id', $eid)->get();

        // ---------------------------------------------------------------
        // 2) PRODUCTOS (10)
        // ---------------------------------------------------------------
        $catalogo = [
            ['Botella de vino', 'minibar', 45], ['Jugo natural', 'minibar', 8],
            ['Café premium', 'restaurante', 12], ['Cena romántica', 'restaurante', 90],
            ['Traslado aeropuerto', 'servicios', 60], ['Spa / masaje', 'servicios', 120],
            ['Planchado (unidad)', 'lavanderia', 8], ['Tabla de quesos', 'restaurante', 55],
            ['Estacionamiento/día', 'servicios', 20], ['Kit de aseo', 'otros', 15],
        ];
        $prodModels = [];
        foreach ($catalogo as [$n, $cat, $precio]) {
            $prodModels[] = Producto::create([
                'empresa_id' => $eid, 'nombre' => $n, 'categoria' => $cat,
                'precio' => $precio, 'descripcion' => 'DEMO', 'activo' => true,
            ]);
        }
        $poolProductos = Producto::where('empresa_id', $eid)->get();

        // ---------------------------------------------------------------
        // 3) USUARIOS del equipo (10)
        // ---------------------------------------------------------------
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'empresa_id' => $eid,
                'name'       => 'Colaborador ' . $i,
                'email'      => 'demo.staff' . $i . '@hospedaje.com',
                'telefono'   => '9' . random_int(10000000, 99999999),
                'rol'        => $i % 2 ? 'recepcionista' : 'housekeeping',
                'activo'     => true,
                'password'   => Hash::make('password'),
            ]);
        }

        // ---------------------------------------------------------------
        // 4) HABITACIONES: variedad de estados para las donas / housekeeping
        // ---------------------------------------------------------------
        $habs = Habitacion::where('empresa_id', $eid)->orderBy('id')->get();
        if ($habs->count() > 0) {
            // Reparto: ocupadas / limpieza / mantenimiento / resto disponible.
            foreach ($habs as $idx => $h) {
                if ($idx < 8)        $h->update(['estado' => 'ocupada']);
                elseif ($idx < 14)   $h->update(['estado' => 'limpieza']);
                elseif ($idx < 18)   $h->update(['estado' => 'mantenimiento']);
                else                 $h->update(['estado' => 'disponible']);
            }
        }
        $poolHabs = Habitacion::where('empresa_id', $eid)->get();
        if ($poolHabs->isEmpty()) {
            $this->command?->error('No hay habitaciones para esta empresa. Ejecuta primero: php artisan db:seed');
            return;
        }

        $correlativo = (int) (Reserva::where('empresa_id', $eid)->count() + 1000);
        $facturaNum  = (int) (Factura::where('empresa_id', $eid)->count() + 1000);

        $crearReserva = function (Carbon $checkin, int $noches, string $estado, bool $forzarPago = false)
            use ($eid, $admin, $poolHuespedes, $poolHabs, $poolProductos, &$correlativo, &$facturaNum) {

            $habitacion = $poolHabs->random();
            $huesped    = $poolHuespedes->random();
            $checkout   = (clone $checkin)->addDays($noches);
            $tarifa     = (float) $habitacion->precio_noche;
            $totalHosp  = $tarifa * $noches;

            $reserva = Reserva::create([
                'empresa_id'    => $eid,
                'codigo'        => 'DEMO-' . str_pad((string) $correlativo++, 5, '0', STR_PAD_LEFT),
                'huesped_id'    => $huesped->id,
                'habitacion_id' => $habitacion->id,
                'user_id'       => $admin?->id,
                'fecha_checkin' => $checkin->toDateString(),
                'fecha_checkout' => $checkout->toDateString(),
                'noches'        => $noches,
                'adultos'       => random_int(1, 2),
                'ninos'         => random_int(0, 2),
                'tarifa_noche'  => $tarifa,
                'total'         => $totalHosp,
                'estado'        => $estado,
                'created_at'    => $checkin->copy()->subDays(random_int(0, 3)),
            ]);

            // Consumos (para algunas reservas)
            $totalConsumos = 0;
            if (random_int(0, 100) > 40) {
                $items = random_int(1, 3);
                for ($k = 0; $k < $items; $k++) {
                    $prod = $poolProductos->random();
                    $cant = random_int(1, 3);
                    $sub  = $cant * (float) $prod->precio;
                    $totalConsumos += $sub;
                    Consumo::create([
                        'empresa_id'  => $eid,
                        'reserva_id'  => $reserva->id,
                        'producto_id' => $prod->id,
                        'descripcion' => $prod->nombre,
                        'cantidad'    => $cant,
                        'precio_unit' => $prod->precio,
                        'subtotal'    => $sub,
                        'fecha'       => $checkin->toDateString(),
                        'user_id'     => $admin?->id,
                    ]);
                }
            }

            $total = $totalHosp + $totalConsumos;
            $reserva->update(['total' => $total]);

            // Factura + pago
            $pagada = $forzarPago || (! in_array($estado, ['pendiente', 'cancelada']) && random_int(0, 100) > 30);
            $metodo = ['efectivo', 'tarjeta', 'yape', 'transferencia'][array_rand([0, 1, 2, 3])];
            $subtotal = round($total / 1.18, 2);
            $factura  = Factura::create([
                'empresa_id' => $eid,
                'numero'     => 'DEMO-' . str_pad((string) $facturaNum++, 6, '0', STR_PAD_LEFT),
                'reserva_id' => $reserva->id,
                'fecha'      => $checkout->toDateString(),
                'subtotal'   => $subtotal,
                'igv'        => round($total - $subtotal, 2),
                'total'      => $total,
                'estado'     => $estado === 'cancelada' ? 'anulada' : ($pagada ? 'pagada' : 'pendiente'),
                'metodo_pago' => $pagada ? $metodo : null,
            ]);
            if ($pagada && $estado !== 'cancelada') {
                Pago::create([
                    'empresa_id' => $eid,
                    'factura_id' => $factura->id,
                    'reserva_id' => $reserva->id,
                    'user_id'    => $admin?->id,
                    'monto'      => $total,
                    'metodo_pago' => $metodo,
                    'fecha'      => $checkout->toDateString(),
                    'nota'       => 'DEMO',
                ]);
            }
            return $reserva;
        };

        // ---------------------------------------------------------------
        // 5) RESERVAS — Ocupación de los ÚLTIMOS 7 DÍAS
        //    (estancias que abarcan cada uno de los últimos 7 días)
        // ---------------------------------------------------------------
        for ($i = 6; $i >= 0; $i--) {
            $dia = (clone $hoy)->subDays($i);
            // 3-6 estancias que ABARCAN este día (checkin <= dia < checkout) para
            // que cada barra del gráfico tenga altura visible y variada.
            $cuantas = random_int(3, 6);
            for ($c = 0; $c < $cuantas; $c++) {
                $estado = ['confirmada', 'checkin', 'checkout'][array_rand([0, 1, 2])];
                // Check-in 0-1 días antes del día objetivo, estancia de 2-4 noches:
                // así la reserva cubre el día con seguridad y suma en el gráfico.
                $checkin = $dia->copy()->subDays(random_int(0, 1));
                $crearReserva($checkin, random_int(2, 4), $estado);
            }
        }

        // ---------------------------------------------------------------
        // 6) RESERVAS del MES ACTUAL con TODOS los estados
        //    (para la dona "Reservas por Estado")
        // ---------------------------------------------------------------
        $estadosMes = ['pendiente', 'pendiente', 'confirmada', 'confirmada', 'confirmada',
                       'checkin', 'checkin', 'checkout', 'checkout', 'cancelada'];
        foreach ($estadosMes as $estado) {
            $dia = $hoy->copy()->startOfMonth()->addDays(random_int(0, min(27, $hoy->day + 20)));
            $crearReserva($dia, random_int(1, 4), $estado);
        }

        // ---------------------------------------------------------------
        // 7) CHECK-INS y CHECK-OUTS de HOY (tarjetas del dashboard)
        // ---------------------------------------------------------------
        for ($i = 0; $i < 3; $i++) {
            $crearReserva($hoy->copy(), random_int(1, 3), 'confirmada');  // llegadas de hoy
        }
        for ($i = 0; $i < 3; $i++) {
            $r = $crearReserva($hoy->copy()->subDays(random_int(2, 4)), 1, 'checkin', true);
            $r->update(['fecha_checkout' => $hoy->toDateString()]);        // salidas de hoy
        }

        // ---------------------------------------------------------------
        // 8) RESERVAS históricas (últimos 6 meses) → línea de ingresos
        // ---------------------------------------------------------------
        for ($m = 5; $m >= 1; $m--) {
            $mesBase = $hoy->copy()->startOfMonth()->subMonths($m);
            $cuantas = random_int(8, 14);
            for ($r = 0; $r < $cuantas; $r++) {
                $checkin = (clone $mesBase)->addDays(random_int(0, 26));
                $crearReserva($checkin, random_int(1, 5), 'checkout', true);
            }
        }

        // ---------------------------------------------------------------
        // 9) CAJA — 10 sesiones (una por día de los últimos 10 días)
        // ---------------------------------------------------------------
        for ($i = 9; $i >= 0; $i--) {
            $dia      = $hoy->copy()->subDays($i);
            $inicial  = 200.00;
            $esperado = round($inicial + random_int(300, 1500) + (random_int(0, 99) / 100), 2);
            if ($i === 0) {
                // La de hoy queda ABIERTA
                CajaSesion::create([
                    'empresa_id'   => $eid, 'user_id' => $admin?->id,
                    'monto_inicial' => $inicial, 'estado' => 'abierta',
                    'abierta_at'   => $dia->copy()->setTime(7, 0), 'notas' => 'DEMO caja del día',
                ]);
            } else {
                $final = round($esperado + (random_int(-30, 30) / 10), 2);
                CajaSesion::create([
                    'empresa_id'   => $eid, 'user_id' => $admin?->id,
                    'monto_inicial' => $inicial, 'monto_esperado' => $esperado,
                    'monto_final'  => $final, 'diferencia' => round($final - $esperado, 2),
                    'estado'       => 'cerrada',
                    'abierta_at'   => $dia->copy()->setTime(7, 0),
                    'cerrada_at'   => $dia->copy()->setTime(19, 30),
                    'notas'        => 'DEMO cierre de caja',
                ]);
            }
        }

        // ---------------------------------------------------------------
        // 10) TARIFAS DE TEMPORADA (10)
        // ---------------------------------------------------------------
        $anio = $hoy->year;
        $temporadas = [
            ['DEMO Feriado Largo', 'alta', "$anio-07-06", "$anio-07-09", 'porcentaje', 20],
            ['DEMO Fiestas Patrias', 'alta', "$anio-07-26", "$anio-07-31", 'porcentaje', 30],
            ['DEMO Aniversario Ciudad', 'especial', "$anio-08-15", "$anio-08-18", 'porcentaje', 15],
            ['DEMO Primavera', 'baja', "$anio-09-01", "$anio-09-30", 'porcentaje', -10],
            ['DEMO Día de la Canción', 'especial', "$anio-10-30", "$anio-10-31", 'fijo', 25],
            ['DEMO Todos los Santos', 'alta', "$anio-11-01", "$anio-11-02", 'porcentaje', 15],
            ['DEMO Black Friday', 'baja', "$anio-11-27", "$anio-11-29", 'porcentaje', -20],
            ['DEMO Navidad', 'alta', "$anio-12-22", "$anio-12-26", 'porcentaje', 35],
            ['DEMO Fin de Año', 'alta', "$anio-12-29", "$anio-12-31", 'porcentaje', 40],
            ['DEMO Verano', 'alta', ($anio + 1) . "-01-01", ($anio + 1) . "-02-28", 'porcentaje', 25],
        ];
        foreach ($temporadas as [$n, $tipo, $ini, $fin, $atipo, $aval]) {
            TarifaTemporada::create([
                'empresa_id' => $eid, 'nombre' => $n, 'tipo' => $tipo,
                'fecha_inicio' => $ini, 'fecha_fin' => $fin,
                'ajuste_tipo' => $atipo, 'ajuste_valor' => $aval, 'activo' => true,
            ]);
        }

        // ---------------------------------------------------------------
        // 11) RESUMEN por módulo (registros DEMO creados)
        // ---------------------------------------------------------------
        $resumen = [
            'Huéspedes'         => Huesped::where('empresa_id', $eid)->where('email', 'like', 'demo.%@correo.com')->count(),
            'Productos'         => Producto::where('empresa_id', $eid)->where('descripcion', 'DEMO')->count(),
            'Usuarios equipo'   => User::where('empresa_id', $eid)->where('email', 'like', 'demo.staff%@hospedaje.com')->count(),
            'Reservas'          => Reserva::where('empresa_id', $eid)->where('codigo', 'like', 'DEMO-%')->count(),
            'Facturas'          => Factura::where('empresa_id', $eid)->where('numero', 'like', 'DEMO-%')->count(),
            'Pagos'             => Pago::where('empresa_id', $eid)->where('nota', 'DEMO')->count(),
            'Sesiones de caja'  => CajaSesion::where('empresa_id', $eid)->where('notas', 'like', 'DEMO%')->count(),
            'Tarifas temporada' => TarifaTemporada::where('empresa_id', $eid)->where('nombre', 'like', 'DEMO %')->count(),
        ];
        $this->command?->info('DemoDashboardSeeder: datos de demostración cargados correctamente.');
        foreach ($resumen as $modulo => $n) {
            $this->command?->line(sprintf('  • %-20s %d registros', $modulo, $n));
        }
    }
}
