@extends('layouts.app')
@section('title', $tarifa->exists ? 'Editar Temporada' : 'Nueva Temporada')
@section('breadcrumb', 'Inicio / Tarifas Temporada / ' . ($tarifa->exists ? 'Editar' : 'Nueva'))

@section('content')
<h1 class="hp-pagetitle mb-3">{{ $tarifa->exists ? 'Editar Temporada' : 'Nueva Temporada' }}</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $tarifa->exists ? route('tarifas.update', $tarifa) : route('tarifas.store') }}">
            @csrf
            @if ($tarifa->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nombre *</label>
                    <input name="nombre" value="{{ old('nombre', $tarifa->nombre) }}" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej. Temporada Alta - Fiestas Patrias">
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo" class="form-select">
                        @foreach (['alta'=>'Alta','baja'=>'Baja','especial'=>'Especial'] as $v=>$l)
                            <option value="{{ $v }}" @selected(old('tipo', $tarifa->tipo ?? 'alta')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Desde *</label>
                    <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', optional($tarifa->fecha_inicio)->format('Y-m-d')) }}" class="form-control @error('fecha_inicio') is-invalid @enderror">
                    @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hasta *</label>
                    <input type="date" name="fecha_fin" value="{{ old('fecha_fin', optional($tarifa->fecha_fin)->format('Y-m-d')) }}" class="form-control @error('fecha_fin') is-invalid @enderror">
                    @error('fecha_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado *</label>
                    <select name="activo" class="form-select">
                        <option value="1" @selected(old('activo', $tarifa->activo ?? 1)==1)>Activa</option>
                        <option value="0" @selected(old('activo', $tarifa->activo ?? 1)==0)>Inactiva</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo de ajuste *</label>
                    <select name="ajuste_tipo" class="form-select">
                        <option value="porcentaje" @selected(old('ajuste_tipo', $tarifa->ajuste_tipo ?? 'porcentaje')==='porcentaje')>Porcentaje (%)</option>
                        <option value="fijo" @selected(old('ajuste_tipo', $tarifa->ajuste_tipo ?? '')==='fijo')>Monto fijo (S/)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Valor del ajuste *</label>
                    <input type="number" step="0.01" name="ajuste_valor" value="{{ old('ajuste_valor', $tarifa->ajuste_valor) }}" class="form-control @error('ajuste_valor') is-invalid @enderror" placeholder="Ej. 20 (sube 20%) o -10 (baja 10%)">
                    @error('ajuste_valor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Usa positivo para recargo y negativo para descuento.</small>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                <a href="{{ route('tarifas.index') }}" class="btn btn-light">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
