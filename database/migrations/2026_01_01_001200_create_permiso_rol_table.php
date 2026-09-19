<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->string('rol');
            $table->string('permiso');
            $table->timestamps();
            $table->unique(['empresa_id', 'rol', 'permiso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_rol');
    }
};
