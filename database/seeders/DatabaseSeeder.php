<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        set_time_limit(0);

        // ── 1. Catálogos base ──
        $this->call([
            Inicial::class,
            BancoSeeder::class,
            EstadoAnimoAfeccionSeeder::class,
            EstadoAnimoCaracteristicaSeeder::class,
            ReconocimientosSeeder::class,
            TemaVozColaboradoresSeeder::class,
        ]);

        // ── 2. Empresa de ejemplo (id=1) ──
        $this->call(EmpresaEjemploSeeder::class);

        // ── 3. Permisos (comandos, no seeders) ──
        Artisan::call('shield:generate', [
            '--panel' => 'admin',
            '--option' => 'permissions',
            '--all' => true,
        ]);
        Artisan::call('shield:generate-cliente');

        // ── 4. Roles y asignación de permisos ──
        $this->call(SpatieRolesSeeder::class);
        $this->call(ShieldPermisosRolesSeeder::class);
        $this->call(RolesClienteSeeder::class);
        $this->call(Empresa2Seeder::class);

        // ── 5. Usuarios de prueba ──
        $this->call(SuperAdminSeeder::class);
        $this->call(ClienteEjemploSeeder::class);

        $this->call(TemaVozColaboradoresSeeder::class);
        $this->call(OcupacionesSeeder::class);
        $this->call(EmpresaCatalogosDemostrativosSeeder::class);
        $this->call(JefesSeeder::class);

    }
}
