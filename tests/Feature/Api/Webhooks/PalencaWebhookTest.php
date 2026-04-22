<?php

declare(strict_types=1);

use App\Models\CandidatoReclutamiento;
use App\Models\HistorialLaboralCandidato;
use App\Services\Palenca\PalencaService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

test('procesa webhook verification completed', function (): void {
    $candidato = CandidatoReclutamiento::factory()->create([
        'curp' => 'GARJ900115HDFRRL09',
    ]);

    HistorialLaboralCandidato::create([
        'candidato_id' => $candidato->id,
        'curp' => 'GARJ900115HDFRRL09',
        'verification_id' => 'verif-webhook-123',
        'consent_id' => 'consent-abc',
        'account_status' => HistorialLaboralCandidato::STATUS_PENDING,
    ]);

    $mockPalenca = Mockery::mock(PalencaService::class);
    $mockPalenca->shouldReceive('estaConfigurado')->andReturn(true);
    $mockPalenca->shouldReceive('obtenerPerfil')->with('GARJ900115HDFRRL09')->andReturn([
        'success' => true,
        'nss' => '98765432101',
        'nombre_imss' => 'Juan García',
        'estatus_laboral' => 'EMPLEADO',
        'datos_completos' => ['personal_info' => ['nss' => '98765432101']],
    ]);
    $mockPalenca->shouldReceive('obtenerEmpleos')->with('GARJ900115HDFRRL09')->andReturn([
        'success' => true,
        'semanas_cotizadas' => 300,
        'empleos' => [['employer' => 'Test SA']],
    ]);

    app()->instance(PalencaService::class, $mockPalenca);

    $response = $this->postJson('/api/webhooks/palenca', [
        'event' => 'verification.completed',
        'identifier' => 'GARJ900115HDFRRL09',
        'verification_id' => 'verif-webhook-123',
        'status' => 'completed',
        'data_available' => true,
        'entities' => ['profile', 'employment'],
    ]);

    $response->assertOk()
        ->assertJsonPath('status', 'processed');

    $historial = HistorialLaboralCandidato::query()
        ->where('verification_id', 'verif-webhook-123')
        ->first();

    expect($historial->account_status)->toBe(HistorialLaboralCandidato::STATUS_COMPLETED)
        ->and($historial->semanas_cotizadas)->toBe(300)
        ->and($historial->nss)->toBe('98765432101');
});

test('rechaza webhook sin verification id', function (): void {
    $response = $this->postJson('/api/webhooks/palenca', [
        'event' => 'verification.completed',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('error', 'verification_id requerido');
});

test('acknowledges eventos no procesados', function (): void {
    $response = $this->postJson('/api/webhooks/palenca', [
        'event' => 'verification.pending',
        'verification_id' => 'verif-unknown',
    ]);

    $response->assertOk()
        ->assertJsonPath('status', 'acknowledged');
});

test('rechaza webhook con autenticacion invalida', function (): void {
    config([
        'services.palenca.webhook_user' => 'expected_user',
        'services.palenca.webhook_password' => 'expected_pass',
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Basic '.base64_encode('wrong:credentials'),
    ])->postJson('/api/webhooks/palenca', [
        'event' => 'verification.completed',
        'verification_id' => 'verif-123',
    ]);

    $response->assertStatus(401);
});

test('acepta webhook sin autenticacion si no hay credenciales configuradas', function (): void {
    config([
        'services.palenca.webhook_user' => '',
        'services.palenca.webhook_password' => '',
    ]);

    $candidato = CandidatoReclutamiento::factory()->create();

    HistorialLaboralCandidato::create([
        'candidato_id' => $candidato->id,
        'curp' => 'GARJ900115HDFRRL09',
        'verification_id' => 'verif-no-auth-123',
        'consent_id' => 'consent-xyz',
        'account_status' => HistorialLaboralCandidato::STATUS_PENDING,
    ]);

    $mockPalenca = Mockery::mock(PalencaService::class);
    $mockPalenca->shouldReceive('estaConfigurado')->andReturn(true);
    $mockPalenca->shouldReceive('obtenerPerfil')->andReturn([
        'success' => true,
        'nss' => '11111111111',
        'nombre_imss' => 'Test',
        'estatus_laboral' => null,
        'datos_completos' => null,
    ]);
    $mockPalenca->shouldReceive('obtenerEmpleos')->andReturn([
        'success' => true,
        'semanas_cotizadas' => 100,
        'empleos' => [],
    ]);

    app()->instance(PalencaService::class, $mockPalenca);

    $response = $this->postJson('/api/webhooks/palenca', [
        'event' => 'verification.completed',
        'identifier' => 'GARJ900115HDFRRL09',
        'verification_id' => 'verif-no-auth-123',
        'status' => 'completed',
        'data_available' => true,
        'entities' => ['profile', 'employment'],
    ]);

    $response->assertOk();
});
