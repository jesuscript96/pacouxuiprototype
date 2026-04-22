<?php

namespace Database\Seeders;

use App\Helpers\SeederRoleNaming;
use App\Models\Empresa;
use App\Models\SpatieRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class SpatieRolesSeeder extends Seeder
{
    /**
     * Crea roles base: super_admin (global) y roles por empresa (company_id = 1 si existe).
     * Nombres técnicos: {slug_empresa}_{base}; display: {nombre empresa} - {etiqueta}.
     * Ejecutar después de shield:generate para tener permisos disponibles.
     */
    public function run(): void
    {
        $guard = 'web';

        $superAdmin = SpatieRole::withoutGlobalScopes()->updateOrCreate(
            ['name' => 'super_admin', 'guard_name' => $guard],
            [
                'display_name' => 'Super Administrador',
                'description' => 'Acceso total al sistema',
                'company_id' => null,
            ]
        );
        $permissions = Permission::all();
        if ($permissions->isNotEmpty()) {
            $superAdmin->syncPermissions($permissions);
        }

        $empresaId = 1;
        $empresa = Empresa::query()->find($empresaId);
        if ($empresa === null) {
            return;
        }

        $rolesEmpresa = [
            [
                'base' => 'admin_empresa',
                'label' => 'Administrador de empresa',
                'description' => 'Administrador del panel Cliente y catálogos',
            ],
            [
                'base' => 'rh_empresa',
                'label' => 'RH Empresa',
                'description' => 'Rol RH con permisos de lectura en catálogos (panel Cliente)',
            ],
            [
                'base' => 'colaborador',
                'label' => 'Colaborador',
                'description' => 'Rol colaborador de la empresa',
            ],
        ];

        foreach ($rolesEmpresa as $def) {
            $name = SeederRoleNaming::technical($empresa, $def['base']);
            SpatieRole::withoutGlobalScopes()->firstOrCreate(
                [
                    'name' => $name,
                    'guard_name' => $guard,
                    'company_id' => $empresaId,
                ],
                [
                    'display_name' => SeederRoleNaming::display($empresa, $def['label']),
                    'description' => $def['description'],
                ]
            );
        }
    }
}
