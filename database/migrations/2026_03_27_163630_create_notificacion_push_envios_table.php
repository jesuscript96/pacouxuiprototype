<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacion_push_envios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notificacion_push_id')
                ->constrained('notificaciones_push')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('chunk_numero')->default(1);
            $table->unsignedInteger('tokens_enviados')->default(0);

            $table->string('onesignal_notification_id')->nullable();
            $table->json('onesignal_response')->nullable();

            $table->enum('estado', ['pendiente', 'enviado', 'fallido'])->default('pendiente');
            $table->text('error_mensaje')->nullable();

            $table->timestamps();

            $table->index(['notificacion_push_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_push_envios');
    }
};
