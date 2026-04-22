<?php

declare(strict_types=1);

use App\Http\Middleware\ScopeByCompany;
use App\Models\Empresa;
use App\Models\SpatieRole;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

require_once __DIR__.'/Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
});

/**
 * @return array{0: User, 1: Empresa, 2: Empresa, 3: Permission, 4: Permission}
 */
function scopeByCompanySetupUserTwoEmpresasTwoRoles(): array
{
    $e1 = crearEmpresaMinima();
    $e2 = Empresa::withoutEvents(function () use ($e1): Empresa {
        $e = $e1->replicate();
        $e->nombre = 'Emp Scope Permisos '.uniqid('', true);
        $e->save();

        return $e->fresh();
    });

    $suffix = uniqid('', true);
    $permA = Permission::create(['name' => "TestScope:PermisoA_{$suffix}", 'guard_name' => 'web']);
    $permB = Permission::create(['name' => "TestScope:PermisoB_{$suffix}", 'guard_name' => 'web']);

    $role1 = SpatieRole::withoutGlobalScopes()->create([
        'name' => 'test_scope_r1_'.$suffix,
        'guard_name' => 'web',
        'display_name' => 'Rol empresa 1',
        'company_id' => $e1->id,
    ]);
    $role1->syncPermissions([$permA]);

    $role2 = SpatieRole::withoutGlobalScopes()->create([
        'name' => 'test_scope_r2_'.$suffix,
        'guard_name' => 'web',
        'display_name' => 'Rol empresa 2',
        'company_id' => $e2->id,
    ]);
    $role2->syncPermissions([$permB]);

    $user = User::factory()->cliente()->create([
        'empresa_id' => $e1->id,
    ]);
    $user->assignRole($role1);
    $user->assignRole($role2);
    $user->empresas()->sync([$e1->id, $e2->id]);

    return [$user, $e1, $e2, $permA, $permB];
}

it('filtra permisos por shield.company_id según roles de la empresa activa', function (): void {
    [$user, $e1, $e2, $permA, $permB] = scopeByCompanySetupUserTwoEmpresasTwoRoles();

    $this->actingAs($user);

    request()->attributes->set('shield.company_id', (int) $e1->id);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $user->unsetRelation('roles');

    expect($user->can($permA->name))->toBeTrue()
        ->and($user->can($permB->name))->toBeFalse();

    request()->attributes->set('shield.company_id', (int) $e2->id);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $user->unsetRelation('roles');

    expect($user->can($permA->name))->toBeFalse()
        ->and($user->can($permB->name))->toBeTrue();
});

it('sin shield.company_id el usuario conserva la unión de permisos de todos sus roles', function (): void {
    [$user, $e1, $e2, $permA, $permB] = scopeByCompanySetupUserTwoEmpresasTwoRoles();

    $this->actingAs($user);

    request()->attributes->remove('shield.company_id');
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $user->unsetRelation('roles');

    expect($user->can($permA->name))->toBeTrue()
        ->and($user->can($permB->name))->toBeTrue();
});

it('ScopeByCompany limpia caché de Spatie al cambiar el contexto de empresa en sesión', function (): void {
    [$user, $e1, $e2, $permA, $permB] = scopeByCompanySetupUserTwoEmpresasTwoRoles();

    $this->actingAs($user);

    session(['scope_by_company.context.'.$user->getKey() => (int) $e1->id]);

    Filament::setTenant($e2);

    $request = Request::create('/cliente', 'GET');
    $request->setUserResolver(static fn (): User => $user);
    app()->instance('request', $request);

    $middleware = app(ScopeByCompany::class);
    $middleware->handle($request, static fn (): \Symfony\Component\HttpFoundation\Response => response('ok'));

    expect((int) $request->attributes->get('shield.company_id'))->toBe((int) $e2->id)
        ->and(session('scope_by_company.context.'.$user->getKey()))->toBe((int) $e2->id);

    expect($user->can($permA->name))->toBeFalse()
        ->and($user->can($permB->name))->toBeTrue();

    Filament::setTenant(null);
});

it('ScopeByCompany usa tenant de la ruta cuando Filament::getTenant() es null', function (): void {
    [$user, $e1, $e2, $permA, $permB] = scopeByCompanySetupUserTwoEmpresasTwoRoles();

    $this->actingAs($user);

    session(['scope_by_company.context.'.$user->getKey() => (int) $e1->id]);

    Filament::setTenant(null);

    $request = Request::create('/cliente/'.$e2->getKey().'/roles', 'GET');
    $request->setUserResolver(static fn (): User => $user);
    $route = new Route(['GET'], 'cliente/{tenant}/roles', static fn (): string => 'ok');
    $route->parameters = ['tenant' => $e2];
    $request->setRouteResolver(static fn () => $route);
    app()->instance('request', $request);

    $middleware = app(ScopeByCompany::class);
    $middleware->handle($request, static fn (): \Symfony\Component\HttpFoundation\Response => response('ok'));

    expect((int) $request->attributes->get('shield.company_id'))->toBe((int) $e2->id)
        ->and($user->can($permA->name))->toBeFalse()
        ->and($user->can($permB->name))->toBeTrue();
});
