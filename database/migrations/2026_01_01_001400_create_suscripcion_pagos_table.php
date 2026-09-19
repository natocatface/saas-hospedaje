<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suscripcion_pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('plan_nombre');
            $table->decimal('monto', 10, 2)->default(0);
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->date('fecha_pago');
            $table->string('metodo')->default('tarjeta');
            $table->string('estado')->default('pagado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripcion_pagos');
    }
};
