<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracion_app', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_app');
            $table->string('android_app_id');
            $table->string('ios_app_id');
            $table->string('one_signal_app_id');
            $table->string('one_signal_rest_api_key');
            $table->string('link_descarga')->nullable();
            $table->string('android_channel_id')->nullable();
            $table->string('version_ios')->nullable();
            $table->string('version_android')->nullable();
            $table->boolean('requiere_validacion')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_app');
    }
};
