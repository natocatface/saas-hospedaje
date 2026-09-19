<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    public function index()
    {
        $habitaciones = Habitacion::with('tipo')->orderBy('numero')->get()->groupBy('estado');

        $resumen = [
            'disponible'    => Habitacion::where('estado', 'disponible')->count(),
            'ocupada'       => Habitacion::where('estado', 'ocupada')->count(),
            'limpieza'      => Habitacion::where('estado', 'limpieza')->count(),
            'mantenimiento' => Habitacion::where('estado', 'mantenimiento')->count(),
        ];

        return view('housekeeping.index', compact('habitaciones', 'resumen'));
    }

    public function actualizar(Request $request, Habitacion $habitacion)
    {
        $request->validate(['estado' => 'required|in:disponible,ocupada,mantenimiento,limpieza']);
        $habitacion->update(['estado' => $request->estado]);

        return back()->with('ok', "Habitación {$habitacion->numero} marcada como {$request->estado}.");
    }
}
