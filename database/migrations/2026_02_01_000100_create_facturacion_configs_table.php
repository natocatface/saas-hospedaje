<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuración de Facturación Electrónica (SUNAT · Perú).
 * Una fila por empresa (tenant). Guarda estado, modo, datos del emisor,
 * credenciales SOL, ruta del certificado digital y series de comprobantes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturacion_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable()->unique();

            // Estado y modo
            $table->boolean('habilitado')->default(false);
            $table->boolean('emitir_automatico')->default(false);
            $table->string('driver', 30)->default('ninguno');   // ninguno | sunat
            $table->string('entorno', 20)->default('beta');      // beta | produccion

            // Datos del emisor
            $table->string('ruc', 15)->nullable();
            $table->string('razon_social')->nullable();
            $table->string('nombre_comercial')->nullable();
            $table->string('direccion_fiscal')->nullable();
            $table->string('ubigeo', 10)->nullable();
            $table->string('departamento', 60)->nullable();
            $table->string('provincia', 60)->nullable();
            $table->string('distrito', 60)->nullable();

            // Credenciales SUNAT
            $table->string('usuario_sol', 60)->nullable();
            $table->text('clave_sol')->nullable();               // encriptada por el modelo
            $table->string('certificado_ruta')->nullable();      // ruta .pem
            $table->text('certificado_clave')->nullable();       // encriptada por el modelo

            // Series y correlativos
            $table->string('serie_boleta', 6)->default('B001');
            $table->string('serie_factura', 6)->default('F001');
            $table->string('serie_nota_credito', 6)->default('FC01');
            $table->string('moneda', 5)->default('PEN');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturacion_configs');
    }
};
