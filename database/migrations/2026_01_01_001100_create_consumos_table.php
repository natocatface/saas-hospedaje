<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->unsignedBigInteger('producto_id')->nullable()->index();
            $table->string('descripcion');
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('precio_unit', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->date('fecha');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumos');
    }
};
