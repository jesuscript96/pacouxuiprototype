<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Homologa valores del campo tipo en users:
     * user → administrador, admin → cliente, employee → colaborador.
     * Cambia el default de la columna a 'administrador'.
     */
    public function up(): void
    {
        DB::table('users')->where('tipo', 'user')->update(['tipo' => 'administrador']);
        DB::table('users')->where('tipo', 'admin')->update(['tipo' => 'cliente']);
        DB::table('users')->where('tipo', 'employee')->update(['tipo' => 'colaborador']);

        Schema::table('users', function (Blueprint $table): void {
            $table->string('tipo', 50)->default('administrador')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('tipo', 50)->default('user')->change();
        });

        DB::table('users')->where('tipo', 'administrador')->update(['tipo' => 'user']);
        DB::table('users')->where('tipo', 'cliente')->update(['tipo' => 'admin']);
        DB::table('users')->where('tipo', 'colaborador')->update(['tipo' => 'employee']);
    }
};
