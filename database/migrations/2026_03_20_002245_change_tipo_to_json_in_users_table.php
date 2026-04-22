<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convierte users.tipo de string a JSON (array de un elemento por valor previo).
     */
    public function up(): void
    {
        DB::statement("UPDATE users SET tipo = CONCAT('[\"', tipo, '\"]') WHERE tipo IS NOT NULL AND tipo != ''");

        Schema::table('users', function (Blueprint $table) {
            $table->json('tipo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE users SET tipo = JSON_UNQUOTE(JSON_EXTRACT(tipo, '$[0]')) WHERE tipo IS NOT NULL AND JSON_VALID(tipo)");

        Schema::table('users', function (Blueprint $table) {
            $table->string('tipo', 50)->nullable()->change();
        });
    }
};
