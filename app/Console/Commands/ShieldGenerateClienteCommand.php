<?php

declare(strict_types=1);

namespace App\Console\Commands;

use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ShieldGenerateClienteCommand extends Command
{
    protected const GUARD = 'web';

    protected $signature = 'shield:generate-cliente
        {--no-cache-clear : No limpiar la caché de permisos al finalizar}';

    protected $description = 'Genera solo permisos para los recursos del panel Cliente (formato Action:Modelo). Idempotente. No crea roles ni toca el panel Admin.';

    public function handle(): int
    {
        $panel = Filament::getPanel('cliente');
        if (! $panel) {
            $this->error('No existe el panel "cliente".');

            return self::FAILURE;
        }

        $config = Utils::getConfig();
        $metodosPorDefecto = $config->policies->methods ?? ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete', 'forceDeleteAny', 'restoreAny', 'replicate', 'reorder'];
        $separator = $config->permissions->separator ?? ':';
        /** @var array<class-string, array<int, string>> $gestionPorRecurso */
        $gestionPorRecurso = config('filament-shield.resources.manage', []);

        $resources = collect($panel->getResources())
            ->filter(fn (string $resource): bool => $this->isClienteResource($resource))
            ->values();

        $pages = collect($panel->getPages())
            ->filter(fn (string $page): bool => method_exists($page, 'getPermissionModel'));

        if ($resources->isEmpty() && $pages->isEmpty()) {
            $this->warn('No hay recursos ni páginas con permisos en el panel Cliente. Nada que generar.');

            return self::SUCCESS;
        }

        $created = 0;
        $total = 0;
        $processedSubjects = [];

        foreach ($resources as $resource) {
            if (! method_exists($resource, 'getModel')) {
                continue;
            }
            $model = class_basename($resource::getModel());
            $subject = Str::of($model)->studly()->toString();

            if (in_array($subject, $processedSubjects, true)) {
                continue;
            }
            $processedSubjects[] = $subject;

            $methods = $gestionPorRecurso[$resource] ?? $metodosPorDefecto;

            foreach ($methods as $method) {
                $affix = Str::of($method)->studly()->toString();
                $name = $affix.$separator.$subject;
                $total++;
                $p = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => self::GUARD],
                    ['name' => $name, 'guard_name' => self::GUARD]
                );
                if ($p->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        foreach ($pages as $page) {
            $model = $page::getPermissionModel();
            if (! $model) {
                continue;
            }
            $subject = Str::of(class_basename($model))->studly()->toString();

            if (in_array($subject, $processedSubjects, true)) {
                continue;
            }
            $processedSubjects[] = $subject;

            foreach ($metodosPorDefecto as $method) {
                $affix = Str::of($method)->studly()->toString();
                $name = $affix.$separator.$subject;
                $total++;
                $p = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => self::GUARD],
                    ['name' => $name, 'guard_name' => self::GUARD]
                );
                if ($p->wasRecentlyCreated) {
                    $created++;
                }
            }
        }

        if (! $this->option('no-cache-clear')) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $pagesCount = $pages->count();
        $this->info("Permisos panel Cliente: {$created} creados. Total considerados: {$total} (de {$resources->count()} recursos + {$pagesCount} páginas).");

        if ($this->output->isVerbose()) {
            $rows = $resources
                ->filter(fn (string $resource): bool => method_exists($resource, 'getModel'))
                ->map(fn (string $resource): array => [
                    class_basename($resource).' (Resource)',
                    implode(', ', $this->permissionNamesForResource($resource, $config, $separator)),
                ]);

            $pageRows = $pages->map(fn (string $page): array => [
                class_basename($page).' (Page)',
                implode(', ', $this->permissionNamesForSubject(
                    Str::of(class_basename($page::getPermissionModel()))->studly()->toString(),
                    $metodosPorDefecto,
                    $separator,
                )),
            ]);

            $this->table(['Origen', 'Permisos'], $rows->merge($pageRows)->toArray());
        }

        return self::SUCCESS;
    }

    private function isClienteResource(string $resource): bool
    {
        if ($resource === \App\Filament\Resources\Shield\RoleResource::class) {
            return false;
        }
        if ($resource === \BezhanSalleh\FilamentShield\Resources\Roles\RoleResource::class) {
            return false;
        }

        return true;
    }

    /**
     * @return array<int, string>
     */
    private function permissionNamesForResource(string $resource, \BezhanSalleh\FilamentShield\Support\ShieldConfig $config, string $separator): array
    {
        if (! method_exists($resource, 'getModel')) {
            return [];
        }
        /** @var array<class-string, array<int, string>> $gestionPorRecurso */
        $gestionPorRecurso = config('filament-shield.resources.manage', []);
        $methods = $gestionPorRecurso[$resource] ?? ($config->policies->methods ?? ['viewAny', 'view', 'create', 'update', 'delete']);

        return $this->permissionNamesForSubject(
            Str::of(class_basename($resource::getModel()))->studly()->toString(),
            $methods,
            $separator,
        );
    }

    /**
     * @param  array<int, string>  $methods
     * @return array<int, string>
     */
    private function permissionNamesForSubject(string $subject, array $methods, string $separator): array
    {
        $names = [];
        foreach ($methods as $method) {
            $affix = Str::of($method)->studly()->toString();
            $names[] = $affix.$separator.$subject;
        }

        return $names;
    }
}
