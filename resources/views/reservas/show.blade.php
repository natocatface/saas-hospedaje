@extends('layouts.app')
@section('title', 'Reserva ' . $reserva->codigo)
@section('breadcrumb', 'Inicio / Reservas / ' . $reserva->codigo)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Reserva {{ $reserva->codigo }}</h1>
    <div>
        <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Editar</a>
        <a href="{{ route('reservas.index') }}" class="btn btn-light">Volver</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header">Detalle de la reserva</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Huésped</small><strong>{{ $reserva->huesped->nombre_completo }}</strong></div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Documento</small>{{ $reserva->huesped->documento_tipo }} {{ $reserva->huesped->documento_numero }}</div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Habitación</small>N° {{ $reserva->habitacion->numero }} · {{ $reserva->habitacion->tipo->nombre ?? '' }}</div>
                    <div class="col-md-6 mb-2"><small class="text-muted d-block">Estado</small><span class="badge bg-{{ $reserva->badgeColor() }}">{{ ucfirst($reserva->estado) }}</span></div>
                    <div class="col-md-3 mb-2"><small class="text-muted d-block">Check-in</small>{{ $reserva->fecha_checkin->format('d/m/Y') }}</div>
                    <div class="col-md-3 mb-2"><small class="text-muted d-block">Check-out</small>{{ $reserva->fecha_checkout->format('d/m/Y') }}</div>
                    <div class="col-md-3 mb-2"><small class="text-muted d-block">Noches</small>{{ $reserva->noches }}</div>
                    <div class="col-md-3 mb-2"><small class="text-muted d-block">Huéspedes</small>{{ $reserva->adultos }} adultos, {{ $reserva->ninos }} niños</div>
                    @if ($reserva->notas)
                        <div class="col-12 mt-2"><small class="text-muted d-block">Notas</small>{{ $reserva->notas }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Consumos / cargos extra --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cart-plus me-1"></i> Consumos y cargos</span>
                <span class="fw-semibold">Total: S/ {{ number_format($reserva->totalConsumos(), 2) }}</span>
            </div>
            <div class="card-body">
                @if ($reserva->estado !== 'cancelada')
                    <form method="POST" action="{{ route('reservas.consumos.store', $reserva) }}" class="row g-2 align-items-end mb-3">
                        @csrf
                        <div class="col-md-7">
                            <label class="form-label">Producto / servicio</label>
                            <select name="producto_id" class="form-select" required>
                                <option value="">Seleccione...</option>
                                @foreach ($productos as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->nombre }} — S/ {{ number_format($prod->precio, 2) }} ({{ ucfirst($prod->categoria) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" name="cantidad" value="1" min="1" max="99" class="form-control" required>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-primary"><i class="bi bi-plus-lg"></i> Agregar</button>
                        </div>
                    </form>
                    @if (! $productos->count())
                        <div class="alert alert-warning small mb-3"><i class="bi bi-exclamation-triangle me-1"></i> No hay productos activos. <a href="{{ route('productos.create') }}">Crea el catálogo</a> primero.</div>
                    @endif
                @endif

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Producto</th><th>Categoría</th><th class="text-center">Cant.</th><th class="text-end">P. unit</th><th class="text-end">Subtotal</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($reserva->consumos as $c)
                                <tr>
                                    <td>{{ $c->descripcion }}</td>
                                    <td><span class="badge bg-light text-dark">{{ ucfirst($c->producto->categoria ?? '—') }}</span></td>
                                    <td class="text-center">{{ $c->cantidad }}</td>
                                    <td class="text-end">S/ {{ number_format($c->precio_unit, 2) }}</td>
                                    <td class="text-end fw-semibold">S/ {{ number_format($c->subtotal, 2) }}</td>
                                    <td class="text-end">
                                        @if ($reserva->estado !== 'cancelada')
                                        <form action="{{ route('reservas.consumos.destroy', [$reserva, $c]) }}" method="POST" onsubmit="return confirm('¿Quitar este consumo?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">Sin consumos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Cambiar estado</div>
            <div class="card-body d-flex flex-wrap gap-2">
                @foreach (['confirmada'=>'primary','checkin'=>'success','checkout'=>'info','cancelada'=>'danger'] as $estado => $color)
                    <form method="POST" action="{{ route('reservas.estado', $reserva) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="estado" value="{{ $estado }}">
                        <button class="btn btn-outline-{{ $color }} btn-sm">{{ ucfirst($estado) }}</button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-receipt me-1"></i> Resumen de cuenta</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Hospedaje ({{ $reserva->noches }} noches)</span><span>S/ {{ number_format($reserva->total, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">Consumos extra</span><span>S/ {{ number_format($reserva->totalConsumos(), 2) }}</span></div>
                <hr>
                <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total cuenta</span><span class="text-success">S/ {{ number_format($reserva->totalCuenta(), 2) }}</span></div>
                @if ($reserva->factura)
                    <hr>
                    <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Factura {{ $reserva->factura->numero }}</span>
                        <span class="badge bg-{{ $reserva->factura->estado === 'pagada' ? 'success' : ($reserva->factura->estado==='anulada' ? 'secondary':'warning') }}">{{ ucfirst($reserva->factura->estado) }}</span></div>
                    <div class="d-flex justify-content-between small"><span class="text-muted">Saldo</span><span class="fw-semibold">S/ {{ number_format($reserva->factura->saldo(), 2) }}</span></div>
                    <a href="{{ route('facturacion.show', $reserva->factura) }}" class="btn btn-outline-primary btn-sm w-100 mt-3"><i class="bi bi-cash-coin me-1"></i> Ver factura / cobrar</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
