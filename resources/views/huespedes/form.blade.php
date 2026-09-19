@extends('layouts.app')
@section('title', $huesped->exists ? 'Editar Huésped' : 'Nuevo Huésped')
@section('breadcrumb', 'Inicio / Huéspedes / ' . ($huesped->exists ? 'Editar' : 'Nuevo'))

@section('content')
<h1 class="hp-pagetitle mb-3">{{ $huesped->exists ? 'Editar Huésped' : 'Nuevo Huésped' }}</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $huesped->exists ? route('huespedes.update', $huesped) : route('huespedes.store') }}">
            @csrf
            @if ($huesped->exists) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombres *</label>
                    <input name="nombres" value="{{ old('nombres', $huesped->nombres) }}" class="form-control @error('nombres') is-invalid @enderror">
                    @error('nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apellidos *</label>
                    <input name="apellidos" value="{{ old('apellidos', $huesped->apellidos) }}" class="form-control @error('apellidos') is-invalid @enderror">
                    @error('apellidos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo Doc. *</label>
                    <select name="documento_tipo" class="form-select">
                        @foreach (['DNI','CE','Pasaporte','RUC'] as $d)
                            <option value="{{ $d }}" @selected(old('documento_tipo', $huesped->documento_tipo)===$d)>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° Documento *</label>
                    <input name="documento_numero" value="{{ old('documento_numero', $huesped->documento_numero) }}" class="form-control @error('documento_numero') is-invalid @enderror">
                    @error('documento_numero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono', $huesped->telefono) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nacionalidad *</label>
                    <input name="nacionalidad" value="{{ old('nacionalidad', $huesped->nacionalidad ?? 'Peru') }}" class="form-control">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $huesped->email) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($huesped->fecha_nacimiento)->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Dirección</label>
                    <input name="direccion" value="{{ old('direccion', $huesped->direccion) }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Notas</label>
                    <textarea name="notas" rows="2" class="form-control">{{ old('notas', $huesped->notas) }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                <a href="{{ route('huespedes.index') }}" class="btn btn-light">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
