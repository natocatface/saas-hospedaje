<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $factura->numero }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{font-family:'Segoe UI',sans-serif;color:#222;font-size:14px}
        .wrap{max-width:720px;margin:24px auto;padding:0 16px}
        .hd{border-bottom:3px solid #2563eb;padding-bottom:12px;margin-bottom:18px}
        @media print{.no-print{display:none}}
    </style>
</head>
<body onload="window.print()">
<div class="wrap">
    <div class="d-flex justify-content-between hd">
        <div>
            <h3 class="mb-0 text-primary">{{ $config['nombre_hotel'] }}</h3>
            <div class="text-muted small">
                @if($config['ruc']) RUC: {{ $config['ruc'] }}<br>@endif
                {{ $config['direccion'] }}<br>
                {{ $config['telefono'] }}
            </div>
        </div>
        <div class="text-end">
            <div class="border rounded p-2">
                <div class="fw-bold">COMPROBANTE</div>
                <div class="text-primary fw-bold">{{ $factura->numero }}</div>
                <div class="small text-muted">{{ $factura->fecha->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-6">
            <small class="text-muted">Cliente</small>
            <div class="fw-semibold">{{ $factura->reserva->huesped->nombre_completo ?? '—' }}</div>
            <div class="small">{{ $factura->reserva->huesped->documento_tipo ?? '' }}: {{ $factura->reserva->huesped->documento_numero ?? '' }}</div>
        </div>
        <div class="col-6 text-end">
            <small class="text-muted">Reserva</small>
            <div>{{ $factura->reserva->codigo ?? '' }}</div>
            <div class="small">Habitación {{ $factura->reserva->habitacion->numero ?? '' }}</div>
        </div>
    </div>

    <table class="table table-sm">
        <thead class="table-light">
            <tr><th>Descripción</th><th class="text-center">Noches</th><th class="text-end">Tarifa</th><th class="text-end">Importe</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Hospedaje hab. {{ $factura->reserva->habitacion->numero ?? '' }} ({{ $factura->reserva->habitacion->tipo->nombre ?? '' }})<br>
                    <small class="text-muted">{{ optional($factura->reserva->fecha_checkin)->format('d/m/Y') }} → {{ optional($factura->reserva->fecha_checkout)->format('d/m/Y') }}</small></td>
                <td class="text-center">{{ $factura->reserva->noches ?? '' }}</td>
                <td class="text-end">S/ {{ number_format($factura->reserva->tarifa_noche ?? 0, 2) }}</td>
                <td class="text-end">S/ {{ number_format($factura->reserva->total ?? 0, 2) }}</td>
            </tr>
            @foreach (($factura->reserva->consumos ?? []) as $c)
            <tr>
                <td>{{ $c->descripcion }} <small class="text-muted">({{ ucfirst($c->producto->categoria ?? 'extra') }})</small></td>
                <td class="text-center">{{ $c->cantidad }}</td>
                <td class="text-end">S/ {{ number_format($c->precio_unit, 2) }}</td>
                <td class="text-end">S/ {{ number_format($c->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row justify-content-end">
        <div class="col-6">
            <table class="table table-sm">
                <tr><td>Subtotal</td><td class="text-end">S/ {{ number_format($factura->subtotal, 2) }}</td></tr>
                <tr><td>IGV (18%)</td><td class="text-end">S/ {{ number_format($factura->igv, 2) }}</td></tr>
                <tr class="fw-bold"><td>TOTAL</td><td class="text-end">S/ {{ number_format($factura->total, 2) }}</td></tr>
            </table>
        </div>
    </div>

    <p class="text-center text-muted small mt-4">¡Gracias por su preferencia!</p>
    <div class="text-center no-print">
        <button class="btn btn-primary btn-sm" onclick="window.print()">Imprimir</button>
    </div>
</div>
</body>
</html>
