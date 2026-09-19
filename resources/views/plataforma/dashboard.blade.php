@extends('layouts.plataforma')
@section('title', 'Dashboard de plataforma')

@section('content')
<h1 class="hp-pagetitle mb-4">Dashboard de la plataforma</h1>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><div class="card kpi bg-kpi-blue h-100"><div class="body"><div class="num">{{ $totalEmpresas }}</div><div class="lbl">Empresas registradas</div><i class="bi bi-building ico"></i></div></div></div>
    <div class="col-6 col-xl-3"><div class="card kpi bg-kpi-green h-100"><div class="body"><div class="num">{{ $activas }}</div><div class="lbl">Activas</div><i class="bi bi-check-circle ico"></i></div></div></div>
    <div class="col-6 col-xl-3"><div class="card kpi bg-kpi-red h-100"><div class="body"><div class="num">{{ $suspendidas }}</div><div class="lbl">Suspendidas</div><i class="bi bi-slash-circle ico"></i></div></div></div>
    <div class="col-6 col-xl-3"><div class="card kpi bg-kpi-green h-100"><div class="body"><div class="num" style="font-size:1.5rem">S/ {{ number_format($mrr, 2) }}</div><div class="lbl">MRR estimado</div><i class="bi bi-graph-up-arrow ico"></i></div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><div class="stat h-100"><div class="ic" style="background:#155e9c"><i class="bi bi-people-fill"></i></div><div><div class="v">{{ $totalUsuarios }}</div><div class="t">Usuarios totales</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="stat h-100"><div class="ic" style="background:#0fb6a8"><i class="bi bi-door-closed-fill"></i></div><div><div class="v">{{ $totalHabitaciones }}</div><div class="t">Habitaciones totales</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="stat h-100"><div class="ic" style="background:#e3a72f"><i class="bi bi-journal-check"></i></div><div><div class="v">{{ $totalReservas }}</div><div class="t">Reservas totales</div></div></div></div>
    <div class="col-6 col-xl-3"><div class="stat h-100"><div class="ic" style="background:#22a565"><i class="bi bi-cash-stack"></i></div><div><div class="v" style="font-size:1.1rem">S/ {{ number_format($ingresoSuscripciones, 2) }}</div><div class="t">Ingresos por suscripción</div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-diagram-3 me-1"></i> Empresas por plan</div>
            <div class="card-body">
                @foreach ($planes as $plan)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>{{ $plan->nombre }} <small class="text-muted">(S/ {{ number_format($plan->precio_mensual, 0) }}/mes)</small></span>
                        <span class="badge bg-primary">{{ $porPlan[$plan->id] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"><span><i class="bi bi-clock-history me-1"></i> Empresas recientes</span><a href="{{ route('plataforma.empresas.index') }}" class="small">Ver todas</a></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Empresa</th><th>Plan</th><th>Estado</th><th>Registrada</th></tr></thead>
                    <tbody>
                        @forelse ($recientes as $e)
                            <tr>
                                <td class="fw-semibold"><a href="{{ route('plataforma.empresas.show', $e) }}">{{ $e->nombre }}</a></td>
                                <td>{{ $e->plan->nombre ?? '—' }}</td>
                                <td><span class="badge bg-{{ $e->badgeSuscripcion() }}">{{ ucfirst($e->estadoSuscripcion()) }}</span></td>
                                <td>{{ $e->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Sin empresas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
