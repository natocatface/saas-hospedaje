@extends('layouts.app')
@section('title', 'Configuración')
@section('breadcrumb', 'Inicio / Configuración')

@section('content')
<h1 class="hp-pagetitle mb-3">Configuración del Sistema</h1>

<form method="POST" action="{{ route('configuracion.update') }}">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-building me-1"></i> Datos del establecimiento</div>
                <div class="card-body row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Nombre del hotel *</label>
                        <input name="nombre_hotel" value="{{ old('nombre_hotel', $config['nombre_hotel'] ?? '') }}" class="form-control @error('nombre_hotel') is-invalid @enderror">
                        @error('nombre_hotel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">RUC</label>
                        <input name="ruc" value="{{ old('ruc', $config['ruc'] ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input name="telefono" value="{{ old('telefono', $config['telefono'] ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Dirección</label>
                        <input name="direccion" value="{{ old('direccion', $config['direccion'] ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Email de contacto</label>
                        <input type="email" name="email" value="{{ old('email', $config['email'] ?? '') }}" class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><i class="bi bi-sliders me-1"></i> Parámetros</div>
                <div class="card-body row g-3">
                    <div class="col-6">
                        <label class="form-label">Símbolo moneda *</label>
                        <input name="simbolo_moneda" value="{{ old('simbolo_moneda', $config['simbolo_moneda'] ?? 'S/') }}" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">IGV (%) *</label>
                        <input type="number" step="0.01" name="igv" value="{{ old('igv', $config['igv'] ?? '18') }}" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Hora check-in</label>
                        <input type="time" name="checkin_hora" value="{{ old('checkin_hora', $config['checkin_hora'] ?? '14:00') }}" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Hora check-out</label>
                        <input type="time" name="checkout_hora" value="{{ old('checkout_hora', $config['checkout_hora'] ?? '12:00') }}" class="form-control">
                    </div>
                </div>
            </div>
            <button class="btn btn-primary w-100 mt-3"><i class="bi bi-check-lg me-1"></i> Guardar configuración</button>
        </div>
    </div>
</form>
@endsection
