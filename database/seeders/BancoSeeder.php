<?php

namespace Database\Seeders;

use App\Models\Banco;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BancoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Idempotente: solo inserta bancos cuyo ID no existe (seguro para entorno de Rafa).
     */
    public function run(): void
    {
        $bancos = [
            ['id' => 1, 'nombre' => 'BBVA Bancomer', 'codigo' => 12, 'comision' => 4.00],
            ['id' => 2, 'nombre' => 'Banamex', 'codigo' => 2, 'comision' => 6.00],
            ['id' => 4, 'nombre' => 'Santander', 'codigo' => 14, 'comision' => 6.00],
            ['id' => 5, 'nombre' => 'HSBC', 'codigo' => 21, 'comision' => 6.00],
            ['id' => 6, 'nombre' => 'BanBajio', 'codigo' => 30, 'comision' => 6.00],
            ['id' => 7, 'nombre' => 'Inbursa', 'codigo' => 36, 'comision' => 6.00],
            ['id' => 8, 'nombre' => 'Mifel', 'codigo' => 42, 'comision' => 6.00],
            ['id' => 9, 'nombre' => 'Scotiabank', 'codigo' => 44, 'comision' => 6.00],
            ['id' => 10, 'nombre' => 'Banregio', 'codigo' => 58, 'comision' => 6.00],
            ['id' => 11, 'nombre' => 'Invex', 'codigo' => 59, 'comision' => 6.00],
            ['id' => 12, 'nombre' => 'Bansi', 'codigo' => 60, 'comision' => 6.00],
            ['id' => 13, 'nombre' => 'Afirme', 'codigo' => 62, 'comision' => 6.00],
            ['id' => 14, 'nombre' => 'Banorte', 'codigo' => 72, 'comision' => 6.00],
            ['id' => 15, 'nombre' => 'Ve por mas', 'codigo' => 113, 'comision' => 6.00],
            ['id' => 16, 'nombre' => 'Banco Azteca', 'codigo' => 127, 'comision' => 6.00],
            ['id' => 18, 'nombre' => 'Multiva', 'codigo' => 132, 'comision' => 6.00],
            ['id' => 19, 'nombre' => 'BanCoppel', 'codigo' => 137, 'comision' => 6.00],
            ['id' => 20, 'nombre' => 'Consubanco', 'codigo' => 140, 'comision' => 6.00],
            ['id' => 21, 'nombre' => 'CI Banco', 'codigo' => 143, 'comision' => 6.00],
            ['id' => 22, 'nombre' => 'Bank of America Mexico', 'codigo' => 106, 'comision' => 6.00],
            ['id' => 23, 'nombre' => 'Dummies Bank', 'codigo' => 0, 'comision' => 6.00],
        ];

        $now = now();
        $rows = collect($bancos)->map(fn (array $banco) => array_merge($banco, [
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ]))->all();

        $existingIds = DB::table('bancos')->whereIn('id', array_column($rows, 'id'))->pluck('id')->all();
        $toInsert = array_values(array_filter($rows, fn (array $row) => ! in_array($row['id'], $existingIds, true)));
        if ($toInsert !== []) {
            Banco::insert($toInsert);
        }
    }
}
