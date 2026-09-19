<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::query();
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        if ($request->filled('q')) {
            $query->where('nombre', 'like', '%'.$request->q.'%');
        }
        $productos = $query->orderBy('categoria')->orderBy('nombre')->paginate(15)->withQueryString();
        $categorias = Producto::CATEGORIAS;

        return view('productos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        return view('productos.form', ['producto' => new Producto()]);
    }

    public function store(Request $request)
    {
        Producto::create($this->validar($request));
        return redirect()->route('productos.index')->with('ok', 'Producto creado.');
    }

    public function edit(Producto $producto)
    {
        return view('productos.form', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        $producto->update($this->validar($request));
        return redirect()->route('productos.index')->with('ok', 'Producto actualizado.');
    }

    public function destroy(Producto $producto)
    {
        if ($producto->consumos()->exists()) {
            return back()->with('error', 'No se puede eliminar: el producto tiene consumos registrados.');
        }
        $producto->delete();
        return back()->with('ok', 'Producto eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:120',
            'categoria' => 'required|in:minibar,restaurante,lavanderia,servicios,otros',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'activo' => 'required|boolean',
        ]);
    }
}
