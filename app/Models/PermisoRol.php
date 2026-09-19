<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Model;

class PermisoRol extends Model
{
    use BelongsToEmpresa;

    protected $table = 'permiso_rol';

    protected $fillable = ['empresa_id', 'rol', 'permiso'];
}
