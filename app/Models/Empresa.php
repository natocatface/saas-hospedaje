<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Empresa extends Model
{
    protected $table = 'empresas';

    protected $fillable = [
        'nombre', 'slug', 'ruc', 'email', 'telefono', 'direccion',
        'plan_id', 'activo', 'trial_ends_at', 'suscripcion_hasta',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'trial_ends_at' => 'datetime',
        'suscripcion_hasta' => 'date',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function suscripcionPagos()
    {
        return $this->hasMany(SuscripcionPago::class);
    }

    public function numHabitaciones(): int
    {
        return Habitacion::withoutGlobalScopes()->where('empresa_id', $this->id)->count();
    }

    public function numUsuarios(): int
    {
        return User::where('empresa_id', $this->id)->count();
    }

    public function puedeAgregarHabitacion(): bool
    {
        $max = $this->plan?->max_habitaciones;
        return empty($max) || $this->numHabitaciones() < $max;
    }

    public function puedeAgregarUsuario(): bool
    {
        $max = $this->plan?->max_usuarios;
        return empty($max) || $this->numUsuarios() < $max;
    }

    // ---------- Suscripción ----------

    public function enTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->endOfDay()->isFuture();
    }

    public function suscripcionVigente(): bool
    {
        return $this->suscripcion_hasta && $this->suscripcion_hasta->endOfDay()->gte(Carbon::now());
    }

    public function vigente(): bool
    {
        if (! $this->activo) {
            return false;
        }
        if ($this->suscripcionVigente() || $this->enTrial()) {
            return true;
        }
        // Sin fechas definidas = sin restricción
        return is_null($this->trial_ends_at) && is_null($this->suscripcion_hasta);
    }

    public function diasRestantes(): int
    {
        $fecha = $this->suscripcionVigente() ? $this->suscripcion_hasta
            : ($this->enTrial() ? $this->trial_ends_at : null);

        return $fecha ? max(0, (int) Carbon::now()->startOfDay()->diffInDays($fecha->copy()->endOfDay(), false)) : 0;
    }

    public function estadoSuscripcion(): string
    {
        if (! $this->activo) {
            return 'suspendida';
        }
        if ($this->suscripcionVigente()) {
            return 'activa';
        }
        if ($this->enTrial()) {
            return 'prueba';
        }
        return 'vencida';
    }

    public function badgeSuscripcion(): string
    {
        return match ($this->estadoSuscripcion()) {
            'activa'     => 'success',
            'prueba'     => 'info',
            'vencida'    => 'warning',
            'suspendida' => 'danger',
            default      => 'secondary',
        };
    }
}
