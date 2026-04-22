<?php

namespace App\Console\Commands;

use App\Models\SpatieRole;
use App\Models\User;
use Illuminate\Console\Command;

class ShieldVerificarRol extends Command
{
    protected $signature = 'shield:verificar-rol
                            {rol : Nombre del rol (ej. rh_empresa)}
                            {--email= : Email del usuario para verificar sus roles y permisos}';

    protected $description = 'Verifica un rol Shield: permisos asignados en BD y opcionalmente los permisos de un usuario por email';

    public function handle(): int
    {
        $rolName = $this->argument('rol');
        $email = $this->option('email');

        $roles = SpatieRole::withoutGlobalScopes()
            ->where('name', $rolName)
            ->orderBy('company_id')
            ->get();

        if ($roles->isEmpty()) {
            $this->error("No existe ningún rol con nombre \"{$rolName}\" en la base de datos.");

            return self::FAILURE;
        }

        $this->info("Rol(es) \"{$rolName}\" encontrado(s):");
        $this->newLine();

        foreach ($roles as $role) {
            $companyLabel = $role->company_id !== null
                ? "empresa_id {$role->company_id}"
                : 'global';
            $this->line("  ID: {$role->id} | company_id: ".($role->company_id ?? 'null')." ({$companyLabel})");
            $permissions = $role->permissions()->orderBy('name')->pluck('name');
            if ($permissions->isEmpty()) {
                $this->warn('    Permisos asignados: ninguno');
            } else {
                $this->line('    Permisos asignados ('.$permissions->count().'):');
                foreach ($permissions as $p) {
                    $this->line('      - '.$p);
                }
            }
            $this->newLine();
        }

        if ($email !== null && $email !== '') {
            $user = User::where('email', $email)->first();
            if (! $user) {
                $this->warn("Usuario con email \"{$email}\" no encontrado.");
            } else {
                $tipoJson = json_encode($user->tipo ?? [], JSON_UNESCAPED_UNICODE);
                $this->info("Usuario: {$user->email} (id={$user->id}, tipo={$tipoJson}, empresa_id=".($user->empresa_id ?? 'null').')');
                $userRoles = $user->roles;
                if ($userRoles->isEmpty()) {
                    $this->warn('  Roles asignados: ninguno');
                } else {
                    $this->line('  Roles asignados: '.$userRoles->pluck('name')->join(', '));
                    $allPerms = $user->getAllPermissions();
                    $permNames = $allPerms->pluck('name')->unique()->sort()->values();
                    $this->line('  Permisos efectivos (vía roles) ('.$permNames->count().'):');
                    foreach ($permNames as $p) {
                        $this->line('    - '.$p);
                    }
                }
            }
        }

        return self::SUCCESS;
    }
}
