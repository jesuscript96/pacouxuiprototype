<?php

declare(strict_types=1);

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
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropForeign(['industria_id']);
            $table->dropForeign(['sub_industria_id']);
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->unsignedBigInteger('industria_id')->nullable()->change();
            $table->unsignedBigInteger('sub_industria_id')->nullable()->change();
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->foreign('industria_id')->references('id')->on('industrias');
            $table->foreign('sub_industria_id')->references('id')->on('sub_industrias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // BL: El estado NOT NULL previo era incorrecto. No se revierte.
    }
};
