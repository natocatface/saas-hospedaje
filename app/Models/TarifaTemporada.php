<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TarifaTemporada extends Model
{
    use BelongsToEmpresa;

    protected $table = 'tarifa_temporadas';

    protected $fillable = [
        'empresa_id', 'nombre', 'tipo', 'fecha_inicio', 'fecha_fin',
        'ajuste_tipo', 'ajuste_valor', 'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'ajuste_valor' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public static function paraFecha($fecha): ?self
    {
        $fecha = Carbon::parse($fecha)->toDateString();

        return static::where('activo', true)
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->whereDate('fecha_fin', '>=', $fecha)
            ->orderByDesc('id')
            ->first();
    }

    public function aplicar(float $precioBase): float
    {
        if ($this->ajuste_tipo === 'porcentaje') {
            return round($precioBase * (1 + ((float) $this->ajuste_valor / 100)), 2);
        }
        return round($precioBase + (float) $this->ajuste_valor, 2);
    }

    public static function tarifaPara(Habitacion $habitacion, $fecha): array
    {
        $base = (float) $habitacion->precio_noche;
        $temporada = static::paraFecha($fecha);

        return [
            'base' => $base,
            'tarifa' => $temporada ? $temporada->aplicar($base) : $base,
            'temporada' => $temporada?->nombre,
            'tipo' => $temporada?->tipo,
        ];
    }

    public function badgeColor(): string
    {
        return match ($this->tipo) {
            'alta' => 'danger',
            'baja' => 'success',
            'especial' => 'primary',
            default => 'secondary',
        };
    }
}
