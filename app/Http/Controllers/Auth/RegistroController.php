<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Empresa;
use App\Models\PermisoRol;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\Permisos;
use Illuminate\Support\Str;

class RegistroController extends Controller
{
    public function show()
    {
        $planes = Plan::where('activo', true)->orderBy('precio_mensual')->get();
        return view('auth.registro', compact('planes'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'empresa_nombre' => 'required|string|max:150',
            'ruc' => 'nullable|string|max:20',
            'empresa_telefono' => 'nullable|string|max:30',
            'plan_id' => 'nullable|exists:planes,id',
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [], [
            'empresa_nombre' => 'nombre de la empresa',
            'name' => 'nombre del administrador',
        ]);

        $plan = $data['plan_id']
            ? Plan::find($data['plan_id'])
            : Plan::where('slug', 'free')->first();

        $user = DB::transaction(function () use ($data, $plan) {
            $empresa = Empresa::create([
                'nombre' => $data['empresa_nombre'],
                'slug' => $this->slugUnico($data['empresa_nombre']),
                'ruc' => $data['ruc'] ?? null,
                'telefono' => $data['empresa_telefono'] ?? null,
                'email' => $data['email'],
                'plan_id' => $plan?->id,
                'activo' => true,
                'trial_ends_at' => now()->addDays(30),
            ]);

            $user = User::create([
                'empresa_id' => $empresa->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'rol' => 'admin',
                'activo' => true,
                'password' => $data['password'],
            ]);

            $config = [
                'nombre_hotel' => $data['empresa_nombre'],
                'ruc' => $data['ruc'] ?? '',
                'telefono' => $data['empresa_telefono'] ?? '',
                'email' => $data['email'],
                'simbolo_moneda' => 'S/',
                'igv' => '18',
                'checkin_hora' => '14:00',
                'checkout_hora' => '12:00',
            ];
            foreach ($config as $clave => $valor) {
                Configuracion::create(['empresa_id' => $empresa->id, 'clave' => $clave, 'valor' => $valor]);
            }

            // Permisos por defecto por rol
            foreach (Permisos::rolesEditables() as $rol => $label) {
                foreach (Permisos::porDefecto($rol) as $permiso) {
                    PermisoRol::create(['empresa_id' => $empresa->id, 'rol' => $rol, 'permiso' => $permiso]);
                }
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('ok', '¡Bienvenido! Tu hotel fue registrado. Empieza creando tus habitaciones.');
    }

    private function slugUnico(string $nombre): string
    {
        $base = Str::slug($nombre) ?: 'hotel';
        $slug = $base;
        $i = 1;
        while (Empresa::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }
        return $slug;
    }
}
