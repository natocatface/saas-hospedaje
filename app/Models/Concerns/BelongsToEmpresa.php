<?php

namespace App\Models\Concerns;

use App\Models\Empresa;

trait BelongsToEmpresa
{
    public static function bootBelongsToEmpresa(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (empty($model->empresa_id) && auth()->check() && auth()->user()->empresa_id) {
                $model->empresa_id = auth()->user()->empresa_id;
            }
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
