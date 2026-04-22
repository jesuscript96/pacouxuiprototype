<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReconocimientosSeeder extends Seeder
{
    /**
     * Seed the reconocimientos table from the acknowledgments SQL data.
     * Idempotente: si la tabla ya tiene datos, no hace nada (evita duplicados y permite que
     * sigan ejecutándose EmpresaEjemploSeeder y el resto del db:seed).
     */
    public function run(): void
    {
        if (DB::table('reconocimientos')->exists()) {
            return;
        }

        $path = __DIR__.'/acknowledgments_202603012159.sql';

        if (! is_file($path)) {
            $this->command?->error('Archivo database/seeders/acknowledgments_202603012159.sql no encontrado. Copia el SQL ahí y vuelve a ejecutar el seeder.');

            return;
        }

        $sql = (string) file_get_contents($path);
        $insertPrefix = 'INSERT INTO reconocimientos (id,nombre,descripcion,es_enviable,es_exclusivo,menciones_necesarias,created_at,updated_at,deleted_at) VALUES';
        $parts = explode($insertPrefix, $sql);

        foreach (array_slice($parts, 1) as $valuesBlock) {
            $statement = $insertPrefix.trim(rtrim($valuesBlock, ";\r\n"));
            if ($statement !== $insertPrefix) {
                DB::unprepared($statement);
            }
        }
    }
}
