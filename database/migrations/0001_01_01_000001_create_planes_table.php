<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->decimal('precio_mensual', 10, 2)->default(0);
            $table->unsignedInteger('max_habitaciones')->nullable(); // null = ilimitado
            $table->unsignedInteger('max_usuarios')->nullable();
            $table->boolean('permite_reportes')->default(true);
            $table->boolean('permite_temporadas')->default(true);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
