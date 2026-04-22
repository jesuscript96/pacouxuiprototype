<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_solicitud', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->boolean('estado')->default(true);
            $table->enum('rango_fechas', ['PASADAS', 'FUTURAS', 'AMBAS']);
            $table->boolean('vigencia_solicitud')->default(false);
            $table->string('unidad_tiempo')->default('DIAS');
            $table->date('fecha_vigencia')->nullable();
            $table->longText('descripcion');
            $table->foreignId('categoria_solicitud_id')->nullable()->constrained('categorias_solicitudes')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_solicitud');
    }
};
