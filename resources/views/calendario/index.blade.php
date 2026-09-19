@extends('layouts.app')
@section('title', 'Calendario')
@section('breadcrumb', 'Inicio / Calendario')

@push('styles')
<style>
    .fc .fc-toolbar-title{font-size:1.15rem}
    .fc-event{cursor:pointer;font-size:.78rem;border:none}
    .fc .fc-button-primary{background:#2563eb;border-color:#2563eb}
    .fc .fc-button-primary:hover{background:#1d4ed8;border-color:#1d4ed8}
    .fc .fc-button-primary:disabled{background:#93b4f5;border-color:#93b4f5}
    .fc-day-today{background:#eff4ff !important}
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="hp-pagetitle">Calendario de Reservas</h1>
    <a href="{{ route('reservas.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Nueva Reserva</a>
</div>

<div class="card mb-3">
    <div class="card-body d-flex flex-wrap gap-3 small">
        <span><span class="badge" style="background:#6c757d">&nbsp;</span> Pendiente</span>
        <span><span class="badge" style="background:#2563eb">&nbsp;</span> Confirmada</span>
        <span><span class="badge" style="background:#22a565">&nbsp;</span> Check-in</span>
        <span><span class="badge" style="background:#16a2b8">&nbsp;</span> Check-out</span>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cal = new FullCalendar.Calendar(document.getElementById('calendar'), {
            initialView: 'dayGridMonth',
            locale: 'es',
            firstDay: 1,
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', list: 'Lista' },
            events: '{{ route('calendario.eventos') }}',
            displayEventTime: false,
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                if (info.event.url) window.location.href = info.event.url;
            }
        });
        cal.render();
    });
</script>
@endpush
