@extends('layouts.plataforma')
@section('title', $empresa->nombre)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">{{ $empresa->nombre }}</h1>
    <a href="{{ route('plataforma.empresas.index') }}" class="btn btn-light">Volver</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Datos de la empresa</span>
                <span class="badge bg-{{ $empresa->badgeSuscripcion() }}">{{ ucfirst($empresa->estadoSuscripcion()) }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">RUC</small>{{ $empresa->ruc ?: '—' }}</div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Correo</small>{{ $empresa->email ?: '—' }}</div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Teléfono</small>{{ $empresa->telefono ?: '—' }}</div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Dirección</small>{{ $empresa->direccion ?: '—' }}</div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Plan actual</small><strong>{{ $empresa->plan->nombre ?? '—' }}</strong></div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Vence</small>{{ optional($empresa->suscripcion_hasta ?? $empresa->trial_ends_at)->format('d/m/Y') ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-receipt me-1"></i> Pagos de suscripción</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Fecha</th><th>Plan</th><th>Período</th><th class="text-end">Monto</th></tr></thead>
                    <tbody>
                        @forelse ($empresa->suscripcionPagos->sortByDesc('id') as $p)
                            <tr><td>{{ $p->fecha_pago->format('d/m/Y') }}</td><td>{{ $p->plan_nombre }}</td>
                                <td>{{ $p->periodo_inicio->format('d/m/Y') }} → {{ $p->periodo_fin->format('d/m/Y') }}</td>
                                <td class="text-end fw-semibold">S/ {{ number_format($p->monto, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Sin pagos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Resumen de uso</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Habitaciones</span><strong>{{ $stats['habitaciones'] }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Reservas</span><strong>{{ $stats['reservas'] }}</strong></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Usuarios</span><strong>{{ $stats['usuarios'] }}</strong></div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Cambiar plan</div>
            <div class="card-body">
                <form method="POST" action="{{ route('plataforma.empresas.plan', $empresa) }}">
                    @csrf
                    <select name="plan_id" class="form-select mb-2">
                        @foreach ($planes as $plan)
                            <option value="{{ $plan->id }}" @selected($empresa->plan_id === $plan->id)>{{ $plan->nombre }} — S/ {{ number_format($plan->precio_mensual, 0) }}/mes</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary w-100"><i class="bi bi-arrow-repeat me-1"></i> Actualizar plan</button>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('plataforma.empresas.toggle', $empresa) }}" onsubmit="return confirm('¿Confirmar cambio de estado?')">
            @csrf
            <button class="btn btn-{{ $empresa->activo ? 'outline-danger' : 'success' }} w-100">
                <i class="bi bi-{{ $empresa->activo ? 'pause' : 'play' }}-fill me-1"></i> {{ $empresa->activo ? 'Suspender empresa' : 'Activar empresa' }}
            </button>
        </form>
    </div>
</div>
@endsection
