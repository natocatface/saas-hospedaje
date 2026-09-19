@extends('layouts.app')
@section('title', $habitacion->exists ? 'Editar Habitación' : 'Nueva Habitación')
@section('breadcrumb', 'Inicio / Habitaciones / ' . ($habitacion->exists ? 'Editar' : 'Nueva'))

@section('content')
<h1 class="hp-pagetitle mb-3">{{ $habitacion->exists ? 'Editar Habitación' : 'Nueva Habitación' }}</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $habitacion->exists ? route('habitaciones.update', $habitacion) : route('habitaciones.store') }}">
            @csrf
            @if ($habitacion->exists) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Número *</label>
                    <input type="text" name="numero" value="{{ old('numero', $habitacion->numero) }}" class="form-control @error('numero') is-invalid @enderror">
                    @error('numero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label">Tipo *</label>
                    <select name="tipo_habitacion_id" class="form-select @error('tipo_habitacion_id') is-invalid @enderror">
                        <option value="">Seleccione...</option>
                        @foreach ($tipos as $t)
                            <option value="{{ $t->id }}" data-precio="{{ $t->precio_base }}" data-cap="{{ $t->capacidad }}"
                                @selected(old('tipo_habitacion_id', $habitacion->tipo_habitacion_id)==$t->id)>
                                {{ $t->nombre }} (S/ {{ number_format($t->precio_base,2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_habitacion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Piso *</label>
                    <input type="number" name="piso" value="{{ old('piso', $habitacion->piso ?? 1) }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Capacidad *</label>
                    <input type="number" name="capacidad" value="{{ old('capacidad', $habitacion->capacidad ?? 1) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Precio por noche (S/) *</label>
                    <input type="number" step="0.01" name="precio_noche" value="{{ old('precio_noche', $habitacion->precio_noche) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado *</label>
                    <select name="estado" class="form-select">
                        @foreach (['disponible','ocupada','mantenimiento','limpieza'] as $e)
                            <option value="{{ $e }}" @selected(old('estado', $habitacion->estado ?? 'disponible')===$e)>{{ ucfirst($e) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" rows="2" class="form-control">{{ old('descripcion', $habitacion->descripcion) }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                <a href="{{ route('habitaciones.index') }}" class="btn btn-light">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.querySelector('[name=tipo_habitacion_id]').addEventListener('change', function(){
        const opt = this.options[this.selectedIndex];
        if (opt.dataset.precio) document.querySelector('[name=precio_noche]').value = opt.dataset.precio;
        if (opt.dataset.cap) document.querySelector('[name=capacidad]').value = opt.dataset.cap;
    });
</script>
@endpush
@endsection
