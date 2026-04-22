<?php

namespace Database\Seeders;

use App\Helpers\SeederRoleNaming;
use App\Models\Empresa;
use App\Models\SpatieRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolesClienteSeeder extends Seeder
{
    protected const GUARD = 'web';

    /**
     * Crea roles del panel Cliente con company_id=1 y asigna permisos ya existentes en BD
     * (generados por shield:generate-cliente). No crea permisos.
     * Idempotente. Nombres con prefijo slug empresa + display con prefijo nombre empresa.
     */
    public function run(): void
    {
        $empresaId = 1;
        $empresa = Empresa::query()->find($empresaId);
        if ($empresa === null) {
            $this->command->warn('No existe empresa id 1. Ejecuta EmpresaEjemploSeeder antes.');

            return;
        }

        $permisosDepartamento = Permission::where('guard_name', self::GUARD)
            ->where('name', 'like', '%Departamento')
            ->where('name', 'not like', '%DepartamentoGeneral')
            ->pluck('name')
            ->toArray();

        $permisosDepartamentoGeneral = Permission::where('guard_name', self::GUARD)
            ->where('name', 'like', '%DepartamentoGeneral')
            ->pluck('name')
            ->toArray();

        $permisosCompletos = array_values(array_unique(array_merge($permisosDepartamento, $permisosDepartamentoGeneral)));
        $permisosSoloLectura = array_values(array_filter($permisosCompletos, static fn (string $name): bool => str_starts_with($name, 'ViewAny:') || str_starts_with($name, 'View:')));

        SpatieRole::withoutGlobalScopes()->firstOrCreate(
            [
                'name' => SeederRoleNaming::technical($empresa, 'admin_empresa'),
                'guard_name' => self::GUARD,
                'company_id' => $empresaId,
            ],
            [
                'display_name' => SeederRoleNaming::display($empresa, 'Administrador de empresa'),
                'description' => 'Acceso completo al panel Cliente (catálogos)',
            ]
        );
        $this->command->info('Rol admin_empresa listo.');

        SpatieRole::withoutGlobalScopes()->firstOrCreate(
            [
                'name' => SeederRoleNaming::technical($empresa, 'rh_empresa'),
                'guard_name' => self::GUARD,
                'company_id' => $empresaId,
            ],
            [
                'display_name' => SeederRoleNaming::display($empresa, 'RH Empresa'),
                'description' => 'Rol RH con permisos de lectura en catálogos (panel Cliente)',
            ]
        );
        $this->command->info('Rol rh_empresa listo.');

        $gestor = SpatieRole::withoutGlobalScopes()->firstOrCreate(
            [
                'name' => SeederRoleNaming::technical($empresa, 'gestor_catalogos'),
                'guard_name' => self::GUARD,
                'company_id' => $empresaId,
            ],
            [
                'display_name' => SeederRoleNaming::display($empresa, 'Gestor de catálogos'),
                'description' => 'CRUD Departamentos y Departamentos generales (panel Cliente)',
            ]
        );
        $gestor->syncPermissions($permisosCompletos);
        $this->command->info('Rol gestor_catalogos creado/actualizado con permisos CRUD.');

        $consultor = SpatieRole::withoutGlobalScopes()->firstOrCreate(
            [
                'name' => SeederRoleNaming::technical($empresa, 'consultor_catalogos'),
                'guard_name' => self::GUARD,
                'company_id' => $empresaId,
            ],
            [
                'display_name' => SeederRoleNaming::display($empresa, 'Consultor de catálogos'),
                'description' => 'Solo lectura Departamentos y Departamentos generales (panel Cliente)',
            ]
        );
        $consultor->syncPermissions($permisosSoloLectura);
        $this->command->info('Rol consultor_catalogos creado/actualizado con permisos de solo lectura.');
    }
}
