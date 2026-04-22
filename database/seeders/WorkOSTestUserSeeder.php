<?php

namespace Database\Seeders;

use App\Models\SpatieRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkOSTestUserSeeder extends Seeder
{
    /**
     * Crea usuario de prueba en tabla users (para panel admin + Shield).
     * Con rol super_admin para ver "Shield / Roles" en el menú.
     * Iniciar sesión con WorkOS usando este email (o crear el User antes del callback).
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@workos.com'],
            [
                'name' => 'Test',
                'apellido_paterno' => 'WorkOS',
                'apellido_materno' => null,
                'password' => Hash::make('password123'),
                'tipo' => ['administrador'],
            ]
        );

        $superAdmin = SpatieRole::withoutGlobalScopes()->updateOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            [
                'display_name' => 'Super Administrador',
                'description' => 'Acceso total al sistema',
                'company_id' => null,
            ]
        );

        if (! $user->hasRole('super_admin')) {
            $user->assignRole($superAdmin);
        }

        $user->update(['tipo' => ['administrador']]);
    }
}
