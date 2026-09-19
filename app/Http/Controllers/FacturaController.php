<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Factura;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    public function index(Request $request)
    {
        $query = Factura::with('reserva.huesped');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('numero', 'like', "%$q%")
                ->orWhereHas('reserva.huesped', fn ($w) =>
                    $w->where('nombres', 'like', "%$q%")->orWhere('apellidos', 'like', "%$q%"));
        }
        if ($request->filled('mes')) {
            $query->whereMonth('fecha', (int) $request->mes);
        }

        $facturas = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $resumen = [
            'total_pagado'    => (float) Factura::where('estado', 'pagada')->sum('total'),
            'total_pendiente' => (float) Factura::where('estado', 'pendiente')->sum('total'),
            'n_pendientes'    => Factura::where('estado', 'pendiente')->count(),
        ];

        return view('facturacion.index', compact('facturas', 'resumen'));
    }

    public function show(Factura $factura)
    {
        $factura->load('reserva.huesped', 'reserva.habitacion.tipo', 'pagos.usuario');
        $feConfig = \App\Models\FacturacionConfig::actual();
        return view('facturacion.show', compact('factura', 'feConfig'));
    }

    public function imprimir(Factura $factura)
    {
        $factura->load('reserva.huesped', 'reserva.habitacion.tipo', 'reserva.consumos.producto');
        $config = [
            'nombre_hotel' => Configuracion::get('nombre_hotel', 'Hotel Sistema Hospedaje'),
            'ruc' => Configuracion::get('ruc', ''),
            'direccion' => Configuracion::get('direccion', ''),
            'telefono' => Configuracion::get('telefono', ''),
        ];
        return view('facturacion.imprimir', compact('factura', 'config'));
    }

    public function pagar(Request $request, Factura $factura)
    {
        $request->validate(['metodo_pago' => 'required|in:efectivo,tarjeta,yape,transferencia']);
        $factura->update(['estado' => 'pagada', 'metodo_pago' => $request->metodo_pago]);

        return back()->with('ok', "Factura {$factura->numero} marcada como pagada.");
    }

    public function anular(Factura $factura)
    {
        $factura->update(['estado' => 'anulada']);
        return back()->with('ok', "Factura {$factura->numero} anulada.");
    }
}
