<?php

declare(strict_types=1);

use App\Models\Empresa;
use App\Models\Industria;
use App\Models\SpatieRole;
use App\Models\Subindustria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $industria = Industria::withoutEvents(fn (): Industria => Industria::query()->create([
        'nombre' => 'Industria Test',
    ]));

    $subindustria = Subindustria::withoutEvents(fn (): Subindustria => Subindustria::query()->create([
        'nombre' => 'Subindustria Test',
        'industria_id' => $industria->id,
    ]));

    $this->empresa = Empresa::withoutEvents(fn (): Empresa => Empresa::factory()->create([
        'industria_id' => $industria->id,
        'sub_industria_id' => $subindustria->id,
    ]));
});

test('puede eliminar rol sin usuarios asignados', function (): void {
    $role = SpatieRole::create([
        'name' => 'rol_prueba',
        'guard_name' => 'web',
    ]);

    expect($role->tieneUsuariosAsignados())->toBeFalse();

    $role->delete();

    expect(SpatieRole::withoutGlobalScopes()->find($role->id))->toBeNull();
});

test('no puede eliminar rol con usuarios asignados', function (): void {
    $role = SpatieRole::create([
        'name' => 'rol_con_usuarios',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);
    $user->assignRole($role);

    expect($role->tieneUsuariosAsignados())->toBeTrue();

    expect(fn () => $role->delete())
        ->toThrow(ValidationException::class, 'tiene usuarios asignados');
});

test('tieneUsuariosAsignados retorna true cuando hay usuarios', function (): void {
    $role = SpatieRole::create([
        'name' => 'rol_test',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);
    $user->assignRole($role);

    expect($role->tieneUsuariosAsignados())->toBeTrue();
});

test('tieneUsuariosAsignados retorna false cuando no hay usuarios', function (): void {
    $role = SpatieRole::create([
        'name' => 'rol_vacio',
        'guard_name' => 'web',
    ]);

    expect($role->tieneUsuariosAsignados())->toBeFalse();
});
