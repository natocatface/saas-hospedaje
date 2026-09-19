@extends('layouts.app')
@section('title', 'Housekeeping')
@section('breadcrumb', 'Inicio / Housekeeping')

@php
    $cols = [
        'limpieza'      => ['Por limpiar', 'info', 'brush'],
        'mantenimiento' => ['Mantenimiento', 'warning', 'tools'],
        'ocupada'       => ['Ocupadas', 'danger', 'door-closed-fill'],
        'disponible'    => ['Disponibles', 'success', 'door-open-fill'],
    ];
    // Acciones permitidas por estado de origen
    $acciones = [
        'limpieza'      => ['disponible' => 'Marcar lista', 'mantenimiento' => 'Enviar a mantenimiento'],
        'mantenimiento' => ['limpieza' => 'Enviar a limpieza', 'disponible' => 'Marcar lista'],
        'ocupada'       => ['limpieza' => 'Liberar (a limpieza)'],
        'disponible'    => ['limpieza' => 'Marcar para limpieza', 'mantenimiento' => 'Enviar a mantenimiento'],
    ];
@endphp

@section('content')
<h1 class="hp-pagetitle mb-3">Housekeeping — Estado de Habitaciones</h1>

<div class="row g-3 mb-3">
    @foreach ($cols as $estado => [$titulo, $color, $icono])
        <div class="col-6 col-xl-3">
            <div class="stat h-100">
                <div class="ic bg-{{ $color }}"><i class="bi bi-{{ $icono }}"></i></div>
                <div><div class="v">{{ $resumen[$estado] }}</div><div class="t">{{ $titulo }}</div></div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    @foreach ($cols as $estado => [$titulo, $color, $icono])
        <div class="col-lg-3 col-md-6">
            <div class="card h-100">
                <div class="card-header bg-{{ $color }} text-white d-flex justify-content-between">
                    <span><i class="bi bi-{{ $icono }} me-1"></i> {{ $titulo }}</span>
                    <span class="badge bg-light text-dark">{{ $resumen[$estado] }}</span>
                </div>
                <div class="card-body p-2" style="max-height:560px;overflow-y:auto">
                    @forelse (($habitaciones[$estado] ?? []) as $h)
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold">N° {{ $h->numero }}</span>
                                <small class="text-muted">{{ $h->tipo->nombre ?? '' }}</small>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                @foreach (($acciones[$estado] ?? []) as $destino => $label)
                                    <form method="POST" action="{{ route('housekeeping.estado', $h) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="estado" value="{{ $destino }}">
                                        <button class="btn btn-outline-secondary btn-sm py-0">{{ $label }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center small py-3 mb-0">Sin habitaciones</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
