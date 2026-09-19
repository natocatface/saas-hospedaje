<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('empresa_id', $request->user()->empresa_id);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%");
            });
        }
        $usuarios = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.form', ['usuario' => new User()]);
    }

    public function store(Request $request)
    {
        $empresa = $request->user()->empresa;
        if ($empresa && ! $empresa->puedeAgregarUsuario()) {
            return back()->withInput()->with('error',
                "Tu plan ({$empresa->plan?->nombre}) permite hasta {$empresa->plan?->max_usuarios} usuarios. Mejora tu plan para agregar más.");
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:20',
            'rol' => 'required|in:admin,recepcionista,housekeeping',
            'activo' => 'required|boolean',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $data['empresa_id'] = $request->user()->empresa_id;

        User::create($data);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario creado.');
    }

    public function edit(Request $request, User $usuario)
    {
        $this->autorizar($request, $usuario);
        return view('usuarios.form', ['usuario' => $usuario]);
    }

    public function update(Request $request, User $usuario)
    {
        $this->autorizar($request, $usuario);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', Rule::unique('users')->ignore($usuario->id)],
            'telefono' => 'nullable|string|max:20',
            'rol' => 'required|in:admin,recepcionista,housekeeping',
            'activo' => 'required|boolean',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $usuario)
    {
        $this->autorizar($request, $usuario);

        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }
        $usuario->delete();

        return back()->with('ok', 'Usuario eliminado.');
    }

    /** Garantiza que el usuario gestionado pertenece a la misma empresa. */
    private function autorizar(Request $request, User $usuario): void
    {
        abort_unless($usuario->empresa_id === $request->user()->empresa_id, 404);
    }
}
