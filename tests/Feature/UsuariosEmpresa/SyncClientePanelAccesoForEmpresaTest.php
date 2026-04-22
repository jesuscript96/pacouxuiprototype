<?php

declare(strict_types=1);

use App\Actions\User\SyncClientePanelAccesoForEmpresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    $this->empresa2 = crearEmpresaMinima([
        'nombre' => 'Empresa 2 Acceso Test',
        'email_contacto' => 'c2-acceso@test.com',
        'email_facturacion' => 'f2-acceso@test.com',
    ]);
});

it('al activar agrega cliente al tipo y fila en empresa_user', function (): void {
    $user = User::factory()->colaborador()->create([
        'tipo' => ['colaborador'],
        'empresa_id' => $this->empresa->id,
    ]);
    expect($user->tipo)->not->toContain('cliente');
    expect($user->empresas()->count())->toBe(0);

    app(SyncClientePanelAccesoForEmpresa::class)($user, $this->empresa, true);

    $user->refresh();
    expect($user->tipo)->toContain('cliente');
    expect($user->empresas()->where('empresas.id', $this->empresa->id)->exists())->toBeTrue();
});

it('al desactivar con una sola empresa quita pivote y quita cliente del tipo', function (): void {
    $user = User::factory()->cliente()->create(['empresa_id' => $this->empresa->id]);
    $user->empresas()->attach($this->empresa->id);
    expect($user->empresas()->count())->toBe(1);
    expect($user->tipo)->toContain('cliente');

    app(SyncClientePanelAccesoForEmpresa::class)($user, $this->empresa, false);

    $user->refresh();
    expect($user->empresas()->count())->toBe(0);
    expect($user->tipo)->not->toContain('cliente');
});

it('al desactivar una empresa mantiene cliente si queda otra en el pivote', function (): void {
    $user = User::factory()->cliente()->create();
    $user->empresas()->attach([$this->empresa->id, $this->empresa2->id]);
    expect($user->empresas()->count())->toBe(2);

    app(SyncClientePanelAccesoForEmpresa::class)($user, $this->empresa, false);

    $user->refresh();
    expect($user->empresas()->count())->toBe(1);
    expect($user->tipo)->toContain('cliente');
});
