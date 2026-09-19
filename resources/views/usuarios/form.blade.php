@extends('layouts.app')
@section('title', $usuario->exists ? 'Editar Usuario' : 'Nuevo Usuario')
@section('breadcrumb', 'Inicio / Usuarios / ' . ($usuario->exists ? 'Editar' : 'Nuevo'))

@section('content')
<h1 class="hp-pagetitle mb-3">{{ $usuario->exists ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $usuario->exists ? route('usuarios.update', $usuario) : route('usuarios.store') }}">
            @csrf
            @if ($usuario->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input name="name" value="{{ old('name', $usuario->name) }}" class="form-control @error('name') is-invalid @enderror">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Correo *</label>
                    <input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="form-control @error('email') is-invalid @enderror">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono', $usuario->telefono) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rol *</label>
                    <select name="rol" class="form-select">
                        @foreach (['admin'=>'Administrador','recepcionista'=>'Recepcionista','housekeeping'=>'Housekeeping'] as $val=>$lbl)
                            <option value="{{ $val }}" @selected(old('rol', $usuario->rol ?? 'recepcionista')===$val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado *</label>
                    <select name="activo" class="form-select">
                        <option value="1" @selected(old('activo', $usuario->activo ?? 1)==1)>Activo</option>
                        <option value="0" @selected(old('activo', $usuario->activo ?? 1)==0)>Inactivo</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ $usuario->exists ? 'Nueva contraseña (opcional)' : 'Contraseña *' }}</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-light">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
