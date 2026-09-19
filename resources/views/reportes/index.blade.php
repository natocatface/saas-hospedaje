@extends('layouts.app')
@section('title', 'Reportes')
@section('breadcrumb', 'Inicio / Reportes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="hp-pagetitle">Reportes</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('reportes.pdf', request()->only('desde','hasta')) }}" target="_blank" class="btn btn-danger"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</a>
        <a href="{{ route('reportes.export', request()->only('desde','hasta')) }}" class="btn btn-success"><i class="bi bi-filetype-csv me-1"></i> CSV</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3"><label class="form-label small">Desde</label><input type="date" name="desde" value="{{ $desde->format('Y-m-d') }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label small">Hasta</label><input type="date" name="hasta" value="{{ $hasta->format('Y-m-d') }}" class="form-control"></div>
            <div class="col-md-3">
                <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Aplicar</button>
                <a href="{{ route('reportes') }}" class="btn btn-light">Mes actual</a>
            </div>
        </form>
    </div>
</div>

{{-- KPIs principales --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><div class="card kpi bg-kpi-green h-100"><div class="body"><div class="num" style="font-size:1.6rem">S/ {{ number_format($ingresoTotal, 2) }}</div><div class="lbl">Ingresos cobrados</div><i class="bi bi-cash-stack ico"></i></div></div></div>
    <div class="col-6 col-xl-3"><div class="card kpi bg-kpi-cyan h-100"><div class="body"><div class="num">{{ $ocupacionPct }}%</div><div class="lbl">Ocupación del período</div><i class="bi bi-pie-chart-fill ico"></i></div></div></div>
    <div class="col-6 col-xl-3"><div class="card kpi bg-kpi-blue h-100"><div class="body"><div class="num">{{ $nFacturas }}</div><div class="lbl">Facturas cobradas</div><i class="bi bi-receipt ico"></i></div></div></div>
    <div class="col-6 col-xl-3"><div class="card kpi bg-kpi-orange h-100"><div class="body"><div class="num" style="font-size:1.6rem">S/ {{ number_format($ticketProm, 2) }}</div><div class="lbl">Ticket promedio</div><i class="bi bi-graph-up ico"></i></div></div></div>
</div>

{{-- Indicadores hoteleros --}}
<div class="card mb-3">
    <div class="card-header"><i class="bi bi-speedometer2 me-1"></i> Indicadores hoteleros</div>
    <div class="card-body">
        <div class="row text-center g-3">
            <div class="col-md-3 col-6">
                <div class="fs-3 fw-bold text-primary">S/ {{ number_format($adr, 2) }}</div>
                <div class="small text-muted">ADR — Tarifa media diaria</div>
                <div class="small text-muted">(ingreso hab. / noches vendidas)</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="fs-3 fw-bold" style="color:#0fb6a8">S/ {{ number_format($revpar, 2) }}</div>
                <div class="small text-muted">RevPAR — Ingreso por hab. disponible</div>
                <div class="small text-muted">(ingreso hab. / hab.-noche)</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="fs-3 fw-bold" style="color:#e3a72f">{{ $nochesVendidas }}</div>
                <div class="small text-muted">Noches vendidas</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="fs-3 fw-bold text-success">S/ {{ number_format($ingresoHabitaciones, 2) }}</div>
                <div class="small text-muted">Ingreso por hospedaje</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-graph-up-arrow me-1"></i> Ingresos por día</div>
            <div class="card-body">
                @if (count($labelsDia))<canvas id="chartDia" height="100"></canvas>
                @else<p class="text-muted text-center py-4 mb-0">Sin ingresos cobrados en el período.</p>@endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-clipboard-data me-1"></i> Reservas por estado</div>
            <div class="card-body">
                @forelse ($reservasPorEstado as $estado => $total)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="bi bi-circle-fill me-1 text-muted" style="font-size:.6rem"></i> {{ ucfirst($estado) }}</span>
                        <span class="badge bg-light text-dark">{{ $total }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">Sin reservas en el período.</p>
                @endforelse
                <hr>
                <div class="d-flex justify-content-between"><span class="text-muted">IGV recaudado</span><strong>S/ {{ number_format($igvTotal, 2) }}</strong></div>
            </div>
        </div>
    </div>
</div>

{{-- Por tipo de habitación --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-collection me-1"></i> Ocupación e ingresos por tipo</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Tipo</th><th class="text-center">Reservas</th><th class="text-center">Noches</th><th class="text-end">Ingreso</th><th class="text-end">ADR</th></tr></thead>
                    <tbody>
                        @forelse ($porTipo as $t)
                            <tr>
                                <td class="fw-semibold">{{ $t->nombre }}</td>
                                <td class="text-center">{{ $t->reservas }}</td>
                                <td class="text-center">{{ $t->noches }}</td>
                                <td class="text-end text-success fw-semibold">S/ {{ number_format($t->ingreso, 2) }}</td>
                                <td class="text-end">S/ {{ number_format($t->noches > 0 ? $t->ingreso / $t->noches : 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Sin datos en el período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-trophy me-1"></i> Habitaciones más rentables</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Hab.</th><th class="text-center">Res.</th><th class="text-end">Ingreso</th></tr></thead>
                    <tbody>
                        @forelse ($topHabitaciones as $i => $h)
                            <tr><td>{{ $i + 1 }}</td><td class="fw-semibold">N° {{ $h->numero }}</td><td class="text-center">{{ $h->reservas }}</td><td class="text-end text-success fw-semibold">S/ {{ number_format($h->ingreso, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if (count($labelsDia))
<script>
    new Chart(document.getElementById('chartDia'), {
        type: 'bar',
        data: { labels: @json($labelsDia), datasets: [{ label: 'Ingresos (S/)', data: @json($dataDia), backgroundColor: '#155e9c', borderRadius: 5 }] },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => 'S/ ' + v } } } }
    });
</script>
@endif
@endpush
