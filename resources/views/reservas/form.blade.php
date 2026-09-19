@extends('layouts.app')
@section('title', $reserva->exists ? 'Editar Reserva' : 'Nueva Reserva')
@section('breadcrumb', 'Inicio / Reservas / ' . ($reserva->exists ? 'Editar' : 'Nueva'))

@section('content')
<h1 class="hp-pagetitle mb-3">{{ $reserva->exists ? 'Editar Reserva' : 'Nueva Reserva' }}</h1>

<form method="POST" action="{{ $reserva->exists ? route('reservas.update', $reserva) : route('reservas.store') }}">
    @csrf
    @if ($reserva->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Huésped *</label>
                            <select name="huesped_id" class="form-select @error('huesped_id') is-invalid @enderror">
                                <option value="">Seleccione un huésped...</option>
                                @foreach ($huespedes as $h)
                                    <option value="{{ $h->id }}" @selected(old('huesped_id', $reserva->huesped_id)==$h->id)>
                                        {{ $h->nombre_completo }} — {{ $h->documento_numero }}
                                    </option>
                                @endforeach
                            </select>
                            @error('huesped_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">¿No está en la lista? <a href="{{ route('huespedes.create') }}">Registrar nuevo huésped</a></small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Habitación *</label>
                            <select name="habitacion_id" id="selHab" class="form-select @error('habitacion_id') is-invalid @enderror">
                                <option value="">Seleccione una habitación...</option>
                                @foreach ($habitaciones as $hab)
                                    <option value="{{ $hab->id }}" data-precio="{{ $hab->precio_noche }}"
                                        @selected(old('habitacion_id', $reserva->habitacion_id)==$hab->id)>
                                        N° {{ $hab->numero }} · {{ $hab->tipo->nombre ?? '' }} · S/ {{ number_format($hab->precio_noche,2) }} · {{ ucfirst($hab->estado) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('habitacion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-in *</label>
                            <input type="date" name="fecha_checkin" id="fIn" value="{{ old('fecha_checkin', optional($reserva->fecha_checkin)->format('Y-m-d')) }}" class="form-control @error('fecha_checkin') is-invalid @enderror">
                            @error('fecha_checkin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-out *</label>
                            <input type="date" name="fecha_checkout" id="fOut" value="{{ old('fecha_checkout', optional($reserva->fecha_checkout)->format('Y-m-d')) }}" class="form-control @error('fecha_checkout') is-invalid @enderror">
                            @error('fecha_checkout')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Adultos *</label>
                            <input type="number" name="adultos" value="{{ old('adultos', $reserva->adultos ?? 1) }}" class="form-control" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Niños *</label>
                            <input type="number" name="ninos" value="{{ old('ninos', $reserva->ninos ?? 0) }}" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tarifa / noche (S/) *</label>
                            <input type="number" step="0.01" name="tarifa_noche" id="fTarifa" value="{{ old('tarifa_noche', $reserva->tarifa_noche) }}" class="form-control @error('tarifa_noche') is-invalid @enderror">
                            @error('tarifa_noche')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado *</label>
                            <select name="estado" class="form-select">
                                @foreach (['pendiente','confirmada','checkin','checkout','cancelada'] as $e)
                                    <option value="{{ $e }}" @selected(old('estado', $reserva->estado ?? 'pendiente')===$e)>{{ ucfirst($e) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea name="notas" rows="2" class="form-control">{{ old('notas', $reserva->notas) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-calculator me-1"></i> Resumen</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Noches</span><strong id="rNoches">0</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Tarifa / noche</span><strong id="rTarifa">S/ 0.00</strong></div>
                    <div id="rTemporada" class="small mb-2" style="display:none"></div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span class="text-success" id="rTotal">S/ 0.00</span></div>
                    <div id="rDispo" class="small mt-2" style="display:none"></div>
                    <button id="btnGuardar" class="btn btn-primary w-100 mt-3"><i class="bi bi-check-lg me-1"></i> Guardar reserva</button>
                    <a href="{{ route('reservas.index') }}" class="btn btn-light w-100 mt-2">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    const selHab = document.getElementById('selHab');
    const fIn = document.getElementById('fIn'), fOut = document.getElementById('fOut');
    const fTarifa = document.getElementById('fTarifa');
    const rNoches = document.getElementById('rNoches'), rTarifa = document.getElementById('rTarifa'), rTotal = document.getElementById('rTotal');
    const rTemporada = document.getElementById('rTemporada');
    const rDispo = document.getElementById('rDispo');
    const btnGuardar = document.getElementById('btnGuardar');
    const URL_TARIFA = '{{ route('reservas.tarifa-sugerida') }}';
    const URL_DISPO = '{{ route('reservas.disponibilidad') }}';
    const RESERVA_ID = '{{ $reserva->id ?? '' }}';

    selHab.addEventListener('change', () => { sugerir(); checkDispo(); });
    fIn.addEventListener('change', () => { sugerir(); checkDispo(); });
    fOut.addEventListener('change', () => { calc(); checkDispo(); });
    fTarifa.addEventListener('change', calc);

    function checkDispo(){
        const hab = selHab.value, ci = fIn.value, co = fOut.value;
        if (!hab || !ci || !co || new Date(co) <= new Date(ci)){ rDispo.style.display = 'none'; setBloqueo(false); return; }
        let url = `${URL_DISPO}?habitacion_id=${hab}&fecha_checkin=${ci}&fecha_checkout=${co}`;
        if (RESERVA_ID) url += `&reserva_id=${RESERVA_ID}`;
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                rDispo.style.display = 'block';
                if (d.disponible){
                    rDispo.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Habitación disponible en esas fechas</span>';
                    setBloqueo(false);
                } else {
                    rDispo.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Ocupada: ya hay una reserva que se cruza con esas fechas</span>';
                    setBloqueo(true);
                }
            })
            .catch(() => { rDispo.style.display = 'none'; setBloqueo(false); });
    }

    function setBloqueo(b){
        btnGuardar.disabled = b;
        btnGuardar.classList.toggle('disabled', b);
    }

    function sugerir(){
        const hab = selHab.value, fecha = fIn.value;
        if (!hab || !fecha){ calc(); return; }
        fetch(`${URL_TARIFA}?habitacion_id=${hab}&fecha=${fecha}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                if (d.tarifa != null) fTarifa.value = parseFloat(d.tarifa).toFixed(2);
                if (d.temporada){
                    rTemporada.style.display = 'block';
                    rTemporada.innerHTML = `<span class="badge bg-warning text-dark"><i class="bi bi-tag"></i> ${d.temporada}</span> <span class="text-muted">(base S/ ${parseFloat(d.base).toFixed(2)})</span>`;
                } else {
                    rTemporada.style.display = 'none';
                }
                calc();
            })
            .catch(() => calc());
    }

    function calc(){
        let noches = 0;
        if (fIn.value && fOut.value){
            const d = (new Date(fOut.value) - new Date(fIn.value)) / 86400000;
            noches = d > 0 ? Math.round(d) : 0;
        }
        const tarifa = parseFloat(fTarifa.value) || 0;
        const total = noches * tarifa;
        rNoches.textContent = noches;
        rTarifa.textContent = 'S/ ' + tarifa.toFixed(2);
        rTotal.textContent = 'S/ ' + total.toFixed(2);
    }
    calc();
    checkDispo();
</script>
@endpush
@endsection
