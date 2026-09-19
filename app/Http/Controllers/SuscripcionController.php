<?php

namespace App\Http\Controllers;

use App\Models\SuscripcionPago;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SuscripcionController extends Controller
{
    public function renovar(Request $request)
    {
        $empresa = $request->user()->empresa;
        $empresa->load('plan', 'suscripcionPagos');

        return view('suscripcion.renovar', compact('empresa'));
    }

    public function pagar(Request $request)
    {
        $request->validate(['metodo' => 'required|in:tarjeta,yape,transferencia']);

        $empresa = $request->user()->empresa;
        $plan = $empresa->plan;
        $precio = (float) ($plan?->precio_mensual ?? 0);

        $inicio = $empresa->suscripcionVigente()
            ? $empresa->suscripcion_hasta->copy()->addDay()
            : Carbon::today();
        $fin = $inicio->copy()->addMonth();

        SuscripcionPago::create([
            'empresa_id' => $empresa->id,
            'plan_id' => $plan?->id,
            'plan_nombre' => $plan?->nombre ?? 'Plan',
            'monto' => $precio,
            'periodo_inicio' => $inicio->toDateString(),
            'periodo_fin' => $fin->toDateString(),
            'fecha_pago' => Carbon::today()->toDateString(),
            'metodo' => $request->metodo,
            'estado' => 'pagado',
        ]);

        $empresa->update([
            'suscripcion_hasta' => $fin->toDateString(),
            'activo' => true,
        ]);

        return redirect()->route('dashboard')->with('ok', 'Suscripción renovada hasta el '.$fin->format('d/m/Y').'.');
    }
}
