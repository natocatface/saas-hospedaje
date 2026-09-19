<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->string('codigo')->unique();
            $table->foreignId('huesped_id')->constrained('huespedes')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_checkin');
            $table->date('fecha_checkout');
            $table->unsignedSmallInteger('noches')->default(1);
            $table->unsignedTinyInteger('adultos')->default(1);
            $table->unsignedTinyInteger('ninos')->default(0);
            $table->decimal('tarifa_noche', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('estado', ['pendiente', 'confirmada', 'checkin', 'checkout', 'cancelada'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
