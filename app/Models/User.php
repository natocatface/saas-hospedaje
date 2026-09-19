<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'empresa_id', 'name', 'email', 'telefono', 'rol', 'activo', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected ?array $permisosCache = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function plan()
    {
        return $this->empresa?->plan;
    }

    /** Permisos concedidos al rol del usuario (admin = todos). */
    public function permisos(): array
    {
        if ($this->esAdmin()) {
            return ['*'];
        }
        if ($this->permisosCache === null) {
            $this->permisosCache = PermisoRol::where('empresa_id', $this->empresa_id)
                ->where('rol', $this->rol)
                ->pluck('permiso')->all();
        }
        return $this->permisosCache;
    }

    /** ¿El usuario tiene un permiso? El admin siempre puede. */
    public function puede(string $permiso): bool
    {
        return $this->esAdmin() || in_array($permiso, $this->permisos(), true);
    }
}
