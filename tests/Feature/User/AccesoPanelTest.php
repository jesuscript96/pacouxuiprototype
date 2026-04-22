<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
});

it('usuario tipo administrador puede acceder al panel admin', function (): void {
    $user = User::factory()->administrador()->create();
    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
});

it('usuario tipo cliente con empresas puede acceder al panel cliente', function (): void {
    $empresa = crearEmpresaMinima();
    $user = User::factory()->cliente()->create(['empresa_id' => $empresa->id]);
    $user->empresas()->syncWithoutDetaching([$empresa->id]);

    expect($user->canAccessPanel(Filament::getPanel('cliente')))->toBeTrue();
});

it('usuario solo colaborador no accede a admin ni cliente', function (): void {
    $empresa = crearEmpresaMinima();
    $user = crearUserColaborador($empresa, ['email' => 'solo.colab.panel@test.com']);

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse()
        ->and($user->canAccessPanel(Filament::getPanel('cliente')))->toBeFalse();
});

it('usuario demo cliente@tecben.com puede autenticarse en admin y cliente (prototipo enseñable)', function (): void {
    $empresa = crearEmpresaMinima();
    $user = User::factory()->create([
        'email' => 'cliente@tecben.com',
        'password' => 'password',
        'tipo' => ['cliente'],
        'empresa_id' => $empresa->id,
    ]);
    $user->empresas()->syncWithoutDetaching([$empresa->id]);

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeTrue()
        ->and($user->canAccessPanel(Filament::getPanel('cliente')))->toBeTrue();
});

it('GET /admin con usuario cliente redirige al panel cliente', function (): void {
    $empresa = crearEmpresaMinima();
    $user = User::factory()->cliente()->create([
        'email' => 'home-admin-cliente@test.com',
        'password' => 'password',
        'empresa_id' => $empresa->id,
    ]);
    $user->empresas()->syncWithoutDetaching([$empresa->id]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertRedirect();
    expect((string) $response->headers->get('Location'))->toContain('/cliente');
});

it('GET /admin con administrador de plataforma muestra el dashboard admin', function (): void {
    $user = User::factory()->administrador()->create();

    $this->actingAs($user)->get('/admin')->assertOk();
});

it('usuario con tipo cliente y colaborador accede al panel cliente', function (): void {
    $empresa = crearEmpresaMinima();
    $user = User::factory()->create([
        'tipo' => ['cliente', 'colaborador'],
        'empresa_id' => $empresa->id,
        'name' => 'Mix',
        'apellido_paterno' => 'Panel',
        'apellido_materno' => 'Test',
        'email' => 'mix.panel@test.com',
        'telefono_movil' => '5511223344',
        'fecha_nacimiento' => '1990-01-01',
        'fecha_ingreso' => '2024-01-01',
        'periodicidad_pago' => 'QUINCENAL',
    ]);
    $user->empresas()->attach($empresa->id);

    expect($user->canAccessPanel(Filament::getPanel('cliente')))->toBeTrue()
        ->and($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});
