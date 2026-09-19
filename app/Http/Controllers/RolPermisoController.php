<?php

namespace App\Http\Controllers;

use App\Models\PermisoRol;
use App\Support\Permisos;
use Illuminate\Http\Request;

class RolPermisoController extends Controller
{
    public function index(Request $request)
    {
        $roles = Permisos::rolesEditables();

        // Matriz actual: [rol => [permiso => true]]
        $asignados = [];
        foreach (array_keys($roles) as $rol) {
            $asignados[$rol] = PermisoRol::where('rol', $rol)->pluck('permiso')->all();
        }

        return view('roles.index', [
            'catalogo' => Permisos::CATALOGO,
            'roles' => $roles,
            'asignados' => $asignados,
        ]);
    }

    public function update(Request $request)
    {
        $roles = array_keys(Permisos::rolesEditables());
        $validos = Permisos::todos();
        $permisos = (array) $request->input('permisos', []);
        $empresaId = $request->user()->empresa_id;

        foreach ($roles as $rol) {
            PermisoRol::where('rol', $rol)->delete();

            $seleccionados = array_intersect((array) ($permisos[$rol] ?? []), $validos);
            foreach ($seleccionados as $permiso) {
                PermisoRol::create([
                    'empresa_id' => $empresaId,
                    'rol' => $rol,
                    'permiso' => $permiso,
                ]);
            }
        }

        return back()->with('ok', 'Permisos actualizados correctamente.');
    }
}
