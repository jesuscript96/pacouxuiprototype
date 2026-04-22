<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * CORE: catálogo de notificaciones incluidas y relación empresa–notificación para el panel admin.
     */
    public function up(): void
    {
        Schema::create('notificaciones_incluidas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('empresas_notificaciones_incluidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('notificacion_incluida_id')->constrained('notificaciones_incluidas', 'id', 'emp_notif_inc_id_fk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas_notificaciones_incluidas');
        Schema::dropIfExists('notificaciones_incluidas');
    }
};
