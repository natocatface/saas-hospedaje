<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    private array $campos = [
        'nombre_hotel', 'ruc', 'direccion', 'telefono', 'email',
        'simbolo_moneda', 'igv', 'checkin_hora', 'checkout_hora',
    ];

    public function edit()
    {
        $config = Configuracion::pluck('valor', 'clave')->toArray();
        return view('configuracion.edit', compact('config'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nombre_hotel' => 'required|string|max:150',
            'ruc' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:120',
            'simbolo_moneda' => 'required|string|max:5',
            'igv' => 'required|numeric|min:0|max:100',
            'checkin_hora' => 'nullable|string|max:5',
            'checkout_hora' => 'nullable|string|max:5',
        ]);

        foreach ($this->campos as $campo) {
            Configuracion::updateOrCreate(['clave' => $campo], ['valor' => $data[$campo] ?? null]);
        }

        return back()->with('ok', 'Configuración guardada.');
    }
}
