<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;

class CalendarioController extends Controller
{
    public function index()
    {
        return view('calendario.index');
    }

    public function eventos(Request $request)
    {
        $colores = [
            'pendiente'  => '#6c757d',
            'confirmada' => '#2563eb',
            'checkin'    => '#22a565',
            'checkout'   => '#16a2b8',
            'cancelada'  => '#e0524d',
        ];

        $query = Reserva::with(['huesped', 'habitacion'])
            ->where('estado', '!=', 'cancelada');

        if ($request->filled('start')) {
            $query->whereDate('fecha_checkout', '>=', $request->start);
        }
        if ($request->filled('end')) {
            $query->whereDate('fecha_checkin', '<=', $request->end);
        }

        $eventos = $query->get()->map(function (Reserva $r) use ($colores) {
            return [
                'id' => $r->id,
                'title' => 'Hab. '.($r->habitacion->numero ?? '?').' · '.($r->huesped->nombre_completo ?? ''),
                'start' => $r->fecha_checkin->toDateString(),
                // FullCalendar trata 'end' como exclusivo: sumamos 1 día para incluir el checkout
                'end' => $r->fecha_checkout->copy()->addDay()->toDateString(),
                'color' => $colores[$r->estado] ?? '#6c757d',
                'url' => route('reservas.show', $r),
                'extendedProps' => ['estado' => ucfirst($r->estado), 'codigo' => $r->codigo],
            ];
        });

        return response()->json($eventos);
    }
}
