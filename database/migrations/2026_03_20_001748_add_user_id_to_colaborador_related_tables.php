<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FASE 1: user_id paralelo a colaborador_id (sin eliminar colaborador_id).
     *
     * @var list<string>
     */
    private const TABLAS = [
        'beneficiarios_colaborador',
        'cuentas_nomina',
        'colaborador_producto',
        'historial_departamentos',
        'historial_areas',
        'historial_puestos',
        'historial_ubicaciones',
        'historial_regiones',
        'historial_razones_sociales',
        'historial_periodicidades_pago',
    ];

    public function up(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('colaborador_id')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABLAS) as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
