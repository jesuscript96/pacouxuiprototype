<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade a users las columnas de negocio que tenía la tabla usuarios.
     * Requiere: empresas, departamentos, puestos, empleados (ya creados).
     * Parte de la unificación usuarios → users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apellido_paterno')->nullable()->after('name');
            $table->string('apellido_materno')->nullable()->after('apellido_paterno');
            $table->string('telefono', 20)->nullable()->after('email');
            $table->string('celular', 20)->nullable()->after('telefono');
            $table->string('tipo', 50)->default('user')->after('celular');
            $table->foreignId('empresa_id')->nullable()->after('tipo')->constrained('empresas')->onDelete('set null');
            $table->foreignId('departamento_id')->nullable()->after('empresa_id')->constrained('departamentos')->onDelete('set null');
            $table->foreignId('puesto_id')->nullable()->after('departamento_id')->constrained('puestos')->onDelete('set null');
            $table->string('imagen')->nullable()->after('puesto_id');
            $table->boolean('ver_reportes')->default(false)->after('imagen');
            $table->string('usuario_tableau')->nullable()->after('ver_reportes');
            $table->boolean('recibir_boletin')->default(false)->after('usuario_tableau');
            $table->string('google2fa_secret')->nullable()->after('recibir_boletin');
            $table->boolean('enable_2fa')->default(false)->after('google2fa_secret');
            $table->timestamp('verified_2fa_at')->nullable()->after('enable_2fa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropForeign(['departamento_id']);
            $table->dropForeign(['puesto_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'apellido_paterno',
                'apellido_materno',
                'telefono',
                'celular',
                'tipo',
                'empresa_id',
                'departamento_id',
                'puesto_id',
                'imagen',
                'ver_reportes',
                'usuario_tableau',
                'recibir_boletin',
                'google2fa_secret',
                'enable_2fa',
                'verified_2fa_at',
            ]);
        });
    }
};
