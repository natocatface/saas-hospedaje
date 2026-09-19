<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use BelongsToEmpresa;

    protected $table = 'configuracions';

    protected $fillable = ['empresa_id', 'clave', 'valor'];

    public static function get(string $clave, $default = null)
    {
        return static::where('clave', $clave)->value('valor') ?? $default;
    }
}
