<?php

declare(strict_types=1);

use App\Models\CartaSua;
use App\Models\Colaborador;
use App\Models\User;
use App\Services\Nubarium\NubariumFirmaService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    $this->empresa = crearEmpresaMinima(['tiene_firma_nubarium' => true]);

    $this->colaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $this->user = User::factory()->colaborador()->create([
        'colaborador_id' => $this->colaborador->id,
        'empresa_id' => $this->empresa->id,
        'email' => fake()->unique()->safeEmail(),
    ]);
});

// =========================================================================
// Listado
// =========================================================================

test('colaborador puede listar sus cartas sua', function (): void {
    CartaSua::factory()->count(3)->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    CartaSua::factory()->create(['empresa_id' => $this->empresa->id]);

    Sanctum::actingAs($this->user);

    $this->getJson('/api/cartas-sua')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(3, 'data');
});

test('listado filtra por bimestre', function (): void {
    CartaSua::factory()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
        'bimestre' => 'Enero-Febrero 2024',
    ]);

    CartaSua::factory()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
        'bimestre' => 'Marzo-Abril 2024',
    ]);

    Sanctum::actingAs($this->user);

    $this->getJson('/api/cartas-sua?bimestre=Enero-Febrero%202024')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.bimestre', 'Enero-Febrero 2024');
});

test('listado filtra por estado', function (): void {
    CartaSua::factory()->pendiente()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    CartaSua::factory()->firmada()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    Sanctum::actingAs($this->user);

    $this->getJson('/api/cartas-sua?estado=pendiente')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->getJson('/api/cartas-sua?estado=firmada')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

// =========================================================================
// Resumen
// =========================================================================

test('colaborador puede ver resumen de sus cartas', function (): void {
    CartaSua::factory()->pendiente()->count(2)->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    CartaSua::factory()->vista()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    CartaSua::factory()->firmada()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    Sanctum::actingAs($this->user);

    $this->getJson('/api/cartas-sua/resumen')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total', 4)
        ->assertJsonPath('data.pendientes', 2)
        ->assertJsonPath('data.vistas', 1)
        ->assertJsonPath('data.firmadas', 1);
});

// =========================================================================
// Detalle
// =========================================================================

test('colaborador puede ver detalle de su carta', function (): void {
    $carta = CartaSua::factory()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
        'bimestre' => 'Enero-Febrero 2024',
    ]);

    Sanctum::actingAs($this->user);

    $this->getJson("/api/cartas-sua/{$carta->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.bimestre', 'Enero-Febrero 2024')
        ->assertJsonPath('data.retiro', $carta->retiro)
        ->assertJsonPath('data.pdf_disponible', false);
});

test('colaborador no puede ver carta de otro', function (): void {
    $otraCarta = CartaSua::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    Sanctum::actingAs($this->user);

    $this->getJson("/api/cartas-sua/{$otraCarta->id}")
        ->assertNotFound();
});

// =========================================================================
// Visualización
// =========================================================================

test('colaborador puede registrar visualizacion', function (): void {
    $carta = CartaSua::factory()->pendiente()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    Sanctum::actingAs($this->user);

    $this->postJson("/api/cartas-sua/{$carta->id}/visualizar")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.estado', CartaSua::ESTADO_VISTA);

    $carta->refresh();
    expect($carta->primera_visualizacion)->not->toBeNull()
        ->and($carta->ultima_visualizacion)->not->toBeNull();
});

test('segunda visualizacion solo actualiza ultima_visualizacion', function (): void {
    $carta = CartaSua::factory()->vista()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    $primeraVez = $carta->primera_visualizacion->toIso8601String();

    Sanctum::actingAs($this->user);

    $this->postJson("/api/cartas-sua/{$carta->id}/visualizar")
        ->assertOk();

    $carta->refresh();
    expect($carta->primera_visualizacion->toIso8601String())->toBe($primeraVez)
        ->and($carta->ultima_visualizacion)->not->toBeNull();
});

// =========================================================================
// Firma
// =========================================================================

test('colaborador puede firmar carta con nubarium', function (): void {
    $mockNubarium = Mockery::mock(NubariumFirmaService::class);
    $mockNubarium->shouldReceive('firmarCartaSua')
        ->once()
        ->andReturn([
            'success' => true,
            'nom151' => 'constancia-test-123',
            'hash' => 'hash-test-abc',
            'codigo_validacion' => 'CODE-123',
            'pdf_firmado' => null,
        ]);

    app()->instance(NubariumFirmaService::class, $mockNubarium);

    $carta = CartaSua::factory()->vista()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    Sanctum::actingAs($this->user);

    $this->postJson("/api/cartas-sua/{$carta->id}/firmar", [
        'imagen_firma' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUg==',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.firmado', true)
        ->assertJsonPath('data.tiene_nom151', true)
        ->assertJsonPath('data.codigo_validacion', 'CODE-123');

    $carta->refresh();
    expect($carta->firmado)->toBeTrue()
        ->and($carta->fecha_firma)->not->toBeNull()
        ->and($carta->nom151)->toBe('constancia-test-123')
        ->and($carta->hash_nom151)->toBe('hash-test-abc');
});

test('firma sin nubarium cuando empresa no tiene firma habilitada', function (): void {
    $mockNubarium = Mockery::mock(NubariumFirmaService::class);
    $mockNubarium->shouldReceive('firmarCartaSua')
        ->once()
        ->andReturn([
            'success' => true,
            'nom151' => null,
            'hash' => null,
            'codigo_validacion' => null,
            'pdf_firmado' => null,
        ]);

    app()->instance(NubariumFirmaService::class, $mockNubarium);

    $carta = CartaSua::factory()->vista()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    Sanctum::actingAs($this->user);

    $this->postJson("/api/cartas-sua/{$carta->id}/firmar", [
        'imagen_firma' => 'base64-firma-test',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.firmado', true)
        ->assertJsonPath('data.tiene_nom151', false);

    $carta->refresh();
    expect($carta->firmado)->toBeTrue()
        ->and($carta->nom151)->toBeNull();
});

test('no se puede firmar carta ya firmada', function (): void {
    $carta = CartaSua::factory()->firmada()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    Sanctum::actingAs($this->user);

    $this->postJson("/api/cartas-sua/{$carta->id}/firmar", [
        'imagen_firma' => 'base64...',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false);
});

test('firma requiere imagen_firma', function (): void {
    $carta = CartaSua::factory()->vista()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    Sanctum::actingAs($this->user);

    $this->postJson("/api/cartas-sua/{$carta->id}/firmar", [])
        ->assertUnprocessable();
});

// =========================================================================
// Seguridad
// =========================================================================

test('usuario sin colaborador recibe 403', function (): void {
    $userSinColaborador = User::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    Sanctum::actingAs($userSinColaborador);

    $this->getJson('/api/cartas-sua')->assertForbidden();
    $this->getJson('/api/cartas-sua/resumen')->assertForbidden();
});

test('usuario no autenticado recibe 401', function (): void {
    $this->getJson('/api/cartas-sua')->assertUnauthorized();
    $this->getJson('/api/cartas-sua/resumen')->assertUnauthorized();
    $this->postJson('/api/cartas-sua/1/firmar')->assertUnauthorized();
});
