<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TableauAdminSeeder extends Seeder
{
    /**
     * Configura el usuario administrador para acceso a reportes Tableau.
     *
     * Uso:
     *   php artisan db:seed --class=TableauAdminSeeder
     */
    public function run(): void
    {
        $email = 'admin@paco.com';

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->command->warn("Usuario {$email} no encontrado. Crea el usuario primero.");

            return;
        }

        $user->update([
            'ver_reportes' => true,
            'usuario_tableau' => 'admin@paco.app',
        ]);

        $this->command->info("Usuario {$email} configurado para Tableau:");
        $this->command->line('  - ver_reportes: true');
        $this->command->line('  - usuario_tableau: admin@paco.app');
    }
}
