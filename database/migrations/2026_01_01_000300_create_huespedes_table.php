<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('huespedes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->string('nombres');
            $table->string('apellidos');
            $table->enum('documento_tipo', ['DNI', 'CE', 'Pasaporte', 'RUC'])->default('DNI');
            $table->string('documento_numero')->index();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('nacionalidad')->default('Peru');
            $table->text('direccion')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('huespedes');
    }
};
