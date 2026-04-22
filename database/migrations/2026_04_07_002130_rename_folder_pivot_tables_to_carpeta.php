<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * BL: Bases que ya ejecutaron las migraciones con tablas folder_* deben renombrarse a carpeta_*.
 * Instalaciones nuevas crean directamente carpeta_* y esta migración no hace nada.
 */
return new class extends Migration
{
    /** @var array<string, string> */
    private const RENAMES = [
        'folder_ubicacion' => 'carpeta_ubicacion',
        'folder_departamento' => 'carpeta_departamento',
        'folder_area' => 'carpeta_area',
        'folder_puesto' => 'carpeta_puesto',
        'folder_empresa' => 'carpeta_empresa',
    ];

    public function up(): void
    {
        foreach (self::RENAMES as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
    }

    public function down(): void
    {
        foreach (self::RENAMES as $from => $to) {
            if (Schema::hasTable($to) && ! Schema::hasTable($from)) {
                Schema::rename($to, $from);
            }
        }
    }
};
