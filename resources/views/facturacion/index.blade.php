@extends('layouts.app')
@section('title', 'Facturación')
@section('breadcrumb', 'Inicio / Facturación')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="hp-pagetitle mb-0">Facturación</h1>
    <a href="{{ route('facturacion.resumen') }}" class="btn btn-outline-primary"><i class="bi bi-collection me-1"></i> Resumen diario de boletas</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat h-100">
            <div class="ic" style="background:#22a565"><i class="bi bi-cash-coin"></i></div>
            <div><div class="v">S/ {{ number_format($resumen['total_pagado'], 2) }}</div><div class="t">Total cobrado</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat h-100">
            <div class="ic" style="background:#fd7e14"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="v">S/ {{ number_format($resumen['total_pendiente'], 2) }}</div><div class="t">Por cobrar</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat h-100">
            <div class="ic" style="background:#e0524d"><i class="bi bi-receipt"></i></div>
            <div><div class="v">{{ $resumen['n_pendientes'] }}</div><div class="t">Facturas pendientes</div></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por número o huésped...">
            </div>
            <div class="col-md-3">
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    @foreach (['pendiente','pagada','anulada'] as $e)
                        <option value="{{ $e }}" @selected(request('estado')===$e)>{{ ucfirst($e) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="mes" class="form-select">
                    <option value="">Todo el año</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected(request('mes')==$m)>{{ ucfirst(\Illuminate\Support\Carbon::create()->month($m)->locale('es')->isoFormat('MMMM')) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-outline-primary"><i class="bi bi-search me-1"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Número</th><th>Huésped</th><th>Fecha</th><th>Subtotal</th><th>IGV</th><th>Total</th><th>Método</th><th>Estado</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                @forelse ($facturas as $f)
                    <tr>
                        <td class="fw-semibold">{{ $f->numero }}</td>
                        <td>{{ $f->reserva->huesped->nombre_completo ?? '—' }}</td>
                        <td>{{ $f->fecha->format('d/m/Y') }}</td>
                        <td>S/ {{ number_format($f->subtotal, 2) }}</td>
                        <td>S/ {{ number_format($f->igv, 2) }}</td>
                        <td class="fw-semibold">S/ {{ number_format($f->total, 2) }}</td>
                        <td>{{ $f->metodo_pago ? ucfirst($f->metodo_pago) : '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $f->estado==='pagada' ? 'success' : ($f->estado==='anulada' ? 'secondary' : 'warning') }}">
                                {{ ucfirst($f->estado) }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('facturacion.show', $f) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('facturacion.imprimir', $f) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No hay facturas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $facturas->links() }}</div>
@endsection
