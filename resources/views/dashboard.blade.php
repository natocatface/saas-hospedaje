@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Inicio / Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="hp-pagetitle">Dashboard</h1>
    <span class="text-muted small">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
</div>

@if ($onbHechos < $onbTotal)
{{-- Onboarding: primeros pasos --}}
<div class="card mb-3" style="border-left:4px solid #e3a72f">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h5 class="mb-0"><i class="bi bi-rocket-takeoff text-warning me-1"></i> Primeros pasos ({{ $onbHechos }}/{{ $onbTotal }})</h5>
            <div class="progress flex-grow-1 ms-md-3" style="height:8px;max-width:320px"><div class="progress-bar bg-warning" style="width:{{ round($onbHechos / $onbTotal * 100) }}%"></div></div>
        </div>
        <div class="row g-2">
            @foreach ($onboarding as $paso)
                <div class="col-md-6 col-xl-4">
                    <div class="d-flex align-items-center gap-2 p-2 rounded border {{ $paso['done'] ? 'bg-light' : '' }}">
                        <i class="bi bi-{{ $paso['done'] ? 'check-circle-fill text-success' : 'circle text-muted' }} fs-5"></i>
                        <span class="flex-grow-1 small {{ $paso['done'] ? 'text-muted text-decoration-line-through' : 'fw-semibold' }}">{{ $paso['t'] }}</span>
                        @if (! $paso['done'] && $paso['url'])
                            <a href="{{ $paso['url'] }}" class="btn btn-sm btn-outline-primary py-0"><i class="bi bi-arrow-right"></i></a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="card kpi bg-kpi-green h-100">
            <div class="body">
                <div class="num">{{ $disponibles }}</div>
                <div class="lbl">Habitaciones Disponibles</div>
                <i class="bi bi-door-open-fill ico"></i>
            </div>
            <a href="{{ route('habitaciones.index') }}" class="foot">Ver <i class="bi bi-arrow-right-circle ms-1"></i></a>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi bg-kpi-red h-100">
            <div class="body">
                <div class="num">{{ $ocupadas }}</div>
                <div class="lbl">Habitaciones Ocupadas</div>
                <i class="bi bi-door-closed-fill ico"></i>
            </div>
            <a href="{{ route('habitaciones.index') }}" class="foot">Ver <i class="bi bi-arrow-right-circle ms-1"></i></a>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi bg-kpi-cyan h-100">
            <div class="body">
                <div class="num">{{ $ocupacionPct }}%</div>
                <div class="lbl">Ocupación ({{ $ocupadas }}/{{ $totalHab }})</div>
                <i class="bi bi-pie-chart-fill ico"></i>
            </div>
            <a href="{{ route('reportes') }}" class="foot">Reporte <i class="bi bi-arrow-right-circle ms-1"></i></a>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card kpi bg-kpi-yellow h-100">
            <div class="body">
                <div class="num">{{ $mantenimiento }}</div>
                <div class="lbl">En Mantenimiento</div>
                <i class="bi bi-tools ico"></i>
            </div>
            <a href="{{ route('habitaciones.index') }}" class="foot">Ver <i class="bi bi-arrow-right-circle ms-1"></i></a>
        </div>
    </div>
</div>

