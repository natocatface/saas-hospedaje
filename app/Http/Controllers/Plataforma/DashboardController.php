<?php

namespace App\Http\Controllers\Plataforma;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Habitacion;
use App\Models\Plan;
use App\Models\Reserva;
use App\Models\SuscripcionPago;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmpresas = Empresa::count();
        $activas       = Empresa::where('activo', true)->count();
        $suspendidas   = Empresa::where('activo', false)->count();

        $planes = Plan::orderBy('precio_mensual')->get();
        $porPlan = Empresa::selectRaw('plan_id, count(*) as c')->groupBy('plan_id')->pluck('c', 'plan_id');

        $totalUsuarios     = User::whereNotNull('empresa_id')->count();
        $totalHabitaciones = Habitacion::count();
        $totalReservas     = Reserva::count();

        $ingresoSuscripciones = (float) SuscripcionPago::sum('monto');
        $mrr = (float) Empresa::where('empresas.activo', true)
            ->join('planes', 'planes.id', '=', 'empresas.plan_id')
            ->sum('planes.precio_mensual');

        $recientes = Empresa::with('plan')->latest()->limit(8)->get();

        return view('plataforma.dashboard', compact(
            'totalEmpresas', 'activas', 'suspendidas', 'planes', 'porPlan',
            'totalUsuarios', 'totalHabitaciones', 'totalReservas',
            'ingresoSuscripciones', 'mrr', 'recientes'
        ));
    }
}
