<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Comando de setup rápido para la rama prototipo.
 * Ejecuta migraciones y seeders mínimos para tener un usuario demo
 * y poder navegar el panel Cliente sin autenticación manual.
 */
class PrototipoSetupCommand extends Command
{
    protected $signature = 'prototipo:setup {--fresh : Borra todas las tablas y las recrea desde cero}';

    protected $description = 'Setup rápido del prototipo: migraciones + seed demo. Login: cliente@tecben.com / password → panel cliente.';

    public function handle(): int
    {
        $this->info('');
        $this->info('🚀  Prototipo Setup');
        $this->info('═══════════════════════════════════════');

        if ($this->option('fresh')) {
            if (! $this->confirm('¿Borrar TODA la BD y recrear desde cero?', false)) {
                $this->warn('Cancelado.');

                return self::FAILURE;
            }
            $this->call('migrate:fresh', ['--force' => true]);
        } else {
            $this->call('migrate', ['--force' => true]);
        }

        $this->info('');
        $this->info('📦  Seeders demo...');

        $seeders = [
            \Database\Seeders\Inicial::class,
            \Database\Seeders\SpatieRolesSeeder::class,
            \Database\Seeders\RolesClienteSeeder::class,
            \Database\Seeders\EmpresaEjemploSeeder::class,
            \Database\Seeders\ClienteEjemploSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        $this->call('config:clear');
        $this->call('cache:clear');

        $this->info('');
        $this->info('✅  Listo. Panel cliente: /cliente/login (o /admin/login → redirige al cliente).');
        $this->info('   Credenciales demo: cliente@tecben.com / password');
        $this->info('');

        return self::SUCCESS;
    }
}
