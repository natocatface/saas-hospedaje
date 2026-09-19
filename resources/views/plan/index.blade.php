@extends('layouts.app')
@section('title', 'Mi Plan')
@section('breadcrumb', 'Inicio / Mi Plan')

@section('content')
<h1 class="hp-pagetitle mb-3">Mi Plan y Suscripción</h1>

@if ($empresa)
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-building me-1"></i> {{ $empresa->nombre }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6 mb-2"><small class="text-muted d-block">Plan actual</small>
                        <span class="badge bg-primary fs-6">{{ $empresa->plan?->nombre ?? 'Sin plan' }}</span>
                    </div>
                    <div class="col-sm-6 mb-2"><small class="text-muted d-block">Precio</small>
                        <strong>@if (($empresa->plan?->precio_mensual ?? 0) > 0) S/ {{ number_format($empresa->plan->precio_mensual, 2) }}/mes @else Gratis @endif</strong>
                    </div>
                    @if ($empresa->trial_ends_at)
                        <div class="col-sm-6 mb-2"><small class="text-muted d-block">Prueba hasta</small>{{ $empresa->trial_ends_at->format('d/m/Y') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-speedometer2 me-1"></i> Uso actual</div>
            <div class="card-body">
                @php
                    $maxH = $empresa->plan?->max_habitaciones; $maxU = $empresa->plan?->max_usuarios;
                    $pctH = $maxH ? min(100, round($uso['habitaciones'] / max(1,$maxH) * 100)) : 0;
                    $pctU = $maxU ? min(100, round($uso['usuarios'] / max(1,$maxU) * 100)) : 0;
                @endphp
                <div class="d-flex justify-content-between small mb-1"><span>Habitaciones</span><span>{{ $uso['habitaciones'] }} / {{ $maxH ?: '∞' }}</span></div>
                <div class="progress mb-3" style="height:7px"><div class="progress-bar" style="width:{{ $maxH ? $pctH : 12 }}%"></div></div>
                <div class="d-flex justify-content-between small mb-1"><span>Usuarios</span><span>{{ $uso['usuarios'] }} / {{ $maxU ?: '∞' }}</span></div>
                <div class="progress" style="height:7px"><div class="progress-bar bg-success" style="width:{{ $maxU ? $pctU : 12 }}%"></div></div>
            </div>
        </div>
    </div>
</div>
@endif

<h5 class="mb-3">Planes disponibles</h5>
<div class="row g-3">
    @foreach ($planes as $plan)
        @php $actual = $empresa && $empresa->plan_id === $plan->id; @endphp
        <div class="col-md-4">
            <div class="card h-100 {{ $actual ? 'border-primary' : '' }}" @if($actual) style="border-width:2px" @endif>
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="fw-bold">{{ $plan->nombre }}</h5>
                    <div class="display-6 fw-bold text-primary my-2">
                        @if ($plan->precio_mensual > 0) S/ {{ number_format($plan->precio_mensual, 0) }}<small class="fs-6 text-muted">/mes</small>
                        @else Gratis @endif
                    </div>
                    <ul class="list-unstyled text-start small flex-grow-1 mt-2">
                        <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> {{ $plan->limiteHabitaciones() }} habitaciones</li>
                        <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> {{ $plan->limiteUsuarios() }} usuarios</li>
                        <li class="mb-1"><i class="bi bi-{{ $plan->permite_reportes ? 'check-circle-fill text-success' : 'x-circle text-muted' }} me-1"></i> Reportes</li>
                        <li class="mb-1"><i class="bi bi-{{ $plan->permite_temporadas ? 'check-circle-fill text-success' : 'x-circle text-muted' }} me-1"></i> Tarifas de temporada</li>
                    </ul>
                    @if ($actual)
                        <button class="btn btn-outline-primary w-100" disabled>Plan actual</button>
                    @else
                        <form method="POST" action="{{ route('plan.cambiar') }}">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button class="btn btn-primary w-100">Cambiar a este plan</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
