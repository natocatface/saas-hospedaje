<?php

namespace App\Http\Controllers;

use App\Models\CajaSesion;
use App\Models\Pago;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function index()
    {
        $sesion = CajaSesion::where('estado', 'abierta')->latest('id')->first();

        $movimientos = collect();
        $resumen = ['efectivo' => 0, 'tarjeta' => 0, 'yape' => 0, 'transferencia' => 0, 'total' => 0];

        if ($sesion) {
            $movimientos = Pago::with(['factura', 'usuario'])
                ->where('caja_sesion_id', $sesion->id)
                ->orderByDesc('id')->get();

            foreach ($movimientos as $p) {
                $resumen[$p->metodo_pago] = ($resumen[$p->metodo_pago] ?? 0) + (float) $p->monto;
                $resumen['total'] += (float) $p->monto;
            }
        }

        $historial = CajaSesion::with('usuario')->where('estado', 'cerrada')
            ->orderByDesc('id')->limit(10)->get();

        return view('caja.index', compact('sesion', 'movimientos', 'resumen', 'historial'));
    }

    public function abrir(Request $request)
    {
        $request->validate(['monto_inicial' => 'required|numeric|min:0']);

        if (CajaSesion::where('estado', 'abierta')->exists()) {
            return back()->with('error', 'Ya existe una caja abierta. Ciérrala antes de abrir otra.');
        }

        CajaSesion::create([
            'user_id' => $request->user()->id,
            'monto_inicial' => $request->monto_inicial,
            'estado' => 'abierta',
            'abierta_at' => now(),
        ]);

        return back()->with('ok', 'Caja abierta correctamente.');
    }

    public function cerrar(Request $request, CajaSesion $caja)
    {
        $request->validate(['monto_final' => 'required|numeric|min:0']);
        abort_unless($caja->estado === 'abierta', 404);

        $esperado = $caja->efectivoEsperado();
        $caja->update([
            'monto_esperado' => $esperado,
            'monto_final' => $request->monto_final,
            'diferencia' => round((float) $request->monto_final - $esperado, 2),
            'estado' => 'cerrada',
            'cerrada_at' => now(),
            'notas' => $request->notas,
        ]);

        return redirect()->route('caja')->with('ok', 'Caja cerrada. Arqueo registrado.');
    }
}
