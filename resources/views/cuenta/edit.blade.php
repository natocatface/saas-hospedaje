@extends('layouts.app')
@section('title', 'Mi cuenta')
@section('breadcrumb', 'Inicio / Mi cuenta')

@section('content')
<h1 class="hp-pagetitle mb-3">Mi cuenta</h1>

<form method="POST" action="{{ route('cuenta.update') }}">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-person me-1"></i> Perfil</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input name="telefono" value="{{ old('telefono', $user->telefono) }}" class="form-control">
                    </div>
                    <div class="text-muted small">Rol: <span class="badge bg-secondary">{{ ucfirst($user->rol) }}</span></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-shield-lock me-1"></i> Cambiar contraseña</div>
                <div class="card-body">
                    <p class="text-muted small">Déjalo en blanco si no deseas cambiarla.</p>
                    <div class="mb-3">
                        <label class="form-label">Contraseña actual</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar cambios</button>
    </div>
</form>
@endsection
