<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('permite tipo solo administrador', function (): void {
    $user = User::factory()->administrador()->create();
    expect($user->tipo)->toBe(['administrador'])
        ->and($user->tieneRol('administrador'))->toBeTrue()
        ->and($user->tieneRol('cliente'))->toBeFalse();
});

it('permite tipo cliente y colaborador simultáneo', function (): void {
    $user = User::factory()->create([
        'tipo' => ['cliente', 'colaborador'],
        'name' => 'A',
        'apellido_paterno' => 'B',
        'apellido_materno' => 'C',
    ]);
    expect($user->fresh()->tipo)->toBe(['cliente', 'colaborador'])
        ->and($user->tieneRol('cliente'))->toBeTrue()
        ->and($user->tieneRol('colaborador'))->toBeTrue();
});

it('tieneRol detecta cada rol del JSON', function (): void {
    $user = User::factory()->create(['tipo' => ['administrador', 'cliente']]);
    expect($user->tieneRol('administrador'))->toBeTrue()
        ->and($user->tieneRol('cliente'))->toBeTrue()
        ->and($user->tieneRol('colaborador'))->toBeFalse();
});

it('agregarRol no duplica', function (): void {
    $user = User::factory()->create(['tipo' => ['cliente']]);
    $user->agregarRol('cliente');
    $user->agregarRol('cliente');
    expect($user->fresh()->tipo)->toBe(['cliente']);
});

it('quitarRol conserva los demás', function (): void {
    $user = User::factory()->create(['tipo' => ['cliente', 'colaborador', 'administrador']]);
    $user->quitarRol('cliente');
    $user->refresh();
    expect($user->tipo)->toHaveCount(2)
        ->and($user->tipo)->toContain('colaborador', 'administrador');
});

it('scopeConRol filtra por JSON', function (): void {
    User::factory()->cliente()->create(['email' => 'c1@test.com']);
    User::factory()->colaborador()->create(['email' => 'col@test.com']);

    $ids = User::query()->conRol('cliente')->pluck('id')->all();
    expect($ids)->toHaveCount(1)
        ->and(User::find($ids[0])->email)->toBe('c1@test.com');
});
