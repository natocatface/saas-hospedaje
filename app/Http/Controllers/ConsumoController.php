<?php

namespace App\Http\Controllers;

use App\Models\Consumo;
use App\Models\Producto;
use App\Models\Reserva;
use Illuminate\Http\Request;

class ConsumoController extends Controller
{
    public function store(Request $request, Reserva $reserva)
    {
        $data = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1|max:99',
        ]);

        $producto = Producto::findOrFail($data['producto_id']);
        $subtotal = round((float) $producto->precio * $data['cantidad'], 2);

        Consumo::create([
            'reserva_id' => $reserva->id,
            'producto_id' => $producto->id,
            'descripcion' => $producto->nombre,
            'cantidad' => $data['cantidad'],
            'precio_unit' => $producto->precio,
            'subtotal' => $subtotal,
            'fecha' => now()->toDateString(),
            'user_id' => $request->user()->id,
        ]);

        $this->recalcularFactura($reserva);

        return back()->with('ok', "Consumo agregado: {$producto->nombre} x{$data['cantidad']}.");
    }

    public function destroy(Reserva $reserva, Consumo $consumo)
    {
        abort_unless($consumo->reserva_id === $reserva->id, 404);
        $consumo->delete();
        $this->recalcularFactura($reserva);

        return back()->with('ok', 'Consumo eliminado.');
    }

    /** Recalcula la factura: hospedaje + consumos (IGV 18%). */
    private function recalcularFactura(Reserva $reserva): void
    {
        $factura = $reserva->factura;
        if (! $factura || $factura->estado === 'anulada') {
            return;
        }

        $total = round((float) $reserva->total + $reserva->totalConsumos(), 2);
        $subtotal = round($total / 1.18, 2);

        $factura->update([
            'total' => $total,
            'subtotal' => $subtotal,
            'igv' => round($total - $subtotal, 2),
        ]);
        $factura->actualizarEstadoPorPagos();
    }
}
