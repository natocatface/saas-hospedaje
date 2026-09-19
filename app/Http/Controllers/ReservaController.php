<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Habitacion;
use App\Models\Huesped;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\TarifaTemporada;
use App\Services\Facturacion\FacturacionElectronica;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaController extends Controller
{
    /** Tarifa sugerida (con temporada aplicada) para una habitación y fecha. */
    public function tarifaSugerida(Request $request)
    {
        $request->validate([
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha' => 'required|date',
        ]);

        $habitacion = Habitacion::findOrFail($request->habitacion_id);
        return response()->json(TarifaTemporada::tarifaPara($habitacion, $request->fecha));
    }

    /** Verifica disponibilidad de una habitación para un rango de fechas. */
    public function disponibilidad(Request $request)
    {
        $request->validate([
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha_checkin' => 'required|date',
            'fecha_checkout' => 'required|date|after:fecha_checkin',
        ]);

        $disponible = $this->habitacionDisponible(
            $request->habitacion_id,
            $request->fecha_checkin,
            $request->fecha_checkout,
            $request->integer('reserva_id') ?: null
        );

        return response()->json(['disponible' => $disponible]);
    }

    /** ¿La habitación está libre en el rango (sin solape con otras reservas activas)? */
    private function habitacionDisponible($habitacionId, $checkin, $checkout, $ignorarId = null): bool
    {
        $query = Reserva::where('habitacion_id', $habitacionId)
            ->where('estado', '!=', 'cancelada')
            ->whereDate('fecha_checkin', '<', $checkout)
            ->whereDate('fecha_checkout', '>', $checkin);

        if ($ignorarId) {
            $query->where('id', '!=', $ignorarId);
        }

        return ! $query->exists();
    }

    private function asegurarDisponibilidad(array $data, $ignorarId = null): void
    {
        if (! $this->habitacionDisponible($data['habitacion_id'], $data['fecha_checkin'], $data['fecha_checkout'], $ignorarId)) {
            throw ValidationException::withMessages([
                'habitacion_id' => 'La habitación ya tiene una reserva que se cruza con esas fechas. Elige otra habitación o ajusta las fechas.',
            ]);
        }
    }

    public function index(Request $request)
    {
        $query = Reserva::with(['huesped', 'habitacion']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('codigo', 'like', "%$q%")
                ->orWhereHas('huesped', fn ($w) => $w->where('nombres', 'like', "%$q%")->orWhere('apellidos', 'like', "%$q%"));
        }

        $reservas = $query->orderByDesc('id')->paginate(12)->withQueryString();
        $estados = ['pendiente', 'confirmada', 'checkin', 'checkout', 'cancelada'];

        return view('reservas.index', compact('reservas', 'estados'));
    }

    public function create()
    {
        return view('reservas.form', [
            'reserva' => new Reserva(['fecha_checkin' => today()->toDateString(), 'fecha_checkout' => today()->addDay()->toDateString()]),
            'huespedes' => Huesped::orderBy('apellidos')->get(),
            'habitaciones' => Habitacion::with('tipo')->orderBy('numero')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $this->asegurarDisponibilidad($data);
        $data = $this->calcular($data);
        $data['codigo'] = $this->nuevoCodigo();
        $data['user_id'] = $request->user()->id;

        $factura = DB::transaction(function () use ($data) {
            $reserva = Reserva::create($data);
            return $this->generarFactura($reserva);
        });

        // Emisión automática ante SUNAT (fuera de la transacción para no
        // bloquear la reserva si el web service tarda o falla).
        $this->emitirSiCorresponde($factura);

        return redirect()->route('reservas.index')->with('ok', 'Reserva registrada correctamente.');
    }

    public function show(Reserva $reserva)
    {
        $reserva->load(['huesped', 'habitacion.tipo', 'usuario', 'factura', 'consumos.producto']);
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();
        return view('reservas.show', compact('reserva', 'productos'));
    }

    public function edit(Reserva $reserva)
    {
        return view('reservas.form', [
            'reserva' => $reserva,
            'huespedes' => Huesped::orderBy('apellidos')->get(),
            'habitaciones' => Habitacion::with('tipo')->orderBy('numero')->get(),
        ]);
    }

    public function update(Request $request, Reserva $reserva)
    {
        $data = $this->validar($request);
        $this->asegurarDisponibilidad($data, $reserva->id);
        $data = $this->calcular($data);
        $reserva->update($data);

        return redirect()->route('reservas.index')->with('ok', 'Reserva actualizada.');
    }

    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        return back()->with('ok', 'Reserva eliminada.');
    }

    public function cambiarEstado(Request $request, Reserva $reserva)
    {
        $request->validate(['estado' => 'required|in:pendiente,confirmada,checkin,checkout,cancelada']);
        $reserva->update(['estado' => $request->estado]);

        if ($request->estado === 'checkin') {
            $reserva->habitacion->update(['estado' => 'ocupada']);
        } elseif (in_array($request->estado, ['checkout', 'cancelada'])) {
            $reserva->habitacion->update(['estado' => 'limpieza']);
        }

        return back()->with('ok', "Reserva {$reserva->codigo} marcada como {$request->estado}.");
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'huesped_id' => 'required|exists:huespedes,id',
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha_checkin' => 'required|date',
            'fecha_checkout' => 'required|date|after:fecha_checkin',
            'adultos' => 'required|integer|min:1|max:10',
            'ninos' => 'required|integer|min:0|max:10',
            'tarifa_noche' => 'required|numeric|min:0',
            'estado' => 'required|in:pendiente,confirmada,checkin,checkout,cancelada',
            'notas' => 'nullable|string',
        ]);
    }

    private function calcular(array $data): array
    {
        $in = Carbon::parse($data['fecha_checkin']);
        $out = Carbon::parse($data['fecha_checkout']);
        $noches = max(1, $in->diffInDays($out));
        $data['noches'] = $noches;
        $data['total'] = round($noches * (float) $data['tarifa_noche'], 2);
        return $data;
    }

    private function nuevoCodigo(): string
    {
        $ultimo = Reserva::max('id') + 1;
        return 'RSV-'.str_pad((string) $ultimo, 5, '0', STR_PAD_LEFT);
    }

    private function generarFactura(Reserva $reserva): Factura
    {
        $total = (float) $reserva->total;
        $subtotal = round($total / 1.18, 2);
        $igv = round($total - $subtotal, 2);
        $num = Factura::max('id') + 1;

        return Factura::create([
            'numero' => 'F001-'.str_pad((string) $num, 6, '0', STR_PAD_LEFT),
            'reserva_id' => $reserva->id,
            'fecha' => $reserva->fecha_checkout,
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $total,
            'estado' => 'pendiente',
        ]);
    }

    /** Emite el comprobante automáticamente si así está configurado. */
    private function emitirSiCorresponde(?Factura $factura): void
    {
        if (! $factura) {
            return;
        }

        $fe = FacturacionElectronica::actual();
        if ($fe->config->habilitado && $fe->config->emitir_automatico && $fe->activa()) {
            try {
                $fe->emitir($factura);
            } catch (\Throwable $e) {
                report($e); // la factura queda pendiente; no rompe el registro de la reserva
            }
        }
    }
}
