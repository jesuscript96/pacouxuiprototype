<?php

declare(strict_types=1);

use App\Models\Empresa;
use App\Services\UsuarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    $this->empresa2 = Empresa::withoutEvents(function (): Empresa {
        $e = $this->empresa->replicate();
        $e->nombre = 'Empresa Test Usuario Service 2';
        $e->save();

        return $e->fresh();
    });
});

function datosUsuarioBase(array $overrides = []): array
{
    return array_merge([
        'name' => 'Juan',
        'apellido_paterno' => 'Pérez',
        'apellido_materno' => 'García',
        'email' => 'usuario.service.'.uniqid('', true).'@test.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'tipo' => ['administrador'],
        'roles' => [],
        'empresas' => [],
        'ver_reportes' => false,
    ], $overrides);
}

it('crea una fila empresa_user por empresa (pivot sin tipo; JSON conserva administrador y cliente)', function (): void {
    $service = app(UsuarioService::class);
    $user = $service->create(datosUsuarioBase([
        'tipo' => ['administrador', 'cliente'],
        'empresa_id' => $this->empresa->id,
        'empresas' => [$this->empresa->id],
    ]));

    $count = DB::table('empresa_user')
        ->where('user_id', $user->id)
        ->where('empresa_id', $this->empresa->id)
        ->count();

    expect($count)->toBe(1)
        ->and($user->tieneRol('administrador'))->toBeTrue()
        ->and($user->tieneRol('cliente'))->toBeTrue();
});

it('crea pivot cliente y colaborador por empresa cuando el JSON incluye ambos tipos', function (): void {
    $service = app(UsuarioService::class);
    $user = $service->create(datosUsuarioBase([
        'tipo' => ['cliente', 'colaborador'],
        'empresa_id' => $this->empresa->id,
        'empresas' => [$this->empresa->id],
    ]));

    expect($user->tieneRol('cliente'))->toBeTrue()
        ->and($user->tieneRol('colaborador'))->toBeTrue()
        ->and((int) $user->empresa_id)->toBe($this->empresa->id);

    $count = DB::table('empresa_user')
        ->where('user_id', $user->id)
        ->where('empresa_id', $this->empresa->id)
        ->count();

    expect($count)->toBe(1);
});

it('mantiene empresa_id principal y fusiona pivot con empresas del multiselect', function (): void {
    $service = app(UsuarioService::class);
    $user = $service->create(datosUsuarioBase([
        'tipo' => ['cliente'],
        'empresa_id' => $this->empresa->id,
        'empresas' => [$this->empresa2->id],
    ]));

    expect((int) $user->empresa_id)->toBe($this->empresa->id);

    $ids = $user->empresas()->get()->pluck('id')->sort()->values()->all();
    expect($ids)->toEqual(
        collect([$this->empresa->id, $this->empresa2->id])->sort()->values()->all(),
    );
});

it('al quitar una empresa elimina pivot y revoca roles Spatie ligados a esa empresa', function (): void {
    $role = \App\Models\SpatieRole::withoutGlobalScopes()->create([
        'name' => 'rol_test_empresa_'.uniqid(),
        'guard_name' => 'web',
        'display_name' => 'Rol test',
        'company_id' => $this->empresa2->id,
    ]);

    $service = app(UsuarioService::class);
    $user = $service->create(datosUsuarioBase([
        'tipo' => ['cliente'],
        'empresa_id' => $this->empresa->id,
        'empresas' => [$this->empresa->id, $this->empresa2->id],
        'roles' => [$role->id],
    ]));

    expect($user->hasRole($role))->toBeTrue();

    // Tras quitar la empresa 2, el formulario solo deja roles válidos; sin ese rol en el payload.
    $service->update($user, datosUsuarioBase([
        'tipo' => ['cliente'],
        'empresa_id' => $this->empresa->id,
        'empresas' => [$this->empresa->id],
        'roles' => [],
    ]));

    $user->refresh();
    expect($user->hasRole($role))->toBeFalse()
        ->and(
            DB::table('empresa_user')
                ->where('user_id', $user->id)
                ->where('empresa_id', $this->empresa2->id)
                ->exists()
        )->toBeFalse();
});

it('rechaza cliente o colaborador sin empresas', function (): void {
    $service = app(UsuarioService::class);
    $service->create(datosUsuarioBase([
        'tipo' => ['cliente'],
        'empresas' => [],
    ]));
})->throws(\Illuminate\Validation\ValidationException::class);

it('rechaza asignar rol de empresa no incluida cuando el editor no es super_admin', function (): void {
    $role = \App\Models\SpatieRole::withoutGlobalScopes()->create([
        'name' => 'rol_validacion_emp_'.uniqid(),
        'guard_name' => 'web',
        'display_name' => 'Rol empresa 2',
        'company_id' => $this->empresa2->id,
    ]);

    $editor = \App\Models\User::factory()->administrador()->create([
        'email' => 'editor.validacion.'.uniqid('', true).'@test.com',
    ]);

    $this->actingAs($editor);

    $service = app(UsuarioService::class);
    $service->create(datosUsuarioBase([
        'tipo' => ['cliente'],
        'empresa_id' => $this->empresa->id,
        'empresas' => [$this->empresa->id],
        'roles' => [$role->id],
    ]));
})->throws(\Illuminate\Validation\ValidationException::class);
