@extends('layouts.app')
@section('title', 'Huéspedes')
@section('breadcrumb', 'Inicio / Huéspedes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Huéspedes</h1>
    <a href="{{ route('huespedes.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Nuevo Huésped</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-9">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por nombre, apellido o documento...">
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Buscar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Nombre</th><th>Documento</th><th>Teléfono</th><th>Email</th><th>Nacionalidad</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($huespedes as $h)
                    <tr>
                        <td class="fw-semibold">{{ $h->nombre_completo }}</td>
                        <td><span class="badge bg-light text-dark">{{ $h->documento_tipo }}</span> {{ $h->documento_numero }}</td>
                        <td>{{ $h->telefono ?: '—' }}</td>
                        <td>{{ $h->email ?: '—' }}</td>
                        <td>{{ $h->nacionalidad }}</td>
                        <td class="text-end">
                            <a href="{{ route('huespedes.edit', $h) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('huespedes.destroy', $h) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar a {{ $h->nombre_completo }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay huéspedes registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $huespedes->links() }}</div>
@endsection
