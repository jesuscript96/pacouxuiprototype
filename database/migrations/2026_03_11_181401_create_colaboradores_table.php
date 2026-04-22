<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Módulo Alta de Colaboradores. Create directo, sin defensas.
     */
    public function up(): void
    {
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');
            $table->string('email')->nullable()->index();
            $table->string('telefono_movil')->nullable()->index();
            $table->string('numero_empleado')->nullable();
            $table->date('fecha_nacimiento');
            $table->string('genero')->nullable();
            $table->string('curp', 18)->nullable()->index();
            $table->string('rfc')->nullable();
            $table->string('nss', 11)->nullable();
            $table->date('fecha_ingreso');
            $table->date('fecha_registro_imss')->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('nacionalidad')->nullable();
            $table->text('direccion')->nullable();
            $table->decimal('salario_bruto', 12, 2)->nullable();
            $table->decimal('salario_neto', 12, 2)->nullable();
            $table->decimal('salario_diario', 12, 2)->nullable();
            $table->decimal('salario_diario_integrado', 12, 2)->nullable();
            $table->decimal('salario_variable', 12, 2)->nullable();
            $table->decimal('monto_maximo', 12, 2)->nullable();
            $table->string('periodicidad_pago');
            $table->integer('dia_periodicidad')->nullable();
            $table->integer('dias_vacaciones_anuales')->default(0);
            $table->integer('dias_vacaciones_restantes')->default(0);
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->time('hora_entrada_comida')->nullable();
            $table->time('hora_salida_comida')->nullable();
            $table->time('hora_entrada_extra')->nullable();
            $table->time('hora_salida_extra')->nullable();
            $table->text('comentario_adicional')->nullable();
            $table->string('codigo_jefe')->nullable();
            $table->boolean('verificado')->default(false);
            $table->boolean('verificacion_carga_masiva')->default(false);
            $table->boolean('tiene_identificacion')->default(false);
            $table->timestamp('fecha_verificacion_movil')->nullable();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('puesto_id')->nullable()->constrained('puestos')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regiones')->nullOnDelete();
            $table->foreignId('centro_pago_id')->nullable()->constrained('centros_pagos')->nullOnDelete();
            $table->foreignId('razon_social_id')->nullable()->constrained('razones_sociales')->nullOnDelete();
            $table->string('nombre_empresa_pago')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['empresa_id', 'ubicacion_id', 'departamento_id', 'area_id', 'puesto_id'], 'colab_emp_ubic_dept_area_puesto');
            $table->index(['empresa_id', 'fecha_ingreso'], 'colab_empresa_fecha_ingreso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colaboradores');
    }
};
