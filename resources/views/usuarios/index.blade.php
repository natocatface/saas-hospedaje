@extends('layouts.app')
@section('title', 'Usuarios')
@section('breadcrumb', 'Inicio / Usuarios')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Usuarios</h1>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Nuevo Usuario</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-9"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por nombre o correo..."></div>
            <div class="col-md-3 d-grid"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Buscar</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Rol</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
                @forelse ($usuarios as $u)
                    <tr>
                        <td class="fw-semibold">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->telefono ?: '—' }}</td>
                        <td><span class="badge bg-{{ $u->rol==='admin' ? 'primary' : ($u->rol==='housekeeping' ? 'info' : 'secondary') }}">{{ ucfirst($u->rol) }}</span></td>
                        <td>
                            @if ($u->activo) <span class="badge bg-success">Activo</span>
                            @else <span class="badge bg-secondary">Inactivo</span> @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('usuarios.edit', $u) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            @if ($u->id !== auth()->id())
                            <form action="{{ route('usuarios.destroy', $u) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar a {{ $u->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $usuarios->links() }}</div>
@endsection
