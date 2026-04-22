<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CorrerProyectoCommand extends Command
{
    protected $signature = 'correr
                            {--build : Solo compilar assets (npm run build) y salir}';

    protected $description = 'Inicia el proyecto en desarrollo (servidor Laravel, cola, pail, Vite)';

    public function handle(): int
    {
        if ($this->option('build')) {
            $this->info('Compilando assets...');
            $process = new Process(
                ['npm', 'run', 'build'],
                base_path(),
                null,
                null,
                null
            );
            $process->run(fn ($type, $buffer) => $this->getOutput()->write($buffer));

            return $process->getExitCode();
        }

        $this->info('Iniciando proyecto (servidor, cola, pail, Vite). Ctrl+C para detener.');
        $this->newLine();

        $process = new Process(
            ['composer', 'run', 'dev'],
            base_path(),
            null,
            null,
            null
        );
        $process->run(fn ($type, $buffer) => $this->getOutput()->write($buffer));

        return $process->getExitCode();
    }
}
