<?php

declare(strict_types=1);

use App\Enums\EstadoVerificacionCuenta;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('cuentas_bancarias', 'envio_verificacion')
            && ! Schema::hasColumn('cuentas_bancarias', 'enviado_verificacion')) {
            Schema::table('cuentas_bancarias', function (Blueprint $table): void {
                $table->renameColumn('envio_verificacion', 'enviado_verificacion');
            });
        }

        if (Schema::hasColumn('cuentas_bancarias', 'enviado_verificacion')) {
            DB::statement("
                UPDATE cuentas_bancarias
                SET enviado_verificacion = CASE
                    WHEN enviado_verificacion IS NULL THEN '0'
                    WHEN LOWER(TRIM(enviado_verificacion)) IN ('1', 'true', 'yes', 'si', 'enviado') THEN '1'
                    ELSE '0'
                END
            ");
        }

        Schema::table('cuentas_bancarias', function (Blueprint $table) {
            $table->string('estado')
                ->default(EstadoVerificacionCuenta::SIN_VERIFICAR->value)
                ->change();
            $table->boolean('enviado_verificacion')
                ->default(false)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Mantener cambios: normalización de estado/verificación.
    }
};
