<?php

declare(strict_types=1);

use App\Filament\Resources\Usuarios\Schemas\UsuarioForm;
use App\Models\Empresa;
use App\Models\SpatieRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Guard;

require_once __DIR__.'/Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa1 = crearEmpresaMinima();
    $this->empresa2 = Empresa::withoutEvents(function (): Empresa {
        $e = $this->empresa1->replicate();
        $e->nombre = 'Empresa Test Roles Options 2';
        $e->save();

        return $e->fresh();
    });

    $guard = Guard::getDefaultName(SpatieRole::class);

    $this->roleEmpresa1 = SpatieRole::withoutGlobalScopes()->create([
        'name' => 'test_role_e1_'.uniqid(),
        'guard_name' => $guard,
        'display_name' => 'Rol empresa 1',
        'company_id' => $this->empresa1->id,
    ]);
    $this->roleEmpresa2 = SpatieRole::withoutGlobalScopes()->create([
        'name' => 'test_role_e2_'.uniqid(),
        'guard_name' => $guard,
        'display_name' => 'Rol empresa 2',
        'company_id' => $this->empresa2->id,
    ]);
    $this->roleGlobal = SpatieRole::withoutGlobalScopes()->create([
        'name' => 'test_role_global_'.uniqid(),
        'guard_name' => $guard,
        'display_name' => 'Rol global',
        'company_id' => null,
    ]);

    $super = SpatieRole::withoutGlobalScopes()->firstOrCreate(
        ['name' => 'super_admin', 'guard_name' => $guard],
        ['display_name' => 'Super Administrador', 'company_id' => null]
    );
    $this->admin = User::factory()->create();
    $this->admin->assignRole($super);
});

/**
 * @return array<int|string, string>
 */
function invokeRolesOptionsForForm(mixed $empresasState): array
{
    $m = new \ReflectionMethod(UsuarioForm::class, 'rolesOptionsForForm');
    $m->setAccessible(true);

    return $m->invoke(null, $empresasState);
}

it('filtra opciones de roles por empresas del formulario aunque el usuario sea super_admin', function (): void {
    $this->actingAs($this->admin);

    $options = invokeRolesOptionsForForm([$this->empresa1->id]);
    $ids = array_map('intval', array_keys($options));

    expect($ids)->toContain($this->roleEmpresa1->id)
        ->toContain($this->roleGlobal->id)
        ->not->toContain($this->roleEmpresa2->id);
});

it('con dos empresas seleccionadas incluye roles de ambas y globales', function (): void {
    $this->actingAs($this->admin);

    $options = invokeRolesOptionsForForm([$this->empresa1->id, $this->empresa2->id]);
    $ids = array_map('intval', array_keys($options));

    expect($ids)->toContain($this->roleEmpresa1->id)
        ->toContain($this->roleEmpresa2->id)
        ->toContain($this->roleGlobal->id);
});

it('sin empresas seleccionadas solo muestra roles globales', function (): void {
    $this->actingAs($this->admin);

    $options = invokeRolesOptionsForForm([]);
    $ids = array_map('intval', array_keys($options));

    expect($ids)->toContain($this->roleGlobal->id)
        ->not->toContain($this->roleEmpresa1->id)
        ->not->toContain($this->roleEmpresa2->id);
});
