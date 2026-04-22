<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacion_push_destinatarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notificacion_push_id')
                ->constrained('notificaciones_push')
                ->cascadeOnDelete();

            $table->foreignId('colaborador_id')
                ->constrained('colaboradores')
                ->cascadeOnDelete();

            $table->enum('estado_lectura', ['NO_LEIDA', 'LEIDA'])
                ->default('NO_LEIDA');

            $table->timestamp('leida_at')->nullable();

            $table->boolean('enviado')->default(false);
            $table->string('onesignal_player_id')->nullable();
            $table->timestamp('enviado_at')->nullable();

            $table->timestamps();

            $table->unique(['notificacion_push_id', 'colaborador_id'], 'npd_notificacion_colaborador_unique');
            $table->index('estado_lectura');
            $table->index('enviado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_push_destinatarios');
    }
};
