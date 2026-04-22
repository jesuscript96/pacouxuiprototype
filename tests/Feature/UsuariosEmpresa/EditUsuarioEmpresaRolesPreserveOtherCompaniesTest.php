<?php

declare(strict_types=1);

use App\Models\SpatieRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Guard;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

/**
 * Replica la lógica de afterSave en EditUsuarioEmpresa: syncRoles(keep del resto de empresas + roles del tenant).
 */
it('con shield.company_id del tenant, conserva roles de otra empresa al sincronizar solo roles del tenant', function (): void {
    $this->seed(\Database\Seeders\Inicial::class);

    $empresa1 = crearEmpresaMinima();
    $empresa2 = crearEmpresaMinima([
        'nombre' => 'Empresa roles preserve 2',
        'email_contacto' => 'contacto-preserve2@test.com',
        'email_facturacion' => 'fact-preserve2@test.com',
    ]);

    $guard = Guard::getDefaultName(SpatieRole::class);

    $rolEmpresa1 = SpatieRole::withoutGlobalScopes()->create([
        'name' => 'rol_preserve_e1_'.uniqid(),
        'guard_name' => $guard,
        'display_name' => 'Rol E1',
        'company_id' => $empresa1->id,
    ]);
    $rolEmpresa2 = SpatieRole::withoutGlobalScopes()->create([
        'name' => 'rol_preserve_e2_'.uniqid(),
        'guard_name' => $guard,
        'display_name' => 'Rol E2',
        'company_id' => $empresa2->id,
    ]);

    $user = User::factory()->cliente()->create(['empresa_id' => $empresa1->id]);
    $user->assignRole([$rolEmpresa1, $rolEmpresa2]);

    request()->attributes->set('shield.company_id', $empresa1->id);

    $user = $user->fresh();
    expect($user->roles)->toHaveCount(1)
        ->and($user->roles->first()->id)->toBe($rolEmpresa1->id);

    $tenantId = (int) $empresa1->id;
    $keep = $user->roles()->withoutGlobalScopes()->get()->filter(
        fn (SpatieRole $r): bool => (int) ($r->company_id ?? 0) !== $tenantId
    );
    $new = SpatieRole::query()->withoutGlobalScopes()
        ->whereIn('id', [$rolEmpresa1->id])
        ->get();

    $user->syncRoles($keep->merge($new)->unique('id')->values()->all());

    $ids = $user->fresh()->roles()->withoutGlobalScopes()->pluck('id')->all();

    expect($ids)->toContain($rolEmpresa1->id)
        ->toContain($rolEmpresa2->id);
});
