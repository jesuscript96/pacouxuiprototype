<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Asigna el rol super_admin al usuario admin@paco.com (creado por Inicial).
     * Idempotente: ejecutar varias veces no duplica ni rompe nada.
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@paco.com')->first();

        if (! $user) {
            $this->command->warn('Usuario admin@paco.com no encontrado. Ejecuta Inicial antes.');

            return;
        }

        if ($user->hasRole('super_admin')) {
            $this->command->info('Usuario admin@paco.com ya tiene rol super_admin.');

            return;
        }

        $user->syncRoles(['super_admin']);
        $this->command->info('Rol super_admin asignado a admin@paco.com.');
    }
}
