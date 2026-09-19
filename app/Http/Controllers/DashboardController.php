<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();
        $user = auth()->user();

        $totalHab       = Habitacion::count();
        $disponibles    = Habitacion::where('estado', 'disponible')->count();
        $ocupadas       = Habitacion::where('estado', 'ocupada')->count();
        $mantenimiento  = Habitacion::where('estado', 'mantenimiento')->count();
        $ocupacionPct   = $totalHab > 0 ? round($ocupadas / $totalHab * 100, 1) : 0;

        $checkinsHoy  = Reserva::whereDate('fecha_checkin', $hoy)->whereIn('estado', ['confirmada', 'checkin', 'pendiente'])->count();
        $checkoutsHoy = Reserva::whereDate('fecha_checkout', $hoy)->whereIn('estado', ['checkin', 'checkout'])->count();
        $totalHuespedes = Huesped::count();
        $reservasMes = Reserva::whereMonth('created_at', $hoy->month)->whereYear('created_at', $hoy->year)->count();

        $ingresosMes = (float) Factura::where('estado', 'pagada')
            ->whereMonth('fecha', $hoy->month)->whereYear('fecha', $hoy->year)->sum('total');
        $facturasPendientes = Factura::where('estado', 'pendiente')->count();

        // Ocupación últimos 7 días
        $labels7 = [];
        $data7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = (clone $hoy)->subDays($i);
            $labels7[] = $dia->format('d/m');
            $data7[] = Reserva::whereDate('fecha_checkin', '<=', $dia)
                ->whereDate('fecha_checkout', '>', $dia)
                ->whereIn('estado', ['confirmada', 'checkin', 'checkout'])
                ->count();
        }

        // Ingresos últimos 6 meses
        $labels6 = [];
        $data6 = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = (clone $hoy)->startOfMonth()->subMonths($i);
            $labels6[] = ucfirst($mes->locale('es')->isoFormat('MMM'));
            $data6[] = (float) Factura::where('estado', 'pagada')
                ->whereMonth('fecha', $mes->month)
                ->whereYear('fecha', $mes->year)
                ->sum('total');
        }

        $proximas = Reserva::with(['huesped', 'habitacion'])
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->whereDate('fecha_checkin', '>=', $hoy)
            ->orderBy('fecha_checkin')
            ->limit(6)->get();

        // Indicadores hoteleros del mes (ADR / RevPAR / ocupación)
        $iniMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();
        $resMes = Reserva::where('estado', '!=', 'cancelada')->whereBetween('fecha_checkin', [$iniMes, $finMes]);
        $ingresoHabMes = (float) (clone $resMes)->sum('total');
        $nochesMes = (int) (clone $resMes)->sum('noches');
        $capMes = max(1, $totalHab * $hoy->daysInMonth);
        $adrMes = $nochesMes > 0 ? round($ingresoHabMes / $nochesMes, 2) : 0;
        $revparMes = round($ingresoHabMes / $capMes, 2);
        $ocupMes = round($nochesMes / $capMes * 100, 1);

        // Onboarding (primeros pasos)
        $onboarding = [
            ['t' => 'Crea tus habitaciones', 'done' => Habitacion::exists(), 'url' => $user->puede('habitaciones.gestionar') ? route('habitaciones.create') : null, 'icon' => 'door-closed'],
            ['t' => 'Registra productos y servicios', 'done' => Producto::exists(), 'url' => $user->puede('productos.gestionar') ? route('productos.create') : null, 'icon' => 'box-seam'],
            ['t' => 'Registra tu primer huésped', 'done' => Huesped::exists(), 'url' => $user->puede('huespedes.gestionar') ? route('huespedes.create') : null, 'icon' => 'people'],
            ['t' => 'Crea tu primera reserva', 'done' => Reserva::exists(), 'url' => $user->puede('reservas.gestionar') ? route('reservas.create') : null, 'icon' => 'journal-check'],
            ['t' => 'Invita a tu equipo', 'done' => User::where('empresa_id', $user->empresa_id)->count() > 1, 'url' => $user->esAdmin() ? route('usuarios.create') : null, 'icon' => 'person-plus'],
        ];
        $onbHechos = count(array_filter($onboarding, fn ($p) => $p['done']));
        $onbTotal = count($onboarding);

        // Distribución de estado de habitaciones (dona)
        $estadosHab = ['disponible' => 0, 'ocupada' => 0, 'mantenimiento' => 0, 'limpieza' => 0];
        foreach (Habitacion::selectRaw('estado, COUNT(*) as c')->groupBy('estado')->pluck('c', 'estado') as $est => $c) {
            $estadosHab[$est] = (int) $c;
        }
        $habEstadoLabels = ['Disponibles', 'Ocupadas', 'Mantenimiento', 'Limpieza'];
        $habEstadoData   = array_values($estadosHab);
        $habEstadoColors = ['#22a565', '#e0524d', '#e3a72f', '#2563eb'];

        // Reservas por estado (mes actual)
        $estadosRes = ['pendiente' => 0, 'confirmada' => 0, 'checkin' => 0, 'checkout' => 0, 'cancelada' => 0];
        foreach (Reserva::whereMonth('fecha_checkin', $hoy->month)->whereYear('fecha_checkin', $hoy->year)
                    ->selectRaw('estado, COUNT(*) as c')->groupBy('estado')->pluck('c', 'estado') as $est => $c) {
            if (array_key_exists($est, $estadosRes)) {
                $estadosRes[$est] = (int) $c;
            }
        }
        $resEstadoLabels = ['Pendientes', 'Confirmadas', 'Check-in', 'Check-out', 'Canceladas'];
        $resEstadoData   = array_values($estadosRes);
        $resEstadoColors = ['#6c757d', '#2563eb', '#22a565', '#0fb6a8', '#e0524d'];

        return view('dashboard', compact(
            'totalHab', 'disponibles', 'ocupadas', 'mantenimiento', 'ocupacionPct',
            'checkinsHoy', 'checkoutsHoy', 'totalHuespedes', 'reservasMes',
            'ingresosMes', 'facturasPendientes',
            'labels7', 'data7', 'labels6', 'data6', 'proximas',
            'adrMes', 'revparMes', 'ocupMes',
            'onboarding', 'onbHechos', 'onbTotal',
            'habEstadoLabels', 'habEstadoData', 'habEstadoColors',
            'resEstadoLabels', 'resEstadoData', 'resEstadoColors'
        ));
    }
}
