@extends('layouts.app')
@section('title', 'Roles y permisos')
@section('breadcrumb', 'Inicio / Roles y permisos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Roles y permisos</h1>
</div>

<div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>
    Define qué puede hacer cada rol. El <strong>Administrador</strong> tiene acceso total y no es editable.
</div>

<form method="POST" action="{{ route('roles.update') }}">
    @csrf @method('PUT')
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:240px">Permiso</th>
                        <th class="text-center">Administrador</th>
                        @foreach ($roles as $rol => $label)
                            <th class="text-center">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($catalogo as $grupo => $permisos)
                        <tr class="table-light">
                            <td colspan="{{ 2 + count($roles) }}" class="fw-bold text-uppercase small text-muted">{{ $grupo }}</td>
                        </tr>
                        @foreach ($permisos as $clave => $etiqueta)
                            <tr>
                                <td>{{ $etiqueta }} <code class="small text-muted">{{ $clave }}</code></td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success"></i></td>
                                @foreach ($roles as $rol => $label)
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input" type="checkbox"
                                                   name="permisos[{{ $rol }}][]" value="{{ $clave }}"
                                                   @checked(in_array($clave, $asignados[$rol] ?? []))>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar permisos</button>
    </div>
</form>
@endsection
