@extends('layouts.plataforma')
@section('title', 'Empresas')

@section('content')
<h1 class="hp-pagetitle mb-3">Empresas registradas</h1>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-6"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por nombre, RUC o correo..."></div>
            <div class="col-md-4">
                <select name="estado" class="form-select">
                    <option value="">Todas</option>
                    <option value="activas" @selected(request('estado')==='activas')>Activas</option>
                    <option value="suspendidas" @selected(request('estado')==='suspendidas')>Suspendidas</option>
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Filtrar</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Empresa</th><th>Plan</th><th>Usuarios</th><th>Suscripción</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
                @forelse ($empresas as $e)
                    <tr>
                        <td class="fw-semibold">{{ $e->nombre }}<br><small class="text-muted">{{ $e->email }}</small></td>
                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $e->plan->nombre ?? '—' }}</span></td>
                        <td>{{ $e->usuarios_count }}</td>
                        <td><span class="badge bg-{{ $e->badgeSuscripcion() }}">{{ ucfirst($e->estadoSuscripcion()) }}</span>
                            @if ($e->diasRestantes() > 0)<br><small class="text-muted">{{ $e->diasRestantes() }} días</small>@endif
                        </td>
                        <td>@if ($e->activo)<span class="badge bg-success">Activa</span>@else<span class="badge bg-danger">Suspendida</span>@endif</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('plataforma.empresas.show', $e) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <form action="{{ route('plataforma.empresas.toggle', $e) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cambiar el estado de {{ $e->nombre }}?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-{{ $e->activo ? 'danger' : 'success' }}"><i class="bi bi-{{ $e->activo ? 'pause' : 'play' }}-fill"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay empresas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $empresas->links() }}</div>
@endsection
