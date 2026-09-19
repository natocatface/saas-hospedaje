<?php

namespace App\Http\Controllers;

use App\Models\TarifaTemporada;
use Illuminate\Http\Request;

class TarifaTemporadaController extends Controller
{
    public function index()
    {
        $tarifas = TarifaTemporada::orderByDesc('fecha_inicio')->paginate(15);
        return view('tarifas.index', compact('tarifas'));
    }

    public function create()
    {
        return view('tarifas.form', ['tarifa' => new TarifaTemporada()]);
    }

    public function store(Request $request)
    {
        TarifaTemporada::create($this->validar($request));
        return redirect()->route('tarifas.index')->with('ok', 'Temporada creada.');
    }

    public function edit(TarifaTemporada $tarifa)
    {
        return view('tarifas.form', compact('tarifa'));
    }

    public function update(Request $request, TarifaTemporada $tarifa)
    {
        $tarifa->update($this->validar($request));
        return redirect()->route('tarifas.index')->with('ok', 'Temporada actualizada.');
    }

    public function destroy(TarifaTemporada $tarifa)
    {
        $tarifa->delete();
        return back()->with('ok', 'Temporada eliminada.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:150',
            'tipo' => 'required|in:alta,baja,especial',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'ajuste_tipo' => 'required|in:porcentaje,fijo',
            'ajuste_valor' => 'required|numeric',
            'activo' => 'required|boolean',
        ]);
    }
}
