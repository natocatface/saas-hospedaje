@extends('layouts.app')
@section('title', 'Caja')
@section('breadcrumb', 'Inicio / Caja')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Caja</h1>
    @if ($sesion)
        <span class="badge bg-success fs-6"><i class="bi bi-unlock me-1"></i> Caja abierta</span>
    @else
        <span class="badge bg-secondary fs-6"><i class="bi bi-lock me-1"></i> Caja cerrada</span>
    @endif
</div>

@if (! $sesion)
    {{-- Abrir caja --}}
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-cash-stack me-1"></i> Aperturar caja</div>
                <div class="card-body">
                    <p class="text-muted small">Registra el monto inicial en efectivo con el que inicias el turno.</p>
                    <form method="POST" action="{{ route('caja.abrir') }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-sm-7">
                            <label class="form-label">Monto inicial (S/)</label>
                            <input type="number" step="0.01" name="monto_inicial" value="0.00" class="form-control" required>
                        </div>
                        <div class="col-sm-5 d-grid">
                            <button class="btn btn-primary"><i class="bi bi-unlock me-1"></i> Abrir caja</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Resumen de la sesión abierta --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="stat h-100"><div class="ic" style="background:#22a565"><i class="bi bi-cash"></i></div>
                <div><div class="v">S/ {{ number_format($resumen['efectivo'], 2) }}</div><div class="t">Cobrado en efectivo</div></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat h-100"><div class="ic" style="background:#155e9c"><i class="bi bi-credit-card"></i></div>
                <div><div class="v">S/ {{ number_format($resumen['tarjeta'] + $resumen['yape'] + $resumen['transferencia'], 2) }}</div><div class="t">Tarjeta / Yape / Transf.</div></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat h-100"><div class="ic" style="background:#0fb6a8"><i class="bi bi-collection"></i></div>
                <div><div class="v">S/ {{ number_format($resumen['total'], 2) }}</div><div class="t">Total cobrado en el turno</div></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat h-100"><div class="ic" style="background:#e3a72f"><i class="bi bi-wallet2"></i></div>
                <div><div class="v">S/ {{ number_format($sesion->efectivoEsperado(), 2) }}</div><div class="t">Efectivo esperado en caja</div></div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-ul me-1"></i> Movimientos del turno</span>
                    <small class="text-muted">Abierta por {{ $sesion->usuario->name ?? '—' }} · {{ $sesion->abierta_at?->format('d/m/Y H:i') }} · inicial S/ {{ number_format($sesion->monto_inicial, 2) }}</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Hora</th><th>Factura</th><th>Método</th><th>Usuario</th><th class="text-end">Monto</th></tr></thead>
                        <tbody>
                            @forelse ($movimientos as $p)
                                <tr>
                                    <td>{{ $p->created_at->format('H:i') }}</td>
                                    <td><a href="{{ route('facturacion.show', $p->factura_id) }}">{{ $p->factura->numero ?? '—' }}</a></td>
                                    <td><span class="badge bg-light text-dark">{{ ucfirst($p->metodo_pago) }}</span></td>
                                    <td>{{ $p->usuario->name ?? '—' }}</td>
                                    <td class="text-end fw-semibold">S/ {{ number_format($p->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Sin movimientos en este turno.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-lock me-1"></i> Cerrar caja (arqueo)</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Monto inicial</span><strong>S/ {{ number_format($sesion->monto_inicial, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">+ Efectivo cobrado</span><strong>S/ {{ number_format($resumen['efectivo'], 2) }}</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3"><span class="fw-semibold">Efectivo esperado</span><span class="fw-bold text-success">S/ {{ number_format($sesion->efectivoEsperado(), 2) }}</span></div>
                    <form method="POST" action="{{ route('caja.cerrar', $sesion) }}" onsubmit="return confirm('¿Cerrar la caja del turno?')">
                        @csrf
                        <label class="form-label">Efectivo contado (S/)</label>
                        <input type="number" step="0.01" name="monto_final" class="form-control mb-2" required>
                        <textarea name="notas" rows="2" class="form-control mb-2" placeholder="Observaciones (opcional)"></textarea>
                        <button class="btn btn-danger w-100"><i class="bi bi-lock me-1"></i> Cerrar caja</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Historial --}}
<div class="card mt-3">
    <div class="card-header"><i class="bi bi-clock-history me-1"></i> Últimos cierres</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Apertura</th><th>Cierre</th><th>Usuario</th><th class="text-end">Inicial</th><th class="text-end">Esperado</th><th class="text-end">Contado</th><th class="text-end">Diferencia</th></tr></thead>
            <tbody>
                @forelse ($historial as $h)
                    <tr>
                        <td>{{ $h->abierta_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $h->cerrada_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $h->usuario->name ?? '—' }}</td>
                        <td class="text-end">S/ {{ number_format($h->monto_inicial, 2) }}</td>
                        <td class="text-end">S/ {{ number_format($h->monto_esperado, 2) }}</td>
                        <td class="text-end">S/ {{ number_format($h->monto_final, 2) }}</td>
                        <td class="text-end fw-semibold {{ $h->diferencia < 0 ? 'text-danger' : ($h->diferencia > 0 ? 'text-warning' : 'text-success') }}">
                            S/ {{ number_format($h->diferencia, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay cierres registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
