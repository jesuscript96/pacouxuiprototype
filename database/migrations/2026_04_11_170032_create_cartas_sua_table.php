<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cartas_sua', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->foreignId('colaborador_id')
                ->constrained('colaboradores')
                ->cascadeOnDelete();

            // BL: datos de la carta provenientes del archivo SUA/Excel
            $table->string('bimestre', 50);
            $table->string('razon_social');
            $table->decimal('retiro', 12, 2);
            $table->decimal('cesantia_vejez', 12, 2);
            $table->decimal('infonavit', 12, 2);
            $table->decimal('total', 12, 2);

            // BL: row completo del Excel para trazabilidad y posible regeneración de PDF
            $table->json('datos_origen')->nullable();

            $table->string('pdf_path', 500)->nullable();

            // BL: tracking de visualización desde app móvil (RN-06)
            $table->timestamp('primera_visualizacion')->nullable();
            $table->timestamp('ultima_visualizacion')->nullable();

            // BL: firma digital (individual o masiva, con Nubarium opcional)
            $table->boolean('firmado')->default(false);
            $table->timestamp('fecha_firma')->nullable();
            $table->longText('imagen_firma')->nullable();
            $table->longText('nom151')->nullable();
            $table->string('hash_nom151')->nullable();
            $table->string('codigo_validacion')->nullable();

            $table->timestamps();

            $table->index('bimestre');
            $table->index('firmado');
            $table->index(['empresa_id', 'bimestre']);

            // BL: RN-01 — una sola carta por combinación colaborador + bimestre + razón social
            $table->unique(
                ['colaborador_id', 'bimestre', 'razon_social'],
                'cartas_sua_unique_colaborador_bimestre_razon'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartas_sua');
    }
};
