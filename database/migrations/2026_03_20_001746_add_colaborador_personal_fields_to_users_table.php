<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Parte 1/4: datos personales del colaborador en users (ALTER pequeño).
     * El resto: migraciones add_colaborador_laboral / fk / flags (2026_03_21_*).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('numero_colaborador')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero')->nullable();
            $table->string('curp', 18)->nullable();
            $table->string('rfc')->nullable();
            $table->string('nss', 11)->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('nacionalidad')->nullable();
            $table->text('direccion')->nullable();
            $table->string('telefono_movil')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'numero_colaborador',
                'fecha_nacimiento',
                'genero',
                'curp',
                'rfc',
                'nss',
                'estado_civil',
                'nacionalidad',
                'direccion',
                'telefono_movil',
            ]);
        });
    }
};
