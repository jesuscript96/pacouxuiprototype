<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nombre_contacto');
            $table->string('email_contacto');
            $table->string('telefono_contacto');
            $table->string('movil_contacto');
            $table->foreignId('industria_id')->nullable()->constrained('industrias'); // ------------> Industrias
            $table->foreignId('sub_industria_id')->nullable()->constrained('sub_industrias'); // ------------> Sub Industrias
            $table->string('email_facturacion');
            $table->date('fecha_inicio_contrato');
            $table->date('fecha_fin_contrato');
            $table->integer('num_usuarios_reportes');
            $table->boolean('activo');
            $table->datetime('fecha_activacion')->nullable();
            $table->string('nombre_app')->nullable();
            $table->string('link_descarga_app')->nullable();
            $table->string('app_android_id')->nullable();
            $table->string('app_ios_id')->nullable();
            $table->string('app_huawei_id')->nullable();
            $table->string('color_primario')->nullable();
            $table->string('color_secundario')->nullable();
            $table->string('color_terciario')->nullable();
            $table->string('color_cuarto')->nullable();
            $table->string('logo_url')->nullable(); // ------------> 'assets/companies/logos/'.$file_name.'.png';
            $table->enum('tipo_comision', ['PERCENTAGE', 'FIXED_AMOUNT', 'MIXED'])->nullable();
            $table->decimal('comision_bisemanal', 10, 2);
            $table->decimal('comision_mensual', 10, 2);
            $table->decimal('comision_quincenal', 10, 2);
            $table->decimal('comision_semanal', 10, 2);
            $table->boolean('tiene_pagos_catorcenales');
            $table->date('fecha_proximo_pago_catorcenal')->nullable();
            $table->boolean('tiene_sub_empresas');
            $table->decimal('comision_gateway', 10, 2);
            $table->boolean('transacciones_con_imss');
            $table->boolean('validar_cuentas_automaticamente');
            $table->boolean('tiene_analiticas_por_ubicacion');
            $table->string('version_android')->nullable();
            $table->string('version_ios')->nullable();
            $table->boolean('tiene_limite_de_sesiones');
            $table->boolean('tiene_firma_nubarium');
            $table->boolean('enviar_boletin');
            $table->boolean('permitir_encuesta_salida');
            $table->unsignedBigInteger('configuracion_app_id')->nullable(); // FK opcional: configuracion_app (crear después de que exista esa tabla)
            $table->boolean('activar_finiquito');
            $table->string('url_finiquito')->nullable();
            $table->boolean('domiciliación_via_api');
            $table->boolean('ha_firmado_nuevo_contrato');
            $table->integer('vigencia_mensajes_urgentes')->nullable();
            $table->boolean('permitir_notificaciones_felicitaciones')->nullable();
            $table->enum('segmento_notificaciones_felicitaciones', ['COMPANY', 'LOCATION'])->nullable();
            $table->boolean('permitir_retenciones');
            $table->integer('dias_vencidos_retencion');
            $table->boolean('pertenece_pepeferia')->nullable();
            $table->string('tipo_registro')->nullable();
            $table->boolean('descargar_cursos')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
