<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filtra automáticamente las consultas por la empresa (tenant) del usuario autenticado.
 * En consola (seeders/comandos) no se aplica, permitiendo operar sobre todas las empresas.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->empresa_id) {
            $builder->where($model->getTable().'.empresa_id', auth()->user()->empresa_id);
        }
    }
}
