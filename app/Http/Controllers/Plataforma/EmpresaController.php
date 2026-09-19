<?php

namespace App\Http\Controllers\Plataforma;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Habitacion;
use App\Models\Plan;
use App\Models\Reserva;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $query = Empresa::with('plan')->withCount('usuarios');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%$q%")->orWhere('ruc', 'like', "%$q%")->orWhere('email', 'like', "%$q%");
            });
        }
        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activas');
        }

        $empresas = $query->latest()->paginate(15)->withQueryString();

        return view('plataforma.empresas.index', compact('empresas'));
    }

    public function show(Empresa $empresa)
    {
        $empresa->load('plan', 'usuarios', 'suscripcionPagos');

        $stats = [
            'habitaciones' => Habitacion::withoutGlobalScopes()->where('empresa_id', $empresa->id)->count(),
            'reservas' => Reserva::withoutGlobalScopes()->where('empresa_id', $empresa->id)->count(),
            'usuarios' => $empresa->usuarios->count(),
        ];
        $planes = Plan::orderBy('precio_mensual')->get();

        return view('plataforma.empresas.show', compact('empresa', 'stats', 'planes'));
    }

    public function toggle(Empresa $empresa)
    {
        $empresa->update(['activo' => ! $empresa->activo]);
        $msg = $empresa->activo ? 'Empresa activada.' : 'Empresa suspendida.';

        return back()->with('ok', $msg);
    }

    public function cambiarPlan(Request $request, Empresa $empresa)
    {
        $request->validate(['plan_id' => 'required|exists:planes,id']);
        $empresa->update(['plan_id' => $request->plan_id]);

        return back()->with('ok', 'Plan de la empresa actualizado.');
    }
}
