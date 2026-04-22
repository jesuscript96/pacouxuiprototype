<?php

declare(strict_types=1);

use App\Models\SpatieRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->seed(\Database\Seeders\ShieldPermisosLegacySeeder::class);
});

// =========================================================================
// Permisos existen en BD
// =========================================================================

test('seeder crea los 4 permisos de CartaSua', function (): void {
    $permisos = Permission::where('name', 'like', '%CartaSua%')
        ->pluck('name')
        ->sort()
        ->values()
        ->toArray();

    expect($permisos)->toBe([
        'Create:CartaSua',
        'Delete:CartaSua',
        'View:CartaSua',
        'ViewAny:CartaSua',
    ]);
});

test('permisos CartaSua usan guard web', function (): void {
    $guards = Permission::where('name', 'like', '%CartaSua%')
        ->pluck('guard_name')
        ->unique()
        ->toArray();

    expect($guards)->toBe(['web']);
});

// =========================================================================
// Asignación a usuario
// =========================================================================

test('usuario con permiso ViewAny:CartaSua puede ver listado', function (): void {
    $empresa = crearEmpresaMinima();

    $role = SpatieRole::withoutGlobalScopes()->create([
        'name' => 'test_carta_sua_viewer_'.uniqid('', true),
        'guard_name' => 'web',
        'display_name' => 'Viewer CartaSua Test',
        'company_id' => $empresa->id,
    ]);
    $role->givePermissionTo('ViewAny:CartaSua');

    $user = User::factory()->cliente()->create(['empresa_id' => $empresa->id]);
    $user->assignRole($role);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect($user->can('ViewAny:CartaSua'))->toBeTrue()
        ->and($user->can('Create:CartaSua'))->toBeFalse();
});

test('usuario con todos los permisos CartaSua puede realizar todas las acciones', function (): void {
    $empresa = crearEmpresaMinima();

    $role = SpatieRole::withoutGlobalScopes()->create([
        'name' => 'test_carta_sua_admin_'.uniqid('', true),
        'guard_name' => 'web',
        'display_name' => 'Admin CartaSua Test',
        'company_id' => $empresa->id,
    ]);
    $role->givePermissionTo([
        'ViewAny:CartaSua',
        'View:CartaSua',
        'Create:CartaSua',
        'Delete:CartaSua',
    ]);

    $user = User::factory()->cliente()->create(['empresa_id' => $empresa->id]);
    $user->assignRole($role);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect($user->can('ViewAny:CartaSua'))->toBeTrue()
        ->and($user->can('View:CartaSua'))->toBeTrue()
        ->and($user->can('Create:CartaSua'))->toBeTrue()
        ->and($user->can('Delete:CartaSua'))->toBeTrue();
});

test('usuario sin rol no tiene permisos CartaSua', function (): void {
    $empresa = crearEmpresaMinima();

    $user = User::factory()->cliente()->create(['empresa_id' => $empresa->id]);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect($user->can('ViewAny:CartaSua'))->toBeFalse()
        ->and($user->can('View:CartaSua'))->toBeFalse()
        ->and($user->can('Create:CartaSua'))->toBeFalse()
        ->and($user->can('Delete:CartaSua'))->toBeFalse();
});
