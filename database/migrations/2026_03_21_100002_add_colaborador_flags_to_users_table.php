<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Parte 4/4: flags de verificación y soft deletes en users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('verificado')->default(false);
            $table->boolean('verificacion_carga_masiva')->default(false);
            $table->boolean('tiene_identificacion')->default(false);
            $table->timestamp('fecha_verificacion_movil')->nullable();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();

            $table->dropColumn([
                'verificado',
                'verificacion_carga_masiva',
                'tiene_identificacion',
                'fecha_verificacion_movil',
            ]);
        });
    }
};
