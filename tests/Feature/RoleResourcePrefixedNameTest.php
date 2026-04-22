<?php

declare(strict_types=1);

use App\Filament\Resources\Shield\RoleResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $base = crearEmpresaMinima();
    $this->empresa = $base->replicate();
    $this->empresa->nombre = 'Palacio de Hierro';
    $this->empresa->save();
});

it('genera prefijo slug underscore desde nombre de empresa', function (): void {
    expect(RoleResource::namePrefixForEmpresaId($this->empresa->id))->toBe('palacio_de_hierro_');
});

it('concatena prefijo y sufijo en fullRoleName', function (): void {
    expect(RoleResource::fullRoleName($this->empresa->id, 'admin_rh'))->toBe('palacio_de_hierro_admin_rh');
});

it('extrae sufijo desde nombre almacenado', function (): void {
    expect(RoleResource::suffixFromStoredName($this->empresa->id, 'palacio_de_hierro_admin_rh'))->toBe('admin_rh');
});

it('mergeRoleNameFromFormData concatena cuando hay company_id', function (): void {
    $merged = RoleResource::mergeRoleNameFromFormData([
        'role_name_edit' => 'rh',
        'company_id' => $this->empresa->id,
    ]);

    expect($merged['name'])->toBe('palacio_de_hierro_rh')
        ->and(isset($merged['role_name_edit']))->toBeFalse();
});

it('mergeRoleNameFromFormData usa texto completo para rol global', function (): void {
    $merged = RoleResource::mergeRoleNameFromFormData([
        'role_name_edit' => 'gerente_paco',
        'company_id' => null,
    ]);

    expect($merged['name'])->toBe('gerente_paco');
});

it('mergeRoleNameFromFormData concatena con company_id aunque is_asignable no venga', function (): void {
    $merged = RoleResource::mergeRoleNameFromFormData([
        'role_name_edit' => 'gestor',
        'company_id' => $this->empresa->id,
    ]);

    expect($merged['name'])->toBe('palacio_de_hierro_gestor');
});

it('roleNameInputForFill devuelve sufijo cuando aplica prefijo', function (): void {
    $suffix = RoleResource::roleNameInputForFill([
        'name' => 'palacio_de_hierro_admin_rh',
        'company_id' => $this->empresa->id,
    ]);

    expect($suffix)->toBe('admin_rh');
});

it('roleNameInputForFill devuelve nombre completo para rol global', function (): void {
    $value = RoleResource::roleNameInputForFill([
        'name' => 'super_rol',
        'company_id' => null,
    ]);

    expect($value)->toBe('super_rol');
});

it('mergeDisplayNameForStorage antepone nombre de empresa', function (): void {
    $merged = RoleResource::mergeDisplayNameForStorage([
        'company_id' => $this->empresa->id,
        'display_name' => 'RH Corporativo',
    ]);

    expect($merged['display_name'])->toBe('Palacio de Hierro - RH Corporativo');
});
