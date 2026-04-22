<?php

namespace Database\Seeders;

use App\Models\EstadoAnimoCaracteristica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EstadoAnimoCaracteristicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Se salta si la tabla no existe (módulo estado de ánimo en pospuestos).
     */
    public function run(): void
    {
        if (! Schema::hasTable('estado_animo_caracteristicas')) {
            return;
        }
        $caracteristicas = [
            ['id' => 1, 'nombre' => 'Enojo', 'lista_inicial' => 'bad'],
            ['id' => 2, 'nombre' => 'Ansiedad', 'lista_inicial' => 'bad'],
            ['id' => 3, 'nombre' => 'Miedo', 'lista_inicial' => 'bad'],
            ['id' => 4, 'nombre' => 'Agobio', 'lista_inicial' => 'bad'],
            ['id' => 5, 'nombre' => 'Desagrado', 'lista_inicial' => 'bad'],
            ['id' => 6, 'nombre' => 'Pena', 'lista_inicial' => 'bad'],
            ['id' => 7, 'nombre' => 'Frustración', 'lista_inicial' => 'bad'],
            ['id' => 8, 'nombre' => 'Molestia', 'lista_inicial' => 'bad'],
            ['id' => 9, 'nombre' => 'Celos', 'lista_inicial' => 'bad'],
            ['id' => 10, 'nombre' => 'Culpa', 'lista_inicial' => 'bad'],
            ['id' => 11, 'nombre' => 'Estrés', 'lista_inicial' => 'bad'],
            ['id' => 12, 'nombre' => 'Sorpresa', 'lista_inicial' => 'very_bad'],
            ['id' => 13, 'nombre' => 'Desesperanza', 'lista_inicial' => 'very_bad'],
            ['id' => 14, 'nombre' => 'Irritabilidad', 'lista_inicial' => 'very_bad'],
            ['id' => 15, 'nombre' => 'Soledad', 'lista_inicial' => 'very_bad'],
            ['id' => 16, 'nombre' => 'Desaliento', 'lista_inicial' => 'very_bad'],
            ['id' => 17, 'nombre' => 'Decepción', 'lista_inicial' => 'very_bad'],
            ['id' => 18, 'nombre' => 'Complacencia', 'lista_inicial' => 'normal'],
            ['id' => 19, 'nombre' => 'Calma', 'lista_inicial' => 'normal'],
            ['id' => 20, 'nombre' => 'Paz', 'lista_inicial' => 'normal'],
            ['id' => 21, 'nombre' => 'Indiferencia', 'lista_inicial' => 'normal'],
            ['id' => 22, 'nombre' => 'Agotamiento', 'lista_inicial' => 'normal'],
            ['id' => 23, 'nombre' => 'Verguenza', 'lista_inicial' => 'very_bad'],
            ['id' => 24, 'nombre' => 'Preocupación', 'lista_inicial' => 'very_bad'],
            ['id' => 25, 'nombre' => 'Tristeza', 'lista_inicial' => 'very_bad'],
            ['id' => 26, 'nombre' => 'Asombro', 'lista_inicial' => 'well'],
            ['id' => 27, 'nombre' => 'Entusiasmo', 'lista_inicial' => 'well'],
            ['id' => 28, 'nombre' => 'Pasión', 'lista_inicial' => 'well'],
            ['id' => 29, 'nombre' => 'Felicidad', 'lista_inicial' => 'well'],
            ['id' => 30, 'nombre' => 'Alegría', 'lista_inicial' => 'well'],
            ['id' => 31, 'nombre' => 'Valentía', 'lista_inicial' => 'well'],
            ['id' => 32, 'nombre' => 'Orgullo', 'lista_inicial' => 'well'],
            ['id' => 33, 'nombre' => 'Confianza', 'lista_inicial' => 'very_well'],
            ['id' => 34, 'nombre' => 'Esperanza', 'lista_inicial' => 'very_well'],
            ['id' => 35, 'nombre' => 'Diversión', 'lista_inicial' => 'very_well'],
            ['id' => 36, 'nombre' => 'Satisfacción', 'lista_inicial' => 'very_well'],
            ['id' => 37, 'nombre' => 'Alivio', 'lista_inicial' => 'very_well'],
            ['id' => 38, 'nombre' => 'Gratitud', 'lista_inicial' => 'very_well'],
        ];

        $now = now();
        $rows = collect($caracteristicas)->map(fn (array $row) => array_merge($row, [
            'created_at' => $now,
            'updated_at' => $now,
        ]))->all();

        $existingIds = DB::table('estado_animo_caracteristicas')->whereIn('id', array_column($rows, 'id'))->pluck('id')->all();
        $toInsert = array_values(array_filter($rows, fn (array $row) => ! in_array($row['id'], $existingIds, true)));
        if ($toInsert !== []) {
            EstadoAnimoCaracteristica::insert($toInsert);
        }
    }
}
