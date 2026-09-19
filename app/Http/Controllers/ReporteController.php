<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    private function rango(Request $request): array
    {
        $desde = $request->filled('desde') ? Carbon::parse($request->desde)->startOfDay() : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->hasta)->endOfDay() : Carbon::now()->endOfMonth();
        return [$desde, $hasta];
    }

    /** Calcula todas las métricas del período. */
    private function metricas(Carbon $desde, Carbon $hasta): array
    {
        $facturasPagadas = Factura::where('estado', 'pagada')->whereBetween('fecha', [$desde, $hasta]);

        $ingresoTotal = (float) (clone $facturasPagadas)->sum('total');
        $igvTotal     = (float) (clone $facturasPagadas)->sum('igv');
        $nFacturas    = (clone $facturasPagadas)->count();
        $ticketProm   = $nFacturas > 0 ? $ingresoTotal / $nFacturas : 0;

        $reservasRango = Reserva::where('estado', '!=', 'cancelada')->whereBetween('fecha_checkin', [$desde, $hasta]);
        $ingresoHabitaciones = (float) (clone $reservasRango)->sum('total');
        $nochesVendidas = (int) (clone $reservasRango)->sum('noches');

        $dias = max(1, $desde->diffInDays($hasta) + 1);
        $totalHab = Habitacion::count();
        $capacidadNoches = max(1, $totalHab * $dias);

        $ocupacionPct = round($nochesVendidas / $capacidadNoches * 100, 1);
        $adr    = $nochesVendidas > 0 ? round($ingresoHabitaciones / $nochesVendidas, 2) : 0;   // tarifa media diaria
        $revpar = round($ingresoHabitaciones / $capacidadNoches, 2);                              // ingreso por hab. disponible

        $reservasPorEstado = Reserva::whereBetween('fecha_checkin', [$desde, $hasta])
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')->pluck('total', 'estado')->toArray();

        // Ingresos por día (gráfico)
        $porDia = (clone $facturasPagadas)
            ->select(DB::raw('DATE(fecha) as dia'), DB::raw('SUM(total) as total'))
            ->groupBy('dia')->orderBy('dia')->pluck('total', 'dia')->toArray();
        $labelsDia = array_map(fn ($d) => Carbon::parse($d)->format('d/m'), array_keys($porDia));
        $dataDia = array_map(fn ($v) => (float) $v, array_values($porDia));

        // Por tipo de habitación
        $porTipo = Reserva::where('reservas.estado', '!=', 'cancelada')
            ->whereBetween('reservas.fecha_checkin', [$desde, $hasta])
            ->join('habitaciones', 'habitaciones.id', '=', 'reservas.habitacion_id')
            ->join('tipo_habitaciones', 'tipo_habitaciones.id', '=', 'habitaciones.tipo_habitacion_id')
            ->select('tipo_habitaciones.nombre',
                DB::raw('COUNT(*) as reservas'),
                DB::raw('SUM(reservas.noches) as noches'),
                DB::raw('SUM(reservas.total) as ingreso'))
            ->groupBy('tipo_habitaciones.nombre')
            ->orderByDesc('ingreso')->get();

        // Top habitaciones por ingreso (facturas pagadas)
        $topHabitaciones = Factura::where('facturas.estado', 'pagada')
            ->whereBetween('facturas.fecha', [$desde, $hasta])
            ->join('reservas', 'reservas.id', '=', 'facturas.reserva_id')
            ->join('habitaciones', 'habitaciones.id', '=', 'reservas.habitacion_id')
            ->select('habitaciones.numero', DB::raw('SUM(facturas.total) as ingreso'), DB::raw('COUNT(*) as reservas'))
            ->groupBy('habitaciones.numero')
            ->orderByDesc('ingreso')->limit(8)->get();

        return compact(
            'ingresoTotal', 'igvTotal', 'nFacturas', 'ticketProm',
            'ingresoHabitaciones', 'nochesVendidas', 'ocupacionPct', 'adr', 'revpar',
            'reservasPorEstado', 'labelsDia', 'dataDia', 'porTipo', 'topHabitaciones'
        );
    }

    public function index(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $m = $this->metricas($desde, $hasta);

        return view('reportes.index', array_merge($m, compact('desde', 'hasta')));
    }

    public function pdf(Request $request)
    {
        [$desde, $hasta] = $this->rango($request);
        $m = $this->metricas($desde, $hasta);
        $hotel = Configuracion::get('nombre_hotel', 'Hotel');

        return view('reportes.pdf', array_merge($m, compact('desde', 'hasta', 'hotel')));
    }

    public function export(Request $request): StreamedResponse
    {
        [$desde, $hasta] = $this->rango($request);

        $facturas = Factura::with('reserva.huesped', 'reserva.habitacion')
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')->get();

        $nombre = 'reporte_facturas_'.$desde->format('Ymd').'_'.$hasta->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($facturas) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Numero', 'Fecha', 'Huesped', 'Habitacion', 'Subtotal', 'IGV', 'Total', 'Estado', 'Metodo']);
            foreach ($facturas as $f) {
                fputcsv($out, [
                    $f->numero,
                    $f->fecha->format('d/m/Y'),
                    $f->reserva->huesped->nombre_completo ?? '',
                    $f->reserva->habitacion->numero ?? '',
                    number_format($f->subtotal, 2),
                    number_format($f->igv, 2),
                    number_format($f->total, 2),
                    $f->estado,
                    $f->metodo_pago,
                ]);
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv']);
    }
}
