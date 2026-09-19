<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos de comprobante electrónico SUNAT en las facturas.
 * Permiten registrar el tipo de comprobante, su serie/correlativo y el
 * resultado del envío a SUNAT (estado, ticket, hash CDR, mensajes).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->string('tipo_comprobante', 20)->nullable()->after('numero'); // boleta | factura | nota_credito
            $table->string('serie', 6)->nullable()->after('tipo_comprobante');
            $table->string('correlativo', 12)->nullable()->after('serie');
            $table->string('moneda', 5)->default('PEN')->after('correlativo');

            $table->string('sunat_estado', 20)->default('no_emitido')->after('metodo_pago');
            // no_emitido | pendiente | enviado | aceptado | observado | rechazado | anulado
            $table->string('sunat_ticket')->nullable()->after('sunat_estado');
            $table->string('sunat_hash')->nullable()->after('sunat_ticket');
            $table->text('sunat_mensaje')->nullable()->after('sunat_hash');
            $table->string('sunat_xml_path')->nullable()->after('sunat_mensaje');
            $table->string('sunat_cdr_path')->nullable()->after('sunat_xml_path');
            $table->timestamp('sunat_enviado_at')->nullable()->after('sunat_cdr_path');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_comprobante', 'serie', 'correlativo', 'moneda',
                'sunat_estado', 'sunat_ticket', 'sunat_hash', 'sunat_mensaje',
                'sunat_xml_path', 'sunat_cdr_path', 'sunat_enviado_at',
            ]);
        });
    }
};
