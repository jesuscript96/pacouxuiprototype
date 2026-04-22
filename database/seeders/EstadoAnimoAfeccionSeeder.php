<?php

namespace Database\Seeders;

use App\Models\EstadoAnimoAfeccion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EstadoAnimoAfeccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Se salta si la tabla no existe (módulo estado de ánimo en pospuestos).
     */
    public function run(): void
    {
        if (! Schema::hasTable('estado_animo_afecciones')) {
            return;
        }
        $afecciones = [
            ['id' => 1, 'nombre' => 'Salud'],
            ['id' => 2, 'nombre' => 'Condición física'],
            ['id' => 3, 'nombre' => 'Cuidado personal'],
            ['id' => 4, 'nombre' => 'Pasatiempos'],
            ['id' => 5, 'nombre' => 'Identidad'],
            ['id' => 6, 'nombre' => 'Espiritualidad'],
            ['id' => 7, 'nombre' => 'Frustración'],
            ['id' => 8, 'nombre' => 'Molestia'],
            ['id' => 9, 'nombre' => 'Celos'],
            ['id' => 10, 'nombre' => 'Culpa'],
            ['id' => 11, 'nombre' => 'Estrés'],
            ['id' => 12, 'nombre' => 'Sorpresa'],
            ['id' => 13, 'nombre' => 'Desesperanza'],
            ['id' => 14, 'nombre' => 'Irritabilidad'],
            ['id' => 15, 'nombre' => 'Soledad'],
            ['id' => 16, 'nombre' => 'Desaliento'],
            ['id' => 17, 'nombre' => 'Decepción'],
            ['id' => 18, 'nombre' => 'Comunidad'],
            ['id' => 19, 'nombre' => 'Familia'],
            ['id' => 20, 'nombre' => 'Amistades'],
            ['id' => 21, 'nombre' => 'Pareja'],
            ['id' => 22, 'nombre' => 'Vida sentimental'],
            ['id' => 23, 'nombre' => 'Quehaceres'],
            ['id' => 24, 'nombre' => 'Trabajo'],
            ['id' => 25, 'nombre' => 'Educación'],
            ['id' => 26, 'nombre' => 'Viajes'],
            ['id' => 27, 'nombre' => 'Clima'],
            ['id' => 28, 'nombre' => 'Sucesos actuales'],
            ['id' => 29, 'nombre' => 'Dinero'],
        ];

        $now = now();
        $rows = collect($afecciones)->map(fn (array $row) => array_merge($row, [
            'created_at' => $now,
            'updated_at' => $now,
        ]))->all();

        $existingIds = DB::table('estado_animo_afecciones')->whereIn('id', array_column($rows, 'id'))->pluck('id')->all();
        $toInsert = array_values(array_filter($rows, fn (array $row) => ! in_array($row['id'], $existingIds, true)));
        if ($toInsert !== []) {
            EstadoAnimoAfeccion::insert($toInsert);
        }
    }
}
