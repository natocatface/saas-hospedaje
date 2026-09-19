<?php

namespace App\Http\Controllers;

use App\Models\CajaSesion;
use App\Models\Factura;
use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function store(Request $request, Factura $factura)
    {
        abort_if($factura->estado === 'anulada', 422, 'La factura está anulada.');

        $saldo = $factura->saldo();
        $data = $request->validate([
            'monto' => "required|numeric|min:0.01|max:$saldo",
            'metodo_pago' => 'required|in:efectivo,tarjeta,yape,transferencia',
            'nota' => 'nullable|string|max:255',
        ], [
            'monto.max' => "El monto no puede superar el saldo pendiente (S/ ".number_format($saldo, 2).").",
        ]);

        $sesion = CajaSesion::where('estado', 'abierta')->latest('id')->first();

        Pago::create([
            'factura_id' => $factura->id,
            'reserva_id' => $factura->reserva_id,
            'caja_sesion_id' => $sesion?->id,
            'user_id' => $request->user()->id,
            'monto' => $data['monto'],
            'metodo_pago' => $data['metodo_pago'],
            'fecha' => now()->toDateString(),
            'nota' => $data['nota'] ?? null,
        ]);

        $factura->update(['metodo_pago' => $data['metodo_pago']]);
        $factura->actualizarEstadoPorPagos();

        $msg = $factura->estaPagada()
            ? "Pago registrado. Factura {$factura->numero} cancelada en su totalidad."
            : "Pago registrado. Saldo pendiente: S/ ".number_format($factura->saldo(), 2).".";

        return back()->with('ok', $msg);
    }

    public function destroy(Factura $factura, Pago $pago)
    {
        abort_unless($pago->factura_id === $factura->id, 404);
        $pago->delete();
        $factura->actualizarEstadoPorPagos();

        return back()->with('ok', 'Pago eliminado.');
    }
}
