@extends('layouts.app')
@section('title', 'Suscripción')
@section('breadcrumb', 'Inicio / Suscripción')

@section('content')
<h1 class="hp-pagetitle mb-3">Suscripción del plan</h1>

@php $estado = $empresa->estadoSuscripcion(); @endphp

@if ($estado === 'vencida' || $estado === 'suspendida')
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>
        @if ($estado === 'suspendida') Tu cuenta está suspendida. Renueva tu suscripción o contacta a soporte.
        @else Tu período {{ $empresa->enTrial() ? 'de prueba' : 'de suscripción' }} venció. Renueva para seguir usando el sistema.
        @endif
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Estado actual</span>
                <span class="badge bg-{{ $empresa->badgeSuscripcion() }}">{{ ucfirst($estado) }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6 mb-2"><small class="text-muted d-block">Plan</small><strong>{{ $empresa->plan->nombre ?? '—' }}</strong></div>
                    <div class="col-sm-6 mb-2"><small class="text-muted d-block">Precio</small>@if(($empresa->plan->precio_mensual ?? 0) > 0) S/ {{ number_format($empresa->plan->precio_mensual, 2) }}/mes @else Gratis @endif</div>
                    <div class="col-sm-6 mb-2"><small class="text-muted d-block">Vigente hasta</small>{{ optional($empresa->suscripcion_hasta ?? $empresa->trial_ends_at)->format('d/m/Y') ?? '—' }}</div>
                    <div class="col-sm-6 mb-2"><small class="text-muted d-block">Días restantes</small>{{ $empresa->diasRestantes() }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-1"></i> Historial de pagos</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Fecha</th><th>Plan</th><th>Período</th><th>Método</th><th class="text-end">Monto</th></tr></thead>
                    <tbody>
                        @forelse ($empresa->suscripcionPagos->sortByDesc('id') as $p)
                            <tr><td>{{ $p->fecha_pago->format('d/m/Y') }}</td><td>{{ $p->plan_nombre }}</td>
                                <td>{{ $p->periodo_inicio->format('d/m/Y') }} → {{ $p->periodo_fin->format('d/m/Y') }}</td>
                                <td>{{ ucfirst($p->metodo) }}</td>
                                <td class="text-end fw-semibold">S/ {{ number_format($p->monto, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Aún no hay pagos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-credit-card me-1"></i> Renovar suscripción</div>
            <div class="card-body">
                <p class="text-muted small">Renueva por <strong>1 mes</strong> el plan {{ $empresa->plan->nombre ?? '' }}.</p>
                <div class="d-flex justify-content-between mb-3 fs-5 fw-bold">
                    <span>A pagar</span>
                    <span class="text-success">S/ {{ number_format($empresa->plan->precio_mensual ?? 0, 2) }}</span>
                </div>
                <form method="POST" action="{{ route('suscripcion.pagar') }}">
                    @csrf
                    <label class="form-label">Método de pago</label>
                    <select name="metodo" class="form-select mb-3" required>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="yape">Yape</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                    <button class="btn btn-primary w-100"><i class="bi bi-check2-circle me-1"></i> Pagar y renovar</button>
                </form>
                <small class="text-muted d-block mt-2"><i class="bi bi-shield-lock me-1"></i> Pago simulado (demo).</small>
            </div>
        </div>
        @if (auth()->user()->esAdmin())
        <a href="{{ route('plan') }}" class="btn btn-outline-secondary w-100 mt-3"><i class="bi bi-gem me-1"></i> Ver / cambiar de plan</a>
        @endif
    </div>
</div>
@endsection
