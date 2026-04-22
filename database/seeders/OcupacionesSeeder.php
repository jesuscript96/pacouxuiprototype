<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OcupacionesSeeder extends Seeder
{
    /**
     * Ruta al archivo SQL de catálogo de ocupaciones (origen: occupation_catalogs).
     * Tabla destino: ocupaciones. Columna: descripcion (traducción de "description").
     */
    private const DATA_FILE = 'occupation_catalogs_202603111313.sql';

    /**
     * Run the database seeds.
     * Lee el archivo SQL del catálogo de ocupaciones, parsea los registros
     * e inserta en la tabla ocupaciones con la columna descripcion.
     */
    public function run(): void
    {
        $path = base_path('database/seeders/data/'.self::DATA_FILE);

        if (! is_readable($path)) {
            throw new \RuntimeException(
                'Archivo de catálogo no encontrado. Copia el SQL a: database/seeders/data/'.self::DATA_FILE
            );
        }

        $sql = file_get_contents($path);

        // (id, 'description', NULL, NULL) o (id, 'description', NULL, NULL);
        $pattern = "/\(\s*(\d+),\s*'((?:[^']|'')*)',\s*NULL,\s*NULL\)/";

        if (! preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER)) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($matches as $m) {
            $id = (int) $m[1];
            $descripcion = str_replace("''", "'", $m[2]);
            $rows[] = [
                'id' => $id,
                'descripcion' => $descripcion,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $chunkSize = 500;
        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            DB::table('ocupaciones')->upsert(
                $chunk,
                ['id'],
                ['descripcion', 'updated_at']
            );
        }
    }
}
