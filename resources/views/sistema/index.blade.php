@extends('layouts.app')
@section('title', 'Backup & Sistema')
@section('breadcrumb', 'Inicio / Backup & Sistema')

@section('content')
<h1 class="hp-pagetitle mb-3">Backup &amp; Sistema</h1>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-info-circle me-1"></i> Información del sistema</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Versión PHP</td><td class="text-end fw-semibold">{{ $info['php'] }}</td></tr>
                    <tr><td class="text-muted">Versión Laravel</td><td class="text-end fw-semibold">{{ $info['laravel'] }}</td></tr>
                    <tr><td class="text-muted">Base de datos</td><td class="text-end fw-semibold">{{ $info['base_datos'] }}</td></tr>
                    <tr><td class="text-muted">Servidor</td><td class="text-end fw-semibold">{{ $info['host'] }}</td></tr>
                    <tr><td class="text-muted">Entorno</td><td class="text-end fw-semibold">{{ ucfirst($info['entorno']) }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-database me-1"></i> Registros almacenados</div>
            <div class="card-body">
                <div class="row text-center g-2">
                    @foreach ($conteos as $label => $n)
                        <div class="col-4">
                            <div class="border rounded py-2">
                                <div class="fs-4 fw-bold text-primary">{{ $n }}</div>
                                <div class="small text-muted">{{ $label }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-hdd-stack me-1"></i> Respaldo de base de datos</div>
            <div class="card-body">
                <p class="text-muted small">Descarga un archivo <code>.sql</code> con la estructura y los datos completos de la base. Guárdalo en un lugar seguro.</p>
                <a href="{{ route('sistema.backup') }}" class="btn btn-primary"><i class="bi bi-download me-1"></i> Descargar respaldo SQL</a>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-arrow-clockwise me-1"></i> Mantenimiento</div>
            <div class="card-body">
                <p class="text-muted small">Limpia la caché de configuración, rutas y vistas. Útil tras cambios de configuración.</p>
                <form method="POST" action="{{ route('sistema.cache') }}">
                    @csrf
                    <button class="btn btn-outline-secondary"><i class="bi bi-trash3 me-1"></i> Limpiar caché</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
