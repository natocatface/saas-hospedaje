@extends('layouts.app')
@section('title', 'Reservas')
@section('breadcrumb', 'Inicio / Reservas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Reservas</h1>
    <a href="{{ route('reservas.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Nueva Reserva</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-6">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por código o huésped...">
            </div>
            <div class="col-md-3">
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
                <tr><th>Código</th><th>Huésped</th><th>Hab.</th><th>Check-in</th><th>Check-out</th><th>Noches</th><th>Total</th><th>Estado</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($reservas as $r)
                    <tr>
                        <td class="fw-semibold">{{ $r->codigo }}</td>
                        <td>{{ $r->huesped->nombre_completo ?? '—' }}</td>
                        <td>{{ $r->habitacion->numero ?? '—' }}</td>
                        <td>{{ $r->fecha_checkin->format('d/m/Y') }}</td>
                        <td>{{ $r->fecha_checkout->format('d/m/Y') }}</td>
                        <td>{{ $r->noches }}</td>
                        <td>S/ {{ number_format($r->total, 2) }}</td>
                        <td><span class="badge bg-{{ $r->badgeColor() }}">{{ ucfirst($r->estado) }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('reservas.show', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('reservas.edit', $r) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('reservas.destroy', $r) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar la reserva {{ $r->codigo }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No hay reservas registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $reservas->links() }}</div>
@endsection
