<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use BelongsToEmpresa;

    protected $table = 'pagos';

    protected $fillable = [
        'empresa_id', 'factura_id', 'reserva_id', 'caja_sesion_id',
        'user_id', 'monto', 'metodo_pago', 'fecha', 'nota',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cajaSesion()
    {
        return $this->belongsTo(CajaSesion::class);
    }
}
