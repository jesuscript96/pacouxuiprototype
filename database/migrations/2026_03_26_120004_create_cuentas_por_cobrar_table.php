<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_por_cobrar', function (Blueprint $table): void {
            $table->id();
            $table->string('estado');
            $table->decimal('debe', 14, 2)->default(0);
            $table->foreignId('estado_cuenta_id')->constrained('estados_cuenta')->cascadeOnDelete();
            $table->foreignId('cuenta_bancaria_id')->nullable()->constrained('cuentas_bancarias')->nullOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('fecha_pago')->nullable();
            $table->dateTime('fecha_confirmacion_pago')->nullable();
            $table->text('comentarios')->nullable();
            $table->string('tipo_confirmacion_pago')->nullable();
            $table->decimal('comisiones_bancarias', 14, 2)->nullable();
            $table->string('periodicidad_pago')->nullable();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('puesto_id')->nullable()->constrained('puestos')->nullOnDelete();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('cuentas_por_cobrar')->nullOnDelete();
            $table->string('centro_costo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_cobrar');
    }
};
