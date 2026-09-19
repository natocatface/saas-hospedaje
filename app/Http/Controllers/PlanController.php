<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $empresa = $request->user()->empresa;
        $planes = Plan::where('activo', true)->orderBy('precio_mensual')->get();

        $uso = $empresa ? [
            'habitaciones' => $empresa->numHabitaciones(),
            'usuarios' => $empresa->numUsuarios(),
        ] : ['habitaciones' => 0, 'usuarios' => 0];

        return view('plan.index', compact('empresa', 'planes', 'uso'));
    }

    public function cambiar(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:planes,id']);
        $empresa = $request->user()->empresa;
        abort_unless($empresa, 404);

        $empresa->update(['plan_id' => $request->plan_id]);

        return back()->with('ok', 'Plan actualizado correctamente.');
    }
}
