<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Parte 2/4: datos laborales y horarios en users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_registro_imss')->nullable();
            $table->decimal('salario_bruto', 12, 2)->nullable();
            $table->decimal('salario_neto', 12, 2)->nullable();
            $table->decimal('salario_diario', 12, 2)->nullable();
            $table->decimal('salario_diario_integrado', 12, 2)->nullable();
            $table->decimal('monto_maximo', 12, 2)->nullable();
            $table->string('periodicidad_pago')->nullable();
            $table->integer('dia_periodicidad')->nullable();
            $table->integer('dias_vacaciones_legales')->default(0);
            $table->integer('dias_vacaciones_empresa')->default(0);
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->time('hora_entrada_comida')->nullable();
            $table->time('hora_salida_comida')->nullable();
            $table->time('hora_inicio_horas_extra')->nullable();
            $table->time('hora_fin_horas_extra')->nullable();
            $table->text('comentario_adicional')->nullable();
            $table->string('codigo_jefe')->nullable();
            $table->string('nombre_empresa_pago')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_ingreso',
                'fecha_registro_imss',
                'salario_bruto',
                'salario_neto',
                'salario_diario',
                'salario_diario_integrado',
                'monto_maximo',
                'periodicidad_pago',
                'dia_periodicidad',
                'dias_vacaciones_legales',
                'dias_vacaciones_empresa',
                'hora_entrada',
                'hora_salida',
                'hora_entrada_comida',
                'hora_salida_comida',
                'hora_inicio_horas_extra',
                'hora_fin_horas_extra',
                'comentario_adicional',
                'codigo_jefe',
                'nombre_empresa_pago',
            ]);
        });
    }
};
