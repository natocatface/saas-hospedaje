<?php

namespace App\Http\Controllers;

use App\Models\Huesped;
use Illuminate\Http\Request;

class HuespedController extends Controller
{
    public function index(Request $request)
    {
        $query = Huesped::query();
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('nombres', 'like', "%$q%")
                  ->orWhere('apellidos', 'like', "%$q%")
                  ->orWhere('documento_numero', 'like', "%$q%");
            });
        }
        $huespedes = $query->orderByDesc('id')->paginate(12)->withQueryString();

        return view('huespedes.index', compact('huespedes'));
    }

    public function create()
    {
        return view('huespedes.form', ['huesped' => new Huesped()]);
    }

    public function store(Request $request)
    {
        Huesped::create($this->validar($request));
        return redirect()->route('huespedes.index')->with('ok', 'Huésped registrado.');
    }

    public function edit(Huesped $huesped)
    {
        return view('huespedes.form', compact('huesped'));
    }

    public function update(Request $request, Huesped $huesped)
    {
        $huesped->update($this->validar($request));
        return redirect()->route('huespedes.index')->with('ok', 'Huésped actualizado.');
    }

    public function destroy(Huesped $huesped)
    {
        if ($huesped->reservas()->exists()) {
            return back()->with('error', 'No se puede eliminar: el huésped tiene reservas.');
        }
        $huesped->delete();
        return back()->with('ok', 'Huésped eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombres' => 'required|string|max:120',
            'apellidos' => 'required|string|max:120',
            'documento_tipo' => 'required|in:DNI,CE,Pasaporte,RUC',
            'documento_numero' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:120',
            'nacionalidad' => 'required|string|max:60',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'notas' => 'nullable|string',
        ]);
    }
}
