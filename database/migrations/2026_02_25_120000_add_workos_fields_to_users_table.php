<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade campos para integración WorkOS y hace password nullable (usuarios solo SSO).
     * Idempotente: si las columnas ya existen, no hace nada (seguro para merge con rama de Rafa).
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'workos_id')) {
                $table->string('workos_id')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
        });

        if (Schema::hasColumn('users', 'password')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'workos_id')) {
                $table->dropUnique(['workos_id']);
                $table->dropColumn('workos_id');
            }
            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
        });

        if (Schema::hasColumn('users', 'password')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL');
            }
        }
    }
};
