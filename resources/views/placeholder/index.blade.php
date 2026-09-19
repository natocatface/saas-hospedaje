@extends('layouts.app')
@section('title', $titulo)
@section('breadcrumb', 'Inicio / ' . $titulo)

@section('content')
<h1 class="hp-pagetitle mb-4">{{ $titulo }}</h1>

<div class="card">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                  style="width:96px;height:96px;background:#eef2ff;color:#2563eb;font-size:2.6rem">
                <i class="bi bi-{{ $icono }}"></i>
            </span>
        </div>
        <h4 class="fw-bold">{{ $titulo }}</h4>
        <p class="text-muted mb-1">{{ $desc }}</p>
        <span class="badge bg-warning text-dark"><i class="bi bi-cone-striped me-1"></i> Módulo en construcción</span>
    </div>
</div>
@endsection
