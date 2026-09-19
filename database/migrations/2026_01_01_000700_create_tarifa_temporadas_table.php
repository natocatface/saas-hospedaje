<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifa_temporadas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->string('nombre');                              // "Temporada Alta - Fiestas Patrias"
            $table->enum('tipo', ['alta', 'baja', 'especial'])->default('alta');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('ajuste_tipo', ['porcentaje', 'fijo'])->default('porcentaje');
            $table->decimal('ajuste_valor', 10, 2)->default(0);    // +20 (%) o +50 (S/)
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifa_temporadas');
    }
};
