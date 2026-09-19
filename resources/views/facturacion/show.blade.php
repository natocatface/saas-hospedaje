@extends('layouts.app')
@section('title', 'Factura ' . $factura->numero)
@section('breadcrumb', 'Inicio / Facturación / ' . $factura->numero)

@section('content')
@php $saldo = $factura->saldo(); $pagado = $factura->montoPagado(); @endphp
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Factura {{ $factura->numero }}</h1>
    <div>
        <a href="{{ route('facturacion.imprimir', $factura) }}" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i> Imprimir</a>
        <a href="{{ route('facturacion') }}" class="btn btn-light">Volver</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Detalle</span>
                <span class="badge bg-{{ $factura->estado==='pagada' ? 'success' : ($factura->estado==='anulada' ? 'secondary' : 'warning') }}">{{ ucfirst($factura->estado) }}</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Huésped</small><strong>{{ $factura->reserva->huesped->nombre_completo ?? '—' }}</strong></div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Documento</small>{{ $factura->reserva->huesped->documento_tipo ?? '' }} {{ $factura->reserva->huesped->documento_numero ?? '' }}</div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Reserva</small>{{ $factura->reserva->codigo ?? '—' }} · Hab. {{ $factura->reserva->habitacion->numero ?? '' }}</div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Fecha</small>{{ $factura->fecha->format('d/m/Y') }}</div>
                </div>
                <table class="table mt-2 mb-0">
                    <tbody>
                        <tr><td>Subtotal</td><td class="text-end">S/ {{ number_format($factura->subtotal, 2) }}</td></tr>
                        <tr><td>IGV (18%)</td><td class="text-end">S/ {{ number_format($factura->igv, 2) }}</td></tr>
                        <tr class="fw-bold fs-5"><td>Total</td><td class="text-end">S/ {{ number_format($factura->total, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Historial de pagos --}}
        <div class="card">
            <div class="card-header"><i class="bi bi-cash-coin me-1"></i> Pagos registrados</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Fecha</th><th>Método</th><th>Usuario</th><th class="text-end">Monto</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($factura->pagos as $p)
                            <tr>
                                <td>{{ $p->fecha->format('d/m/Y') }}</td>
                                <td><span class="badge bg-light text-dark">{{ ucfirst($p->metodo_pago) }}</span></td>
                                <td>{{ $p->usuario->name ?? '—' }}</td>
                                <td class="text-end fw-semibold">S/ {{ number_format($p->monto, 2) }}</td>
                                <td class="text-end">
                                    @if (auth()->user()->esAdmin() && $factura->estado !== 'anulada')
                                    <form action="{{ route('facturacion.pago.destroy', [$factura, $p]) }}" method="POST" onsubmit="return confirm('¿Eliminar este pago?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Aún no hay pagos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-wallet2 me-1"></i> Estado de cuenta</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Total factura</span><span>S/ {{ number_format($factura->total, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Pagado</span><span class="text-success">S/ {{ number_format($pagado, 2) }}</span></div>
                <hr>
                <div class="d-flex justify-content-between fs-5 fw-bold"><span>Saldo</span><span class="{{ $saldo > 0 ? 'text-danger' : 'text-success' }}">S/ {{ number_format($saldo, 2) }}</span></div>
                @php $pct = $factura->total > 0 ? min(100, round($pagado / (float)$factura->total * 100)) : 100; @endphp
                <div class="progress mt-2" style="height:8px"><div class="progress-bar bg-success" style="width:{{ $pct }}%"></div></div>
            </div>
        </div>

        {{-- ============ Comprobante electrónico SUNAT ============ --}}
        @php
            $sc = $factura->sunat_estado ?? 'no_emitido';
            $scColor = match ($sc) {
                'aceptado'  => 'success',
                'observado' => 'info',
                'enviado'   => 'primary',
                'pendiente' => 'warning',
                'rechazado' => 'danger',
                'anulado'   => 'secondary',
                default     => 'light',
            };
            $puedeEmitir = $feConfig->habilitado && $feConfig->driver !== 'ninguno'
                && ! $factura->aceptadaPorSunat() && $factura->estado !== 'anulada';
        @endphp
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-file-earmark-text me-1"></i> Comprobante SUNAT</span>
                <span class="badge bg-{{ $scColor }} {{ $scColor==='light' ? 'text-dark' : '' }}">{{ $factura->sunatEstadoLabel() }}</span>
            </div>
            <div class="card-body">
                @if ($factura->comprobanteNumero())
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Comprobante</span>
                        <strong>{{ ucfirst($factura->tipo_comprobante ?? '') }} {{ $factura->comprobanteNumero() }}</strong>
                    </div>
                @endif
                @if ($factura->sunat_hash)
                    <div class="mb-2"><small class="text-muted d-block">Hash</small><code class="small">{{ \Illuminate\Support\Str::limit($factura->sunat_hash, 32) }}</code></div>
                @endif
                @if ($factura->sunat_mensaje)
                    <div class="alert alert-{{ in_array($sc,['rechazado']) ? 'danger' : 'light' }} py-2 px-3 small mb-2">{{ $factura->sunat_mensaje }}</div>
                @endif

                @if (! $feConfig->habilitado)
                    <div class="text-muted small mb-2"><i class="bi bi-info-circle me-1"></i> Facturación electrónica desactivada.
                        @if (auth()->user()->esAdmin())<a href="{{ route('facturacion.config') }}">Configurar</a>@endif
                    </div>
                @endif

                @if ($puedeEmitir)
                    <form method="POST" action="{{ route('facturacion.emitir', $factura) }}" onsubmit="return confirm('¿Emitir este comprobante ante SUNAT?')">
                        @csrf
                        <button class="btn btn-primary w-100">
                            <i class="bi bi-cloud-arrow-up me-1"></i>
                            {{ $sc === 'rechazado' ? 'Reintentar envío a SUNAT' : 'Emitir a SUNAT' }}
                        </button>
                    </form>
                @endif

                @if ($factura->esBoleta() && $sc === 'pendiente')
                    <a href="{{ route('facturacion.resumen', ['fecha' => $factura->fecha->toDateString()]) }}" class="btn btn-outline-primary w-100 mt-2">
                        <i class="bi bi-collection me-1"></i> Declarar en Resumen diario
                    </a>
                @endif

                @if ($factura->sunat_xml_path || $factura->sunat_cdr_path)
                    <div class="d-flex gap-2 mt-2">
                        @if ($factura->sunat_xml_path)
                            <a href="{{ route('facturacion.xml', $factura) }}" class="btn btn-sm btn-outline-secondary flex-fill"><i class="bi bi-filetype-xml me-1"></i> XML</a>
                        @endif
                        @if ($factura->sunat_cdr_path)
                            <a href="{{ route('facturacion.cdr', $factura) }}" class="btn btn-sm btn-outline-secondary flex-fill"><i class="bi bi-file-earmark-zip me-1"></i> CDR</a>
                        @endif
                    </div>
                @endif

                @if ($factura->aceptadaPorSunat() && auth()->user()->puede('facturacion.anular'))
                    <hr>
                    <form method="POST" action="{{ route('facturacion.nota-credito', $factura) }}" onsubmit="return confirm('¿Emitir nota de crédito para anular el comprobante?')">
                        @csrf
                        <label class="form-label small">Anular con nota de crédito</label>
                        <input name="motivo" class="form-control form-control-sm mb-2" placeholder="Motivo de anulación" required>
                        <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-file-earmark-minus me-1"></i> Emitir nota de crédito</button>
                    </form>
                @endif
            </div>
        </div>

        @if ($factura->estado !== 'anulada' && $saldo > 0)
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-plus-circle me-1"></i> Registrar pago</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('facturacion.pago', $factura) }}">
                        @csrf
                        <label class="form-label">Monto (S/)</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $saldo }}" name="monto" value="{{ number_format($saldo, 2, '.', '') }}" class="form-control mb-2" required>
                        <label class="form-label">Método de pago</label>
                        <select name="metodo_pago" class="form-select mb-2" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="yape">Yape</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                        <input name="nota" class="form-control mb-2" placeholder="Nota (opcional)">
                        <button class="btn btn-success w-100"><i class="bi bi-check2-circle me-1"></i> Registrar pago</button>
                    </form>
                    <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i> Si abonas menos del saldo, la factura queda con pago parcial.</small>
                </div>
            </div>
        @elseif ($factura->estado === 'pagada')
            <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> Factura cancelada en su totalidad.</div>
        @endif

        @if ($factura->estado === 'pendiente' && auth()->user()->puede('facturacion.anular'))
            <form method="POST" action="{{ route('facturacion.anular', $factura) }}" onsubmit="return confirm('¿Anular esta factura?')">
                @csrf @method('PATCH')
                <button class="btn btn-outline-danger w-100"><i class="bi bi-x-circle me-1"></i> Anular factura</button>
            </form>
        @elseif ($factura->estado === 'anulada')
            <div class="alert alert-secondary mb-0"><i class="bi bi-slash-circle me-1"></i> Factura anulada.</div>
        @endif
    </div>
</div>
@endsection
