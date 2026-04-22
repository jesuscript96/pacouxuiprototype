<?php

namespace Database\Seeders;

use App\Helpers\SeederRoleNaming;
use App\Models\Empresa;
use App\Models\SpatieRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ShieldPermisosRolesSeeder extends Seeder
{
    /**
     * Asigna permisos a roles por empresa (admin_empresa, rh_empresa).
     * Ejecutar después de SpatieRolesSeeder y shield:generate.
     */
    public function run(): void
    {
        $empresaId = 1;
        $empresa = Empresa::query()->find($empresaId);
        if ($empresa === null) {
            $this->command->warn('No existe empresa id 1. Crea la empresa o ajusta $empresaId.');

            return;
        }

        $adminEmpresa = SpatieRole::withoutGlobalScopes()
            ->where('name', SeederRoleNaming::technical($empresa, 'admin_empresa'))
            ->where('company_id', $empresaId)
            ->first();

        if ($adminEmpresa) {
            $adminPermisos = Permission::whereIn('name', [
                'ViewAny:Empresa', 'View:Empresa', 'Create:Empresa', 'Update:Empresa', 'Delete:Empresa',
                'ViewAny:CentroCosto', 'View:CentroCosto', 'Create:CentroCosto', 'Update:CentroCosto', 'Delete:CentroCosto',
                'ViewAny:Industria', 'View:Industria', 'Create:Industria', 'Update:Industria',
                'ViewAny:Subindustria', 'View:Subindustria', 'Create:Subindustria', 'Update:Subindustria',
                'ViewAny:Producto', 'View:Producto', 'Create:Producto', 'Update:Producto',
                'ViewAny:NotificacionesIncluidas', 'View:NotificacionesIncluidas', 'Create:NotificacionesIncluidas', 'Update:NotificacionesIncluidas',
                'ViewAny:Reconocmiento', 'View:Reconocmiento', 'Create:Reconocmiento', 'Update:Reconocmiento',
                'ViewAny:TemaVozColaborador', 'View:TemaVozColaborador', 'Create:TemaVozColaborador', 'Update:TemaVozColaborador',
                'ViewAny:Departamento', 'View:Departamento', 'Create:Departamento', 'Update:Departamento', 'Delete:Departamento',
                'ViewAny:DepartamentoGeneral', 'View:DepartamentoGeneral', 'Create:DepartamentoGeneral', 'Update:DepartamentoGeneral', 'Delete:DepartamentoGeneral',
                'ViewAny:NotificacionPush', 'View:NotificacionPush', 'Create:NotificacionPush', 'Update:NotificacionPush', 'Delete:NotificacionPush',
                'ViewAny:CartaSua', 'View:CartaSua', 'Create:CartaSua', 'Delete:CartaSua',
            ])->pluck('name');
            $adminEmpresa->syncPermissions($adminPermisos);
            $this->command->info('Permisos asignados a rol admin_empresa (empresa '.$empresaId.').');
        }

        $rhEmpresa = SpatieRole::withoutGlobalScopes()
            ->where('name', SeederRoleNaming::technical($empresa, 'rh_empresa'))
            ->where('company_id', $empresaId)
            ->first();

        if ($rhEmpresa) {
            $rhPermisos = Permission::whereIn('name', [
                'ViewAny:Empresa', 'View:Empresa',
                'ViewAny:CentroCosto', 'View:CentroCosto',
                'ViewAny:Industria', 'View:Industria',
                'ViewAny:Subindustria', 'View:Subindustria',
                'ViewAny:Producto', 'View:Producto',
                'ViewAny:NotificacionesIncluidas', 'View:NotificacionesIncluidas',
                'ViewAny:Reconocmiento', 'View:Reconocmiento',
                'ViewAny:TemaVozColaborador', 'View:TemaVozColaborador',
                'ViewAny:Departamento', 'View:Departamento',
                'ViewAny:DepartamentoGeneral', 'View:DepartamentoGeneral',
                'ViewAny:NotificacionPush', 'View:NotificacionPush',
                'ViewAny:CartaSua', 'View:CartaSua',
            ])->pluck('name');
            $rhEmpresa->syncPermissions($rhPermisos);
            $this->command->info('Permisos asignados a rol rh_empresa (empresa '.$empresaId.').');
        }
    }
}
