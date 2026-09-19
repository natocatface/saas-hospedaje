<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->string('clave');
            $table->text('valor')->nullable();
            $table->unique(['empresa_id', 'clave']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracions');
    }
};
