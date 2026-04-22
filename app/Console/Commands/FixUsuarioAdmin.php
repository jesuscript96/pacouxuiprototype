<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Console\Command;

class FixUsuarioAdmin extends Command
{
    protected $signature = 'usuario:fix-admin
                            {email? : Email del usuario admin a reparar (ej. adrian.garcia@789.com)}';

    protected $description = 'Asigna la primera empresa al usuario admin si no tiene empresas (repara datos tras migración usuarios→users).';

    public function handle(): int
    {
        $email = $this->argument('email') ?? 'adrian.garcia@789.com';
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Usuario no encontrado: {$email}");

            return self::FAILURE;
        }

        if (! $user->tieneRol('cliente')) {
            $this->warn("El usuario {$email} no es tipo 'cliente'. No se aplican cambios.");

            return self::SUCCESS;
        }

        $empresa = Empresa::first();
        if (! $empresa) {
            $this->error('No hay ninguna empresa en la base de datos. Crea una antes de ejecutar el comando.');

            return self::FAILURE;
        }

        $count = $user->empresas()->count();
        if ($count > 0) {
            $this->info("El usuario ya tiene {$count} empresa(s) asignada(s). No se aplican cambios.");

            return self::SUCCESS;
        }

        $user->empresas()->sync([$empresa->id]);
        $user->update(['empresa_id' => $empresa->id]);
        $this->info("Usuario reparado: {$email}. Empresa asignada: {$empresa->nombre} (id: {$empresa->id}).");

        return self::SUCCESS;
    }
}
