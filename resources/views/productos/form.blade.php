@extends('layouts.app')
@section('title', $producto->exists ? 'Editar Producto' : 'Nuevo Producto')
@section('breadcrumb', 'Inicio / Productos / ' . ($producto->exists ? 'Editar' : 'Nuevo'))

@section('content')
<h1 class="hp-pagetitle mb-3">{{ $producto->exists ? 'Editar Producto' : 'Nuevo Producto' }}</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $producto->exists ? route('productos.update', $producto) : route('productos.store') }}">
            @csrf
            @if ($producto->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input name="nombre" value="{{ old('nombre', $producto->nombre) }}" class="form-control @error('nombre') is-invalid @enderror">
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Categoría *</label>
                    <select name="categoria" class="form-select">
                        @foreach (['minibar','restaurante','lavanderia','servicios','otros'] as $c)
                            <option value="{{ $c }}" @selected(old('categoria', $producto->categoria ?? 'minibar')===$c)>{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Precio (S/) *</label>
                    <input type="number" step="0.01" name="precio" value="{{ old('precio', $producto->precio) }}" class="form-control @error('precio') is-invalid @enderror">
                    @error('precio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado *</label>
                    <select name="activo" class="form-select">
                        <option value="1" @selected(old('activo', $producto->activo ?? 1)==1)>Activo</option>
                        <option value="0" @selected(old('activo', $producto->activo ?? 1)==0)>Inactivo</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" rows="2" class="form-control">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                <a href="{{ route('productos.index') }}" class="btn btn-light">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
