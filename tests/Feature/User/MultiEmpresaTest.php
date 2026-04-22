<?php

declare(strict_types=1);

use App\Models\Empresa;
use App\Models\SpatieRole;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->e1 = crearEmpresaMinima();
    $this->e2 = Empresa::withoutEvents(function (): Empresa {
        $e = $this->e1->replicate();
        $e->nombre = 'Empresa Multi Test 2 '.uniqid('', true);
        $e->save();

        return $e->fresh();
    });
});

it('usuario cliente en dos empresas tiene ambas en la relación empresas', function (): void {
    $user = User::factory()->cliente()->create(['empresa_id' => $this->e1->id]);
    $user->empresas()->attach($this->e1->id);
    $user->empresas()->attach($this->e2->id);

    $ids = $user->empresas()->get()->pluck('id')->sort()->values()->all();
    expect($ids)->toEqual(collect([$this->e1->id, $this->e2->id])->sort()->values()->all());
});

it('al quitar una empresa el usuario conserva la otra en pivot', function (): void {
    $user = User::factory()->cliente()->create(['empresa_id' => $this->e1->id]);
    $user->empresas()->attach($this->e1->id);
    $user->empresas()->attach($this->e2->id);

    $user->empresas()->detach($this->e1->id);
    $user->refresh();

    expect($user->empresas)->toHaveCount(1)
        ->and($user->empresas->first()->id)->toBe($this->e2->id);
});

it('UsuarioService revoca roles Spatie de la empresa eliminada del formulario', function (): void {
    $role = SpatieRole::withoutGlobalScopes()->create([
        'name' => 'rol_multi_emp_'.uniqid(),
        'guard_name' => 'web',
        'display_name' => 'Rol multi',
        'company_id' => $this->e2->id,
    ]);

    $service = app(UsuarioService::class);
    $user = $service->create([
        'name' => 'Multi',
        'apellido_paterno' => 'Emp',
        'apellido_materno' => 'Test',
        'email' => 'multi.emp.'.uniqid().'@test.com',
        'password' => 'Password123!',
        'tipo' => ['cliente'],
        'empresa_id' => $this->e1->id,
        'empresas' => [$this->e1->id, $this->e2->id],
        'roles' => [$role->id],
        'ver_reportes' => false,
    ]);

    expect($user->hasRole($role))->toBeTrue();

    $service->update($user, [
        'name' => 'Multi',
        'apellido_paterno' => 'Emp',
        'apellido_materno' => 'Test',
        'email' => $user->email,
        'tipo' => ['cliente'],
        'empresa_id' => $this->e1->id,
        'empresas' => [$this->e1->id],
        'roles' => [$role->id],
        'ver_reportes' => false,
    ]);

    $user->refresh();
    expect($user->hasRole($role))->toBeFalse();
});
