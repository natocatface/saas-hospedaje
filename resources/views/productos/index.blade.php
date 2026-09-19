@extends('layouts.app')
@section('title', 'Productos')
@section('breadcrumb', 'Inicio / Productos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Productos y Servicios</h1>
    <a href="{{ route('productos.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Nuevo Producto</a>
</div>

<div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i> Estos productos (minibar, restaurante, lavandería, servicios) se cargan como consumos a la cuenta de cada reserva.</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-6"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar producto..."></div>
            <div class="col-md-4">
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias as $c)
                        <option value="{{ $c }}" @selected(request('categoria')===$c)>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Filtrar</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Producto</th><th>Categoría</th><th class="text-end">Precio</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
                @forelse ($productos as $p)
                    <tr>
                        <td class="fw-semibold">{{ $p->nombre }}</td>
                        <td><span class="badge bg-{{ $p->badgeColor() }}">{{ ucfirst($p->categoria) }}</span></td>
                        <td class="text-end">S/ {{ number_format($p->precio, 2) }}</td>
                        <td>@if ($p->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Inactivo</span>@endif</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('productos.edit', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('productos.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este producto?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No hay productos. Crea el primero.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $productos->links() }}</div>
@endsection
