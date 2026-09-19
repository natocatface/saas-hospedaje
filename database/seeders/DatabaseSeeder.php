<?php

namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Consumo;
use App\Models\PermisoRol;
use App\Models\Plan;
use App\Models\Reserva;
use App\Models\TarifaTemporada;
use App\Models\TipoHabitacion;
use App\Models\User;
use App\Support\Permisos;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Planes de suscripción ----------
        $planes = [
            ['nombre' => 'Free',        'slug' => 'free',        'precio_mensual' => 0,   'max_habitaciones' => 10,  'max_usuarios' => 2,  'permite_reportes' => true,  'permite_temporadas' => false, 'descripcion' => 'Ideal para empezar.'],
            ['nombre' => 'Pro',         'slug' => 'pro',         'precio_mensual' => 99,  'max_habitaciones' => 60,  'max_usuarios' => 10, 'permite_reportes' => true,  'permite_temporadas' => true,  'descripcion' => 'Para hoteles en crecimiento.'],
            ['nombre' => 'Empresarial', 'slug' => 'empresarial', 'precio_mensual' => 249, 'max_habitaciones' => null, 'max_usuarios' => null, 'permite_reportes' => true, 'permite_temporadas' => true, 'descripcion' => 'Sin límites para cadenas.'],
        ];
        foreach ($planes as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], $p + ['activo' => true]);
        }
        $planPro = Plan::where('slug', 'pro')->first();

        // ---------- Empresa demo (tenant) ----------
        $empresa = Empresa::updateOrCreate(['slug' => 'hotel-demo'], [
            'nombre' => 'Hotel Sistema Hospedaje',
            'ruc' => '20123456789',
            'email' => 'reservas@hospedaje.com',
            'telefono' => '(01) 555-1234',
            'direccion' => 'Av. Principal 123, Lima - Perú',
            'plan_id' => $planPro?->id,
            'activo' => true,
            'trial_ends_at' => null,
            'suscripcion_hasta' => Carbon::now()->addYear(),
        ]);
        $eid = $empresa->id;

        // ---------- Super administrador de la plataforma ----------
        User::updateOrCreate(['email' => 'superadmin@hospedaje.com'], [
            'empresa_id' => null, 'name' => 'Super Admin', 'telefono' => '900000000',
            'rol' => 'superadmin', 'activo' => true, 'password' => Hash::make('password'),
        ]);

        // ---------- Segunda empresa demo (para el panel de plataforma) ----------
        $planFree = Plan::where('slug', 'free')->first();
        $empresa2 = Empresa::updateOrCreate(['slug' => 'hostal-el-viajero'], [
            'nombre' => 'Hostal El Viajero',
            'ruc' => '20555666777',
            'email' => 'contacto@elviajero.com',
            'telefono' => '(01) 444-5566',
            'direccion' => 'Jr. Comercio 456, Cusco - Perú',
            'plan_id' => $planFree?->id,
            'activo' => true,
            'trial_ends_at' => Carbon::now()->addDays(18),
            'suscripcion_hasta' => null,
        ]);
        User::updateOrCreate(['email' => 'admin@elviajero.com'], [
            'empresa_id' => $empresa2->id, 'name' => 'Ana Torres', 'telefono' => '988111222',
            'rol' => 'admin', 'activo' => true, 'password' => Hash::make('password'),
        ]);
        foreach (['nombre_hotel' => 'Hostal El Viajero', 'simbolo_moneda' => 'S/', 'igv' => '18'] as $ck => $cv) {
            Configuracion::updateOrCreate(['empresa_id' => $empresa2->id, 'clave' => $ck], ['empresa_id' => $empresa2->id, 'valor' => $cv]);
        }
        foreach (Permisos::rolesEditables() as $rol2 => $lbl2) {
            foreach (Permisos::porDefecto($rol2) as $perm2) {
                PermisoRol::updateOrCreate(['empresa_id' => $empresa2->id, 'rol' => $rol2, 'permiso' => $perm2], ['empresa_id' => $empresa2->id]);
            }
        }

        // ---------- Usuarios (empresa demo principal) ----------
        User::updateOrCreate(['email' => 'admin@hospedaje.com'], [
            'empresa_id' => $eid, 'name' => 'Administrador', 'telefono' => '999000111',
            'rol' => 'admin', 'activo' => true, 'password' => Hash::make('password'),
        ]);
        User::updateOrCreate(['email' => 'recepcion@hospedaje.com'], [
            'empresa_id' => $eid, 'name' => 'Recepcionista', 'telefono' => '999000222',
            'rol' => 'recepcionista', 'activo' => true, 'password' => Hash::make('password'),
        ]);

        // ---------- Tipos de habitación ----------
        $tipos = [
            ['nombre' => 'Individual', 'descripcion' => '1 cama individual, ideal para viajeros solos.', 'capacidad' => 1, 'precio_base' => 90],
            ['nombre' => 'Doble',      'descripcion' => '2 camas o 1 matrimonial.',                       'capacidad' => 2, 'precio_base' => 140],
            ['nombre' => 'Matrimonial','descripcion' => '1 cama matrimonial, vista a la ciudad.',         'capacidad' => 2, 'precio_base' => 160],
            ['nombre' => 'Triple',     'descripcion' => '3 camas, ideal para familias pequeñas.',         'capacidad' => 3, 'precio_base' => 210],
            ['nombre' => 'Suite',      'descripcion' => 'Suite de lujo con sala y jacuzzi.',              'capacidad' => 4, 'precio_base' => 350],
        ];
        $tipoModels = [];
        foreach ($tipos as $t) {
            $tipoModels[] = TipoHabitacion::updateOrCreate(
                ['empresa_id' => $eid, 'nombre' => $t['nombre']],
                $t + ['empresa_id' => $eid]
            );
        }

        // ---------- 42 Habitaciones ----------
        $estadosCiclo = ['disponible','disponible','disponible','disponible','disponible',
                         'disponible','disponible','disponible','disponible','limpieza'];
        $contador = 0;
        for ($piso = 1; $piso <= 4; $piso++) {
            for ($n = 1; $n <= 11 && $contador < 42; $n++) {
                $contador++;
                $tipo = $tipoModels[array_rand($tipoModels)];
                $numero = $piso . str_pad((string) $n, 2, '0', STR_PAD_LEFT);
                Habitacion::updateOrCreate(['empresa_id' => $eid, 'numero' => $numero], [
                    'empresa_id' => $eid,
                    'tipo_habitacion_id' => $tipo->id,
                    'piso' => $piso,
                    'precio_noche' => $tipo->precio_base,
                    'capacidad' => $tipo->capacidad,
                    'estado' => $estadosCiclo[array_rand($estadosCiclo)],
                    'descripcion' => 'Habitación ' . $tipo->nombre . ' en piso ' . $piso . '.',
                ]);
            }
        }
        Habitacion::where('empresa_id', $eid)->limit(4)->update(['estado' => 'ocupada']);

        // ---------- Huéspedes ----------
        $nombres = ['Carlos','María','José','Ana','Luis','Rosa','Pedro','Carmen','Jorge','Lucía','Miguel','Elena','Raúl','Sofía','Diego','Valeria'];
        $apellidos = ['Quispe','Mamani','Flores','Rojas','Vargas','Torres','Huamán','Castro','Ramos','Díaz','Chávez','Sánchez'];
        $huespedes = [];
        for ($i = 0; $i < 25; $i++) {
            $huespedes[] = Huesped::create([
                'empresa_id' => $eid,
                'nombres' => $nombres[array_rand($nombres)],
                'apellidos' => $apellidos[array_rand($apellidos)] . ' ' . $apellidos[array_rand($apellidos)],
                'documento_tipo' => 'DNI',
                'documento_numero' => (string) random_int(40000000, 79999999),
                'telefono' => '9' . random_int(10000000, 99999999),
                'email' => 'huesped' . $i . '@correo.com',
                'nacionalidad' => 'Peru',
                'direccion' => 'Av. Siempre Viva ' . random_int(100, 999),
            ]);
        }

        // ---------- Reservas + Facturas ----------
        $habitaciones = Habitacion::where('empresa_id', $eid)->get();
        $admin = User::where('email', 'admin@hospedaje.com')->first();
        $correlativo = 1;
        $facturaNum = 1;

        for ($m = 5; $m >= 0; $m--) {
            $mesBase = Carbon::now()->subMonths($m)->startOfMonth();
            $reservasMes = random_int(8, 22);
            for ($r = 0; $r < $reservasMes; $r++) {
                $habitacion = $habitaciones->random();
                $huesped = $huespedes[array_rand($huespedes)];
                $checkin = (clone $mesBase)->addDays(random_int(0, 26));
                $noches = random_int(1, 5);
                $checkout = (clone $checkin)->addDays($noches);
                $tarifa = (float) $habitacion->precio_noche;
                $total = $tarifa * $noches;

                $estado = 'checkout';
                if ($m === 0) {
                    $estado = ['confirmada', 'checkin', 'pendiente', 'checkout'][array_rand([0,1,2,3])];
                }

                $reserva = Reserva::create([
                    'empresa_id' => $eid,
                    'codigo' => 'RSV-' . str_pad((string) $correlativo++, 5, '0', STR_PAD_LEFT),
                    'huesped_id' => $huesped->id,
                    'habitacion_id' => $habitacion->id,
                    'user_id' => $admin?->id,
                    'fecha_checkin' => $checkin->toDateString(),
                    'fecha_checkout' => $checkout->toDateString(),
                    'noches' => $noches,
                    'adultos' => random_int(1, 2),
                    'ninos' => random_int(0, 2),
                    'tarifa_noche' => $tarifa,
                    'total' => $total,
                    'estado' => $estado,
                ]);

                $subtotal = round($total / 1.18, 2);
                $igv = round($total - $subtotal, 2);
                $pagada = ! in_array($estado, ['pendiente']) && random_int(0, 100) > 25;
                $metodo = ['efectivo','tarjeta','yape','transferencia'][array_rand([0,1,2,3])];
                $factura = Factura::create([
                    'empresa_id' => $eid,
                    'numero' => 'F001-' . str_pad((string) $facturaNum++, 6, '0', STR_PAD_LEFT),
                    'reserva_id' => $reserva->id,
                    'fecha' => $checkout->toDateString(),
                    'subtotal' => $subtotal,
                    'igv' => $igv,
                    'total' => $total,
                    'estado' => $pagada ? 'pagada' : 'pendiente',
                    'metodo_pago' => $pagada ? $metodo : null,
                ]);
                if ($pagada) {
                    Pago::create([
                        'empresa_id' => $eid,
                        'factura_id' => $factura->id,
                        'reserva_id' => $reserva->id,
                        'user_id' => $admin?->id,
                        'monto' => $total,
                        'metodo_pago' => $metodo,
                        'fecha' => $checkout->toDateString(),
                    ]);
                }
            }
        }

        // ---------- Catálogo de productos ----------
        $productos = [
            ['nombre' => 'Agua mineral 600ml', 'categoria' => 'minibar',     'precio' => 5],
            ['nombre' => 'Gaseosa lata',       'categoria' => 'minibar',     'precio' => 6],
            ['nombre' => 'Cerveza',            'categoria' => 'minibar',     'precio' => 10],
            ['nombre' => 'Snack / galletas',   'categoria' => 'minibar',     'precio' => 7],
            ['nombre' => 'Desayuno buffet',    'categoria' => 'restaurante', 'precio' => 25],
            ['nombre' => 'Almuerzo ejecutivo', 'categoria' => 'restaurante', 'precio' => 35],
            ['nombre' => 'Lavandería (kg)',    'categoria' => 'lavanderia',  'precio' => 15],
            ['nombre' => 'Late check-out',     'categoria' => 'servicios',   'precio' => 40],
        ];
        $prodModels = [];
        foreach ($productos as $p) {
            $prodModels[] = Producto::updateOrCreate(
                ['empresa_id' => $eid, 'nombre' => $p['nombre']],
                $p + ['empresa_id' => $eid, 'activo' => true]
            );
        }

        // ---------- Consumos demo (mes actual) ----------
        $reservasMesActual = Reserva::where('empresa_id', $eid)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->inRandomOrder()->limit(10)->get();
        foreach ($reservasMesActual as $reserva) {
            $items = random_int(1, 3);
            for ($k = 0; $k < $items; $k++) {
                $prod = $prodModels[array_rand($prodModels)];
                $cant = random_int(1, 3);
                Consumo::create([
                    'empresa_id' => $eid,
                    'reserva_id' => $reserva->id,
                    'producto_id' => $prod->id,
                    'descripcion' => $prod->nombre,
                    'cantidad' => $cant,
                    'precio_unit' => $prod->precio,
                    'subtotal' => $cant * (float) $prod->precio,
                    'fecha' => Carbon::now()->toDateString(),
                    'user_id' => $admin?->id,
                ]);
            }
            // Recalcular la factura con hospedaje + consumos
            $factura = $reserva->factura;
            if ($factura && $factura->estado !== 'anulada') {
                $totalCuenta = (float) $reserva->total + (float) $reserva->consumos()->sum('subtotal');
                $sub = round($totalCuenta / 1.18, 2);
                $factura->update([
                    'total' => $totalCuenta,
                    'subtotal' => $sub,
                    'igv' => round($totalCuenta - $sub, 2),
                ]);
                $factura->actualizarEstadoPorPagos();
            }
        }

        // ---------- Tarifas de temporada ----------
        $anio = Carbon::now()->year;
        $temporadas = [
            ['nombre' => 'Temporada Alta - Fiestas Patrias', 'tipo' => 'alta', 'fecha_inicio' => "$anio-07-26", 'fecha_fin' => "$anio-07-31", 'ajuste_tipo' => 'porcentaje', 'ajuste_valor' => 25],
            ['nombre' => 'Temporada Alta - Fin de Año',       'tipo' => 'alta', 'fecha_inicio' => "$anio-12-20", 'fecha_fin' => "$anio-12-31", 'ajuste_tipo' => 'porcentaje', 'ajuste_valor' => 30],
            ['nombre' => 'Temporada Baja - Otoño',            'tipo' => 'baja', 'fecha_inicio' => "$anio-05-01", 'fecha_fin' => "$anio-06-30", 'ajuste_tipo' => 'porcentaje', 'ajuste_valor' => -10],
        ];
        foreach ($temporadas as $t) {
            TarifaTemporada::updateOrCreate(
                ['empresa_id' => $eid, 'nombre' => $t['nombre']],
                $t + ['empresa_id' => $eid, 'activo' => true]
            );
        }

        // ---------- Configuración ----------
        $config = [
            'nombre_hotel' => 'Hotel Sistema Hospedaje',
            'ruc' => '20123456789',
            'direccion' => 'Av. Principal 123, Lima - Perú',
            'telefono' => '(01) 555-1234',
            'email' => 'reservas@hospedaje.com',
            'moneda' => 'PEN',
            'simbolo_moneda' => 'S/',
            'igv' => '18',
            'checkin_hora' => '14:00',
            'checkout_hora' => '12:00',
        ];
        foreach ($config as $clave => $valor) {
            Configuracion::updateOrCreate(
                ['empresa_id' => $eid, 'clave' => $clave],
                ['empresa_id' => $eid, 'valor' => $valor]
            );
        }

        // ---------- Permisos por defecto ----------
        foreach (Permisos::rolesEditables() as $rol => $label) {
            foreach (Permisos::porDefecto($rol) as $permiso) {
                PermisoRol::updateOrCreate(
                    ['empresa_id' => $eid, 'rol' => $rol, 'permiso' => $permiso],
                    ['empresa_id' => $eid]
                );
            }
        }
    }
}
