<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_push', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->string('titulo', 255);
            $table->text('mensaje');
            $table->string('url')->nullable()->comment('URL de redirección al abrir');

            $table->json('data')->nullable()->comment('Payload adicional para la app');
            $table->json('filtros')->nullable()->comment('Filtros: ubicaciones, areas, departamentos, puestos, generos, edad, antiguedad, etc.');

            $table->enum('estado', [
                'borrador',
                'programada',
                'enviando',
                'enviada',
                'fallida',
                'cancelada',
            ])->default('borrador');

            $table->timestamp('programada_para')->nullable()->comment('Fecha/hora de envío programado');
            $table->timestamp('enviada_at')->nullable()->comment('Fecha/hora real de envío');

            $table->unsignedInteger('total_destinatarios')->default(0);
            $table->unsignedInteger('total_enviados')->default(0);
            $table->unsignedInteger('total_fallidos')->default(0);

            $table->foreignId('creado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'programada_para']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_push');
    }
};
