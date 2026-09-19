<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HabitacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Habitacion::with('tipo');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('q')) {
            $query->where('numero', 'like', '%'.$request->q.'%');
        }

        $habitaciones = $query->orderBy('numero')->paginate(12)->withQueryString();
        $estados = Habitacion::ESTADOS;

        return view('habitaciones.index', compact('habitaciones', 'estados'));
    }

    public function create()
    {
        $tipos = TipoHabitacion::where('activo', true)->orderBy('nombre')->get();
        return view('habitaciones.form', ['habitacion' => new Habitacion(), 'tipos' => $tipos]);
    }

    public function store(Request $request)
    {
        $empresa = $request->user()->empresa;
        if ($empresa && ! $empresa->puedeAgregarHabitacion()) {
            return back()->withInput()->with('error',
                "Tu plan ({$empresa->plan?->nombre}) permite hasta {$empresa->plan?->max_habitaciones} habitaciones. Mejora tu plan para agregar más.");
        }

        $data = $this->validar($request);
        Habitacion::create($data);

        return redirect()->route('habitaciones.index')->with('ok', 'Habitación creada correctamente.');
    }

    public function edit(Habitacion $habitacione)
    {
        $tipos = TipoHabitacion::where('activo', true)->orderBy('nombre')->get();
        return view('habitaciones.form', ['habitacion' => $habitacione, 'tipos' => $tipos]);
    }

    public function update(Request $request, Habitacion $habitacione)
    {
        $data = $this->validar($request, $habitacione->id);
        $habitacione->update($data);

        return redirect()->route('habitaciones.index')->with('ok', 'Habitación actualizada.');
    }

    public function destroy(Habitacion $habitacione)
    {
        if ($habitacione->reservas()->exists()) {
            return back()->with('error', 'No se puede eliminar: la habitación tiene reservas asociadas.');
        }
        $habitacione->delete();

        return back()->with('ok', 'Habitación eliminada.');
    }

    private function validar(Request $request, $id = null): array
    {
        $empresaId = $request->user()->empresa_id;

        return $request->validate([
            'numero' => ['required', 'string', 'max:10',
                Rule::unique('habitaciones', 'numero')
                    ->where(fn ($q) => $q->where('empresa_id', $empresaId))
                    ->ignore($id),
            ],
            'tipo_habitacion_id' => 'required|exists:tipo_habitaciones,id',
            'piso' => 'required|integer|min:1|max:50',
            'precio_noche' => 'required|numeric|min:0',
            'capacidad' => 'required|integer|min:1|max:10',
            'estado' => 'required|in:disponible,ocupada,mantenimiento,limpieza',
            'descripcion' => 'nullable|string',
        ]);
    }
}
