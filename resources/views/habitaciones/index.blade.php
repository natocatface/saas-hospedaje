@extends('layouts.app')
@section('title', 'Habitaciones')
@section('breadcrumb', 'Inicio / Habitaciones')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Habitaciones</h1>
    <a href="{{ route('habitaciones.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Nueva Habitación</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por número...">
            </div>
            <div class="col-md-4">
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $e)
                        <option value="{{ $e }}" @selected(request('estado')===$e)>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>N°</th><th>Tipo</th><th>Piso</th><th>Cap.</th><th>Precio/noche</th><th>Estado</th><th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($habitaciones as $h)
                    <tr>
                        <td class="fw-semibold">{{ $h->numero }}</td>
                        <td>{{ $h->tipo->nombre ?? '—' }}</td>
                        <td>{{ $h->piso }}</td>
                        <td>{{ $h->capacidad }}</td>
                        <td>S/ {{ number_format($h->precio_noche, 2) }}</td>
                        <td><span class="badge bg-{{ $h->badgeColor() }}">{{ ucfirst($h->estado) }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('habitaciones.edit', $h) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('habitaciones.destroy', $h) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar la habitación {{ $h->numero }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay habitaciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $habitaciones->links() }}</div>
@endsection
