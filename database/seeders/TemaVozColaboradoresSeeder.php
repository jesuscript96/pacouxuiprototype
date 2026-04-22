<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemaVozColaboradoresSeeder extends Seeder
{
    /**
     * Seed the temas_voz_colaboradores table from the voice_employee_subjects SQL data.
     * Idempotente: si la tabla ya tiene datos, no hace nada.
     */
    public function run(): void
    {
        if (DB::table('temas_voz_colaboradores')->exists()) {
            return;
        }

        $path = __DIR__.'/voice_employee_subjects_202603012229.sql';

        if (! is_file($path)) {
            $this->command?->error('Archivo database/seeders/voice_employee_subjects_202603012229.sql no encontrado. Copia el SQL ahí y vuelve a ejecutar el seeder.');

            return;
        }

        $sql = (string) file_get_contents($path);
        $insertPrefix = 'INSERT INTO temas_voz_colaboradores (id,nombre,descripcion,exclusivo_para_empresa,created_at,updated_at,deleted_at) VALUES';
        $parts = explode($insertPrefix, $sql);

        foreach (array_slice($parts, 1) as $valuesBlock) {
            $statement = $insertPrefix.trim(rtrim($valuesBlock, ";\r\n"));
            if ($statement !== $insertPrefix) {
                DB::unprepared($statement);
            }
        }
    }
}
