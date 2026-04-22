<?php

namespace App\Console\Commands;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Console\Command;

class CorregirTipoSuperAdmin extends Command
{
    protected $signature = 'usuarios:corregir-tipo-super-admin
                            {--fix : Actualizar tipo a "administrador" en usuarios con rol super_admin}
                            {--list : Solo listar usuarios con su tipo, rol y empresas}';

    protected $description = 'Lista usuarios y opcionalmente corrige tipo a "administrador" para quienes tienen rol super_admin (acceso panel Admin)';

    public function handle(): int
    {
        $fix = $this->option('fix');
        $list = $this->option('list') || ! $fix;

        $superAdminName = Utils::getSuperAdminName();

        if ($list) {
            $this->info('Usuarios en tabla users:');
            $this->newLine();
            User::query()->orderBy('id')->get()->each(function (User $u): void {
                $roles = $u->roles->pluck('name')->join(', ');
                $empresasCount = $u->empresas()->count();
                $empresaId = $u->empresa_id;
                $tipoJson = json_encode($u->tipo ?? [], JSON_UNESCAPED_UNICODE);
                $this->line(sprintf(
                    '  id=%s email=%s tipo=%s roles=[%s] empresa_id=%s empresas(pivot)=%s',
                    $u->id,
                    $u->email,
                    $tipoJson,
                    $roles,
                    $empresaId ?? 'null',
                    $empresasCount
                ));
            });
            $this->newLine();
        }

        if ($fix) {
            $ids = User::whereHas('roles', fn ($q) => $q->where('name', $superAdminName))
                ->get()
                ->filter(fn (User $u) => ! $u->tieneRol('administrador'))
                ->pluck('id');

            $count = 0;
            foreach ($ids as $id) {
                $user = User::find($id);
                if ($user) {
                    $user->update(['tipo' => ['administrador']]);
                    $count++;
                }
            }
            if ($count > 0) {
                $this->info("Se actualizó tipo a ['administrador'] en {$count} usuario(s) con rol super_admin.");
            } else {
                $this->info('Ningún usuario con rol super_admin tenía tipo distinto de incluir "administrador".');
            }
        }

        return self::SUCCESS;
    }
}
