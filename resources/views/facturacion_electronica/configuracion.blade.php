@extends('layouts.app')
@section('title', 'Facturación Electrónica')
@section('breadcrumb', 'Inicio / Facturación Electrónica')

@push('styles')
<style>
    .fe-banner{
        position:relative;overflow:hidden;border-radius:var(--hp-radius);
        background:linear-gradient(135deg,#155e9c,#0fb6a8);color:#fff;
        padding:1.4rem 1.6rem;box-shadow:var(--hp-shadow);
    }
    .fe-banner::after{
        content:"";position:absolute;right:-40px;top:-40px;width:220px;height:220px;
        background:radial-gradient(circle,rgba(255,255,255,.16),transparent 70%);
    }
    .fe-banner .fe-ico{
        width:60px;height:60px;border-radius:16px;display:grid;place-items:center;
        background:rgba(255,255,255,.16);font-size:1.7rem;flex:0 0 auto;
    }
    .fe-banner h2{font-weight:700;font-size:1.4rem;margin:0}
    .fe-banner p{margin:.35rem 0 0;opacity:.92;font-size:.9rem;max-width:640px}
    .fe-sunat-badge{
        background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);
        border-radius:10px;padding:.35rem .7rem;font-weight:700;letter-spacing:.5px;font-size:.8rem;
    }
    .fe-chips{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:1rem}
    .fe-chip{
        display:inline-flex;align-items:center;gap:.35rem;font-size:.78rem;font-weight:600;
        padding:.3rem .7rem;border-radius:999px;background:rgba(255,255,255,.16);
        border:1px solid rgba(255,255,255,.28);
    }
    .fe-chip.ok{background:rgba(34,197,94,.9);border-color:transparent}
    .fe-chip.bad{background:rgba(224,82,77,.92);border-color:transparent}
    .fe-chip.warn{background:rgba(227,167,47,.95);border-color:transparent;color:#3a2c00}
    .fe-btn-probar{
        background:#fff;color:#0f4c80;border:0;border-radius:10px;padding:.55rem .95rem;
        font-weight:700;font-size:.85rem;box-shadow:0 4px 12px rgba(0,0,0,.12);white-space:nowrap;
    }
    .fe-btn-probar:hover{background:#eef6ff}
    .fe-section-ico{
        width:38px;height:38px;border-radius:10px;display:grid;place-items:center;flex:0 0 auto;
        color:#fff;font-size:1.05rem;
    }
    .fe-check{border:1px solid var(--hp-border);border-radius:var(--hp-radius-sm);padding:.85rem 1rem}
</style>
@endpush

@section('content')
<h1 class="hp-pagetitle mb-3">Facturación Electrónica</h1>

{{-- ============ BANNER + ESTADO ============ --}}
<div class="fe-banner mb-3">
    <div class="d-flex align-items-start gap-3 flex-wrap">
        <div class="fe-ico"><i class="bi bi-file-earmark-text"></i></div>
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h2>Facturación Electrónica</h2>
                <span style="font-size:1.2rem">🇵🇪</span><span style="opacity:.9">Perú</span>
            </div>
            <p>Emisión de comprobantes electrónicos ante <strong>SUNAT</strong> · UBL 2.1 · Boletas, facturas y notas de crédito.</p>
        </div>
        <div class="text-end">
            <div class="fe-sunat-badge d-inline-block mb-2">SUNAT</div>
            <div>
                <form method="POST" action="{{ route('facturacion.config.probar') }}">
                    @csrf
                    <button type="submit" class="fe-btn-probar"><i class="bi bi-lightning-charge-fill me-1"></i> Probar conexión con SUNAT</button>
                </form>
            </div>
        </div>
    </div>

    <div class="fe-chips">
        <span class="fe-chip {{ $config->habilitado ? 'ok' : '' }}">
            <i class="bi bi-{{ $config->habilitado ? 'check-circle-fill' : 'slash-circle' }}"></i>
            {{ $config->habilitado ? 'Habilitada' : 'Deshabilitada' }}
        </span>
        <span class="fe-chip">Driver: {{ $config->driver ?: 'null' }}</span>
        <span class="fe-chip">Modo: {{ $config->entorno }}</span>
        <span class="fe-chip {{ $config->certificadoExiste() ? 'ok' : 'bad' }}">
            <i class="bi bi-{{ $config->certificadoExiste() ? 'shield-check' : 'x-circle' }}"></i>
            {{ $config->certificadoExiste() ? 'Certificado encontrado' : 'Certificado no encontrado' }}
        </span>
    </div>
</div>

{{-- ============ FORMULARIO ============ --}}
<form method="POST" action="{{ route('facturacion.config.update') }}">
    @csrf @method('PUT')

    {{-- Estado y modo --}}
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center gap-2">
            <span class="fe-section-ico" style="background:#0fb6a8"><i class="bi bi-lightning-charge-fill"></i></span>
            <div>
                <div class="fw-bold">Estado y modo</div>
                <small class="text-muted">Activación, forma de emisión y entorno de SUNAT</small>
            </div>
        </div>
        <div class="card-body">
            <div class="fe-check mb-3">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" name="habilitado" value="1" id="habilitado" @checked($config->habilitado)>
                    <label class="form-check-label" for="habilitado">
                        <strong>Habilitar facturación electrónica</strong><br>
                        <small class="text-muted">Si está desactivada, las ventas no generan comprobante ante SUNAT.</small>
                    </label>
                </div>
            </div>
            <div class="fe-check mb-3">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" name="emitir_automatico" value="1" id="emitir_automatico" @checked($config->emitir_automatico)>
                    <label class="form-check-label" for="emitir_automatico">
                        <strong>Emitir automáticamente al facturar la reserva</strong><br>
                        <small class="text-muted">Cada boleta o factura se envía apenas se genera desde la reserva.</small>
                    </label>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Driver de emisión</label>
                    <select name="driver" class="form-select">
                        @foreach (\App\Models\FacturacionConfig::DRIVERS as $k => $label)
                            <option value="{{ $k }}" @selected($config->driver === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Entorno SUNAT</label>
                    <select name="entorno" class="form-select">
                        @foreach (\App\Models\FacturacionConfig::ENTORNOS as $k => $label)
                            <option value="{{ $k }}" @selected($config->entorno === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Datos del emisor --}}
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center gap-2">
            <span class="fe-section-ico" style="background:#155e9c"><i class="bi bi-building"></i></span>
            <div>
                <div class="fw-bold">Datos del emisor</div>
                <small class="text-muted">Aparecen en el comprobante electrónico</small>
            </div>
        </div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">RUC <span class="text-danger">*</span></label>
                <input name="ruc" value="{{ old('ruc', $config->ruc) }}" class="form-control @error('ruc') is-invalid @enderror" placeholder="20000000001">
                @error('ruc')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Razón social <span class="text-danger">*</span></label>
                <input name="razon_social" value="{{ old('razon_social', $config->razon_social) }}" class="form-control @error('razon_social') is-invalid @enderror">
                @error('razon_social')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Nombre comercial</label>
                <input name="nombre_comercial" value="{{ old('nombre_comercial', $config->nombre_comercial) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Dirección fiscal</label>
                <input name="direccion_fiscal" value="{{ old('direccion_fiscal', $config->direccion_fiscal) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Ubigeo</label>
                <input name="ubigeo" value="{{ old('ubigeo', $config->ubigeo) }}" class="form-control" placeholder="150101">
            </div>
            <div class="col-md-6">
                <label class="form-label">Departamento</label>
                <input name="departamento" value="{{ old('departamento', $config->departamento) }}" class="form-control" placeholder="LIMA">
            </div>
            <div class="col-md-6">
                <label class="form-label">Provincia</label>
                <input name="provincia" value="{{ old('provincia', $config->provincia) }}" class="form-control" placeholder="LIMA">
            </div>
            <div class="col-md-6">
                <label class="form-label">Distrito</label>
                <input name="distrito" value="{{ old('distrito', $config->distrito) }}" class="form-control" placeholder="LIMA">
            </div>
        </div>
    </div>

    {{-- Credenciales SUNAT --}}
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center gap-2">
            <span class="fe-section-ico" style="background:#e3a72f"><i class="bi bi-key-fill"></i></span>
            <div>
                <div class="fw-bold">Credenciales SUNAT</div>
                <small class="text-muted">Clave SOL y certificado digital</small>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info d-flex align-items-center gap-2 py-2">
                <i class="bi bi-info-circle-fill"></i>
                <div>En <strong>beta</strong> puedes usar RUC <strong>20000000001</strong> con usuario y clave <strong>MODDATOS</strong>.</div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Usuario Clave SOL</label>
                    <input name="usuario_sol" value="{{ old('usuario_sol', $config->usuario_sol) }}" class="form-control" placeholder="MODDATOS">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Clave SOL</label>
                    <input type="password" name="clave_sol" class="form-control" autocomplete="new-password"
                           placeholder="{{ $config->clave_sol ? '•••••••• (guardada · deja vacío para conservar)' : '' }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Ruta del certificado (.pem)</label>
                    <input name="certificado_ruta" value="{{ old('certificado_ruta', $config->certificado_ruta) }}" class="form-control"
                           placeholder="C:\SAAS\saas_hospedaje\storage\facturacion\pe\certificate.pem">
                    @if ($config->certificado_ruta && ! $config->certificadoExiste())
                        <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i> No se encontró el certificado en la ruta indicada.</div>
                    @elseif ($config->certificadoExiste())
                        <div class="text-success small mt-1"><i class="bi bi-check-circle me-1"></i> Certificado encontrado.</div>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label">Clave del certificado</label>
                    <input type="password" name="certificado_clave" class="form-control" autocomplete="new-password"
                           placeholder="{{ $config->certificado_clave ? '•••••••• (guardada)' : '' }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Series de comprobantes --}}
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center gap-2">
            <span class="fe-section-ico" style="background:#0f4c80"><i class="bi bi-list-ol"></i></span>
            <div>
                <div class="fw-bold">Series de comprobantes</div>
                <small class="text-muted">Prefijos de numeración por tipo de documento</small>
            </div>
        </div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label">Serie boleta</label>
                <input name="serie_boleta" value="{{ old('serie_boleta', $config->serie_boleta) }}" class="form-control" placeholder="B001">
            </div>
            <div class="col-md-3">
                <label class="form-label">Serie factura</label>
                <input name="serie_factura" value="{{ old('serie_factura', $config->serie_factura) }}" class="form-control" placeholder="F001">
            </div>
            <div class="col-md-3">
                <label class="form-label">Serie nota crédito</label>
                <input name="serie_nota_credito" value="{{ old('serie_nota_credito', $config->serie_nota_credito) }}" class="form-control" placeholder="FC01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Moneda</label>
                <input name="moneda" value="{{ old('moneda', $config->moneda ?: 'PEN') }}" class="form-control" placeholder="PEN">
            </div>
        </div>
    </div>

    {{-- Acciones --}}
    <div class="card">
        <div class="card-body d-flex align-items-center justify-content-between">
            <a href="{{ route('dashboard') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> Volver</a>
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar configuración</button>
        </div>
    </div>
</form>
@endsection