{{-- Stats mini --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="stat h-100">
            <div class="ic" style="background:#22a565"><i class="bi bi-box-arrow-in-right"></i></div>
            <div><div class="v">{{ $checkinsHoy }}</div><div class="t">Check-ins Hoy · Llegadas del día</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat h-100">
            <div class="ic" style="background:#e0524d"><i class="bi bi-box-arrow-right"></i></div>
            <div><div class="v">{{ $checkoutsHoy }}</div><div class="t">Check-outs Hoy · Salidas del día</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat h-100">
            <div class="ic" style="background:#2563eb"><i class="bi bi-people-fill"></i></div>
            <div><div class="v">{{ $totalHuespedes }}</div><div class="t">Total Huéspedes registrados</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat h-100">
            <div class="ic" style="background:#e0a800"><i class="bi bi-calendar-check"></i></div>
            <div><div class="v">{{ $reservasMes }}</div><div class="t">Reservas este mes · {{ now()->locale('es')->isoFormat('MMMM') }}</div></div>
        </div>
    </div>
</div>

{{-- Indicadores hoteleros del mes --}}
<div class="card mb-3">
    <div class="card-header"><i class="bi bi-speedometer2 me-1"></i> Indicadores del mes ({{ now()->locale('es')->isoFormat('MMMM') }})</div>
    <div class="card-body">
        <div class="row text-center g-3 align-items-center">
            <div class="col-md-3 col-6">
                <div class="fs-3 fw-bold text-primary">S/ {{ number_format($adrMes, 2) }}</div>
                <div class="small text-muted">ADR · Tarifa media diaria</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="fs-3 fw-bold" style="color:#0fb6a8">S/ {{ number_format($revparMes, 2) }}</div>
                <div class="small text-muted">RevPAR · Ingreso por hab. disponible</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="fs-3 fw-bold" style="color:#e3a72f">{{ $ocupMes }}%</div>
                <div class="small text-muted">Ocupación del mes</div>
            </div>
            <div class="col-md-3 col-6">
                <a href="{{ route('reportes') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-bar-chart me-1"></i> Reportes completos</a>
            </div>
        </div>
    </div>
</div>

{{-- Ocupación 7 días + panel derecho --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-bar-chart-line me-1"></i> Ocupación Últimos 7 Días</div>
            <div class="card-body"><div class="chart-box" style="height:200px"><canvas id="chartOcupacion"></canvas></div></div>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-pie-chart-fill me-1"></i> Estado de Habitaciones</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="chart-box" style="height:230px;width:100%"><canvas id="chartHabEstado"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-clipboard-data me-1"></i> Reservas por Estado · {{ now()->locale('es')->isoFormat('MMMM') }}</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div class="chart-box" style="height:230px;width:100%"><canvas id="chartResEstado"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card kpi bg-kpi-green mb-3">
            <div class="body">
                <div class="lbl mb-1">Ingresos del Mes</div>
                <div class="num">S/ {{ number_format($ingresosMes, 2) }}</div>
                <i class="bi bi-cash-stack ico"></i>
            </div>
            <a href="{{ route('reportes') }}" class="foot">Ver reporte completo <i class="bi bi-arrow-right-circle ms-1"></i></a>
        </div>
        <div class="card kpi bg-kpi-orange mb-3">
            <div class="body">
                <div class="lbl mb-1">Facturas Pendientes</div>
                <div class="num">{{ $facturasPendientes }}</div>
                <i class="bi bi-receipt ico"></i>
            </div>
            <a href="{{ route('facturacion') }}" class="foot">Ver facturas pendientes <i class="bi bi-arrow-right-circle ms-1"></i></a>
        </div>
        <div class="card">
            <div class="card-header"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Acciones Rápidas</div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('reservas.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Nueva Reserva</a>
                <a href="{{ route('huespedes.create') }}" class="btn btn-success"><i class="bi bi-person-plus me-1"></i> Nuevo Huésped</a>
                <a href="{{ route('habitaciones.index') }}" class="btn btn-info text-white"><i class="bi bi-grid me-1"></i> Ver Disponibilidad</a>
                <a href="{{ route('reportes') }}" class="btn btn-secondary"><i class="bi bi-bar-chart me-1"></i> Reportes</a>
            </div>
        </div>
    </div>
</div>

{{-- Ingresos 6 meses --}}
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up-arrow me-1"></i> Ingresos Últimos 6 Meses</span>
                <span class="badge bg-success">Total: S/ {{ number_format(array_sum($data6), 2) }}</span>
            </div>
            <div class="card-body"><div class="chart-box" style="height:230px"><canvas id="chartIngresos"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-calendar-event me-1"></i> Próximas Reservas</div>
            <div class="list-group list-group-flush">
                @forelse ($proximas as $r)
                    <a href="{{ route('reservas.show', $r) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold small">{{ $r->huesped->nombre_completo }}</span>
                            <span class="badge bg-{{ $r->badgeColor() }}">{{ ucfirst($r->estado) }}</span>
                        </div>
                        <div class="small text-muted">
                            Hab. {{ $r->habitacion->numero }} · {{ $r->fecha_checkin->format('d/m') }} → {{ $r->fecha_checkout->format('d/m') }}
                        </div>
                    </a>
                @empty
                    <div class="list-group-item text-muted small text-center py-4">Sin reservas próximas</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .chart-box { position: relative; width: 100%; }
</style>
@endpush

@push('scripts')
<script>
    // Plugin: muestra "Sin datos" en el centro cuando la dona está vacía
    const emptyDoughnut = {
        id: 'emptyDoughnut',
        afterDraw(chart) {
            const ds = chart.data.datasets[0]?.data || [];
            const total = ds.reduce((a, b) => a + (Number(b) || 0), 0);
            if (total > 0) return;
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const cx = (chartArea.left + chartArea.right) / 2;
            const cy = (chartArea.top + chartArea.bottom) / 2;
            const r = Math.min(chartArea.right - chartArea.left, chartArea.bottom - chartArea.top) / 2 - 4;
            ctx.save();
            // anillo gris
            ctx.beginPath();
            ctx.arc(cx, cy, r, 0, Math.PI * 2);
            ctx.arc(cx, cy, r * 0.62, 0, Math.PI * 2, true);
            ctx.fillStyle = '#eef1f5';
            ctx.fill('evenodd');
            // texto
            ctx.fillStyle = '#9aa4b2';
            ctx.font = '600 13px system-ui, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('Sin datos', cx, cy);
            ctx.restore();
        }
    };

    const ctxO = document.getElementById('chartOcupacion');
    new Chart(ctxO, {
        type: 'bar',
        data: {
            labels: @json($labels7),
            datasets: [{
                label: 'Habitaciones ocupadas',
                data: @json($data7),
                backgroundColor: '#22a565',
                borderRadius: 6,
                maxBarThickness: 46
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => ` ${c.parsed.y} hab. ocupadas` } }
            },
            scales: { y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 } } }
        }
    });

    // Estado de habitaciones (dona)
    new Chart(document.getElementById('chartHabEstado'), {
        type: 'doughnut',
        data: {
            labels: @json($habEstadoLabels),
            datasets: [{
                data: @json($habEstadoData),
                backgroundColor: @json($habEstadoColors),
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } },
                tooltip: { callbacks: { label: c => ` ${c.label}: ${c.parsed} hab.` } }
            }
        },
        plugins: [emptyDoughnut]
    });

    // Reservas por estado del mes (dona)
    new Chart(document.getElementById('chartResEstado'), {
        type: 'doughnut',
        data: {
            labels: @json($resEstadoLabels),
            datasets: [{
                data: @json($resEstadoData),
                backgroundColor: @json($resEstadoColors),
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } },
                tooltip: { callbacks: { label: c => ` ${c.label}: ${c.parsed}` } }
            }
        },
        plugins: [emptyDoughnut]
    });

    const ctxI = document.getElementById('chartIngresos');
    new Chart(ctxI, {
        type: 'line',
        data: {
            labels: @json($labels6),
            datasets: [{
                label: 'Ingresos (S/)',
                data: @json($data6),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,.12)',
                fill: true,
                tension: .4,
                pointRadius: 4,
                pointBackgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'S/ ' + v } } }
        }
    });
</script>
@endpush
