<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intentos_cobro', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('codigo_razon')->nullable();
            $table->string('referencia_numerica_emisor')->nullable();
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_liquidacion')->nullable();
            $table->foreignId('cuenta_bancaria_id')->nullable()->constrained('cuentas_bancarias')->nullOnDelete();
            $table->foreignId('cuenta_por_cobrar_id')->constrained('cuentas_por_cobrar')->cascadeOnDelete();
            $table->decimal('monto', 14, 2)->nullable();
            $table->foreignId('comprobante_txt_procesado_id')->nullable()->constrained('comprobantes_txt_procesados')->nullOnDelete();
            $table->boolean('es_recargo')->default(false);
            $table->string('estado_recargo')->nullable();
            $table->decimal('monto_cobrado', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intentos_cobro');
    }
};
