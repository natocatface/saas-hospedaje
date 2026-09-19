<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte {{ $desde->format('d/m/Y') }} - {{ $hasta->format('d/m/Y') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{font-family:'Segoe UI',sans-serif;color:#1e293b;font-size:13px}
        .wrap{max-width:900px;margin:18px auto;padding:0 18px}
        .hd{border-bottom:3px solid #155e9c;padding-bottom:10px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-end}
        .hd h2{margin:0;color:#0a1b30}
        .kpi-row{display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap}
        .kpi-box{flex:1;min-width:130px;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px}
        .kpi-box .v{font-size:1.3rem;font-weight:700;color:#155e9c}
        .kpi-box .l{font-size:.72rem;color:#64748b}
        h5{margin:18px 0 8px;color:#0a1b30}
        table{width:100%;font-size:12px}
        @media print{.no-print{display:none}}
    </style>
</head>
<body onload="window.print()">
<div class="wrap">
    <div class="hd">
        <div>
            <h2>{{ $hotel }}</h2>
            <div class="text-muted">Reporte de gestión</div>
        </div>
        <div class="text-end">
            <div><strong>Período:</strong> {{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</div>
            <div class="text-muted small">Generado: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="kpi-row">
        <div class="kpi-box"><div class="v">S/ {{ number_format($ingresoTotal, 2) }}</div><div class="l">Ingresos cobrados</div></div>
        <div class="kpi-box"><div class="v">{{ $ocupacionPct }}%</div><div class="l">Ocupación</div></div>
        <div class="kpi-box"><div class="v">S/ {{ number_format($adr, 2) }}</div><div class="l">ADR (tarifa media)</div></div>
        <div class="kpi-box"><div class="v">S/ {{ number_format($revpar, 2) }}</div><div class="l">RevPAR</div></div>
        <div class="kpi-box"><div class="v">{{ $nochesVendidas }}</div><div class="l">Noches vendidas</div></div>
    </div>

    <div class="kpi-row">
        <div class="kpi-box"><div class="v">{{ $nFacturas }}</div><div class="l">Facturas cobradas</div></div>
        <div class="kpi-box"><div class="v">S/ {{ number_format($ticketProm, 2) }}</div><div class="l">Ticket promedio</div></div>
        <div class="kpi-box"><div class="v">S/ {{ number_format($ingresoHabitaciones, 2) }}</div><div class="l">Ingreso hospedaje</div></div>
        <div class="kpi-box"><div class="v">S/ {{ number_format($igvTotal, 2) }}</div><div class="l">IGV recaudado</div></div>
    </div>

    <h5>Ocupación e ingresos por tipo de habitación</h5>
    <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>Tipo</th><th class="text-center">Reservas</th><th class="text-center">Noches</th><th class="text-end">Ingreso</th><th class="text-end">ADR</th></tr></thead>
        <tbody>
            @forelse ($porTipo as $t)
                <tr><td>{{ $t->nombre }}</td><td class="text-center">{{ $t->reservas }}</td><td class="text-center">{{ $t->noches }}</td>
                    <td class="text-end">S/ {{ number_format($t->ingreso, 2) }}</td>
                    <td class="text-end">S/ {{ number_format($t->noches > 0 ? $t->ingreso / $t->noches : 0, 2) }}</td></tr>
            @empty
                <tr><td colspan="5" class="text-center">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h5>Habitaciones más rentables</h5>
    <table class="table table-sm table-bordered">
        <thead class="table-light"><tr><th>#</th><th>Habitación</th><th class="text-center">Reservas</th><th class="text-end">Ingreso</th></tr></thead>
        <tbody>
            @forelse ($topHabitaciones as $i => $h)
                <tr><td>{{ $i + 1 }}</td><td>N° {{ $h->numero }}</td><td class="text-center">{{ $h->reservas }}</td><td class="text-end">S/ {{ number_format($h->ingreso, 2) }}</td></tr>
            @empty
                <tr><td colspan="4" class="text-center">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="text-center no-print mt-3">
        <button class="btn btn-primary btn-sm" onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>
</div>
</body>
</html>
