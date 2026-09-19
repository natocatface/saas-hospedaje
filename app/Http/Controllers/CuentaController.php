<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CuentaController extends Controller
{
    public function edit(Request $request)
    {
        return view('cuenta.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'telefono' => 'nullable|string|max:20',
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'min:6', 'confirmed'],
        ], [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'current_password.required_with' => 'Ingresa tu contraseña actual para cambiarla.',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->telefono = $data['telefono'] ?? null;

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return back()->with('ok', 'Tu cuenta fue actualizada.');
    }
}
