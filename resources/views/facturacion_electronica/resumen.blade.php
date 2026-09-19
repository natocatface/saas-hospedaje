@extends('layouts.app')
@section('title', 'Resumen diario de boletas')
@section('breadcrumb', 'Inicio / Facturación / Resumen diario')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="hp-pagetitle">Resumen diario de boletas (SUNAT)</h1>
    <a href="{{ route('facturacion') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> Volver a Facturación</a>
</div>

@if (! $config->habilitado || $config->driver !== 'sunat')
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>
        La facturación electrónica con driver SUNAT no está activa.
        @if (auth()->user()->esAdmin())<a href="{{ route('facturacion.config') }}">Ir a la configuración</a>.@endif
    </div>
@endif

<div class="row g-3">
    {{-- Boletas del día --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-receipt-cutoff me-1"></i> Boletas pendientes de declarar</span>
                <form method="GET" class="d-flex align-items-center gap-2">
                    <input type="date" name="fecha" value="{{ $fecha->toDateString() }}" class="form-control form-control-sm" style="width:auto" onchange="this.form.submit()">
                </form>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Boleta</th><th>Huésped</th><th class="text-end">Total</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse ($boletas as $b)
                            <tr>
                                <td class="fw-semibold">{{ $b->comprobanteNumero() ?? $b->numero }}</td>
                                <td>{{ $b->reserva->huesped->nombre_completo ?? '—' }}</td>
                                <td class="text-end">S/ {{ number_format($b->total, 2) }}</td>
                                <td><span class="badge bg-warning">{{ $b->sunatEstadoLabel() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No hay boletas pendientes de declarar en esta fecha.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($boletas->isNotEmpty())
                <div class="card-body border-top d-flex align-items-center justify-content-between">
                    <div><strong>{{ $boletas->count() }}</strong> boleta(s) · Total S/ {{ number_format($boletas->sum('total'), 2) }}</div>
                    <form method="POST" action="{{ route('facturacion.resumen.generar') }}" onsubmit="return confirm('¿Generar y enviar el resumen a SUNAT?')">
                        @csrf
                        <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">
                        <button class="btn btn-primary"><i class="bi bi-cloud-arrow-up me-1"></i> Generar y enviar resumen</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Resúmenes generados --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-collection me-1"></i> Resúmenes generados</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Resumen</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($resumenes as $r)
                            @php
                                $rc = match ($r->estado) {
                                    'aceptado' => 'success', 'rechazado' => 'danger',
                                    'procesando','enviado' => 'primary', default => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $r->identificador }}</div>
                                    <small class="text-muted">{{ $r->fecha_referencia->format('d/m/Y') }} · {{ $r->total_boletas }} bol.</small>
                                    @if ($r->ticket)<small class="text-muted d-block">Ticket: {{ \Illuminate\Support\Str::limit($r->ticket, 18) }}</small>@endif
                                </td>
                                <td><span class="badge bg-{{ $rc }}">{{ $r->estadoLabel() }}</span></td>
                                <td class="text-end">
                                    @if ($r->enProceso())
                                        <form method="POST" action="{{ route('facturacion.resumen.consultar', $r) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary" title="Consultar estado"><i class="bi bi-arrow-repeat"></i></button>
                                        </form>
                                    @endif
                                    @if ($r->cdr_path)
                                        <a href="{{ route('facturacion.resumen.cdr', $r) }}" class="btn btn-sm btn-outline-secondary mt-1" title="Descargar CDR"><i class="bi bi-file-earmark-zip"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Aún no has generado resúmenes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="alert alert-info mt-3 small mb-0">
            <i class="bi bi-info-circle me-1"></i> Las boletas se declaran agrupadas. Tras enviar el resumen, SUNAT responde de forma asíncrona: usa <i class="bi bi-arrow-repeat"></i> para consultar el estado y descargar el CDR.
        </div>
    </div>
</div>
@endsection
