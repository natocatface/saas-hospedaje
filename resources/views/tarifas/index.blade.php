@extends('layouts.app')
@section('title', 'Tarifas de Temporada')
@section('breadcrumb', 'Inicio / Tarifas Temporada')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Tarifas de Temporada</h1>
    <a href="{{ route('tarifas.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Nueva Temporada</a>
</div>

<div class="alert alert-info small">
    <i class="bi bi-info-circle me-1"></i> Las temporadas activas ajustan automáticamente la tarifa sugerida al crear una reserva según la fecha de check-in.
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Nombre</th><th>Tipo</th><th>Vigencia</th><th>Ajuste</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
                @forelse ($tarifas as $t)
                    <tr>
                        <td class="fw-semibold">{{ $t->nombre }}</td>
                        <td><span class="badge bg-{{ $t->badgeColor() }}">{{ ucfirst($t->tipo) }}</span></td>
                        <td>{{ $t->fecha_inicio->format('d/m/Y') }} → {{ $t->fecha_fin->format('d/m/Y') }}</td>
                        <td>
                            @if ($t->ajuste_tipo === 'porcentaje')
                                {{ $t->ajuste_valor > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($t->ajuste_valor,2),'0'),'.') }}%
                            @else
                                {{ $t->ajuste_valor > 0 ? '+' : '' }}S/ {{ number_format($t->ajuste_valor,2) }}
                            @endif
                        </td>
                        <td>@if ($t->activo)<span class="badge bg-success">Activa</span>@else<span class="badge bg-secondary">Inactiva</span>@endif</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('tarifas.edit', $t) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('tarifas.destroy', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta temporada?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay temporadas configuradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $tarifas->links() }}</div>
@endsection
