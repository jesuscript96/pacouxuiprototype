<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reconocimientos', function (Blueprint $table) {
            $table->string('imagen_inicial')->nullable()->after('menciones_necesarias');
            $table->string('imagen_final')->nullable()->after('imagen_inicial');
        });
    }

    public function down(): void
    {
        Schema::table('reconocimientos', function (Blueprint $table) {
            $table->dropColumn(['imagen_inicial', 'imagen_final']);
        });
    }
};
