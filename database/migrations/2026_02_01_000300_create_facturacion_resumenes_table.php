<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Resúmenes diarios de boletas (RC) enviados a SUNAT.
 * Las boletas se declaran de forma agrupada y asíncrona: el envío devuelve un
 * ticket y luego se consulta el estado para obtener el CDR.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturacion_resumenes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->index();
            $table->string('identificador')->nullable();      // RC-YYYYMMDD-N
            $table->unsignedInteger('correlativo')->default(1);
            $table->date('fecha_referencia');                 // fecha de emisión de las boletas
            $table->string('ticket')->nullable();
            $table->string('estado', 20)->default('generado'); // generado|enviado|procesando|aceptado|rechazado
            $table->unsignedInteger('total_boletas')->default(0);
            $table->text('mensaje')->nullable();
            $table->string('cdr_path')->nullable();
            $table->timestamp('enviado_at')->nullable();
            $table->timestamp('aceptado_at')->nullable();
            $table->timestamps();
        });

        Schema::table('facturas', function (Blueprint $table) {
            $table->unsignedBigInteger('resumen_id')->nullable()->after('sunat_enviado_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn('resumen_id');
        });
        Schema::dropIfExists('facturacion_resumenes');
    }
};
