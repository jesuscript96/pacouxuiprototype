<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Idempotente: si las columnas ya existen no hace nada (seguro para merge).
     */
    public function up(): void
    {
        if (! Schema::hasTable('spatie_roles')) {
            return;
        }

        Schema::table('spatie_roles', function (Blueprint $table) {
            if (! Schema::hasColumn('spatie_roles', 'display_name')) {
                $table->string('display_name')->nullable()->after(
                    Schema::hasColumn('spatie_roles', 'company_id') ? 'company_id' : 'guard_name'
                );
            }
            if (! Schema::hasColumn('spatie_roles', 'description')) {
                $table->string('description')->nullable()->after('display_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('spatie_roles')) {
            return;
        }

        Schema::table('spatie_roles', function (Blueprint $table) {
            if (Schema::hasColumn('spatie_roles', 'display_name')) {
                $table->dropColumn('display_name');
            }
            if (Schema::hasColumn('spatie_roles', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
