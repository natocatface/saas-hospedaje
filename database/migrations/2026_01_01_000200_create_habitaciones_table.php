<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->string('numero');
            $table->foreignId('tipo_habitacion_id')->constrained('tipo_habitaciones')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedTinyInteger('piso')->default(1);
            $table->decimal('precio_noche', 10, 2)->default(0);
            $table->unsignedTinyInteger('capacidad')->default(1);
            $table->enum('estado', ['disponible', 'ocupada', 'mantenimiento', 'limpieza'])->default('disponible');
            $table->text('descripcion')->nullable();
            $table->unique(['empresa_id', 'numero']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};
