<?php

declare(strict_types=1);

use App\Models\CandidatoReclutamiento;
use App\Models\Vacante;
use App\Services\ArchivoService;
use App\Services\OpenAI\OpenAIService;
use App\Services\ValidacionDocumental\DomicilioValidadorService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    $this->empresa = crearEmpresaMinima();
});

test('valida comprobante de domicilio correctamente', function (): void {
    $vacante = Vacante::factory()->paraEmpresa($this->empresa)->create();
    $candidato = CandidatoReclutamiento::factory()->paraVacante($vacante)->create();

    $mockOpenAI = Mockery::mock(OpenAIService::class);
    $mockOpenAI->shouldReceive('estaConfigurado')->andReturn(true);
    $mockOpenAI->shouldReceive('analizarImagen')->once()->andReturn([
        'success' => true,
        'content' => json_encode([
            'domicilio' => 'Av. Reforma 123, Col. Centro, CDMX',
            'issue_date' => now()->subDays(30)->format('d/m/Y'),
            'days_diff' => 30,
            'total_mount' => 450.00,
            'type_receipt_service' => 'CFE',
            'name_receipt_holder' => 'Juan García',
        ]),
    ]);

    $mockArchivo = Mockery::mock(ArchivoService::class);
    $mockArchivo->shouldReceive('url')
        ->once()
        ->andReturn('https://example.com/comprobante.jpg');

    app()->instance(OpenAIService::class, $mockOpenAI);
    app()->instance(ArchivoService::class, $mockArchivo);

    $service = app(DomicilioValidadorService::class);
    $resultado = $service->validar($candidato, 'comprobante_domicilio', 'companies/1/candidatos/1/comprobante.jpg');

    expect($resultado->isValid)->toBeTrue()
        ->and($resultado->dataIsValid)->toBeTrue()
        ->and($resultado->datosExtraidos)->toHaveKey('domicilio')
        ->and($resultado->datosExtraidos['domicilio'])->toBe('Av. Reforma 123, Col. Centro, CDMX')
        ->and($resultado->datosExtraidos['es_reciente'])->toBeTrue();
});

test('rechaza comprobante con mas de 90 dias de antiguedad', function (): void {
    $vacante = Vacante::factory()->paraEmpresa($this->empresa)->create();
    $candidato = CandidatoReclutamiento::factory()->paraVacante($vacante)->create();

    $mockOpenAI = Mockery::mock(OpenAIService::class);
    $mockOpenAI->shouldReceive('estaConfigurado')->andReturn(true);
    $mockOpenAI->shouldReceive('analizarImagen')->once()->andReturn([
        'success' => true,
        'content' => json_encode([
            'domicilio' => 'Calle Falsa 123',
            'issue_date' => now()->subDays(120)->format('d/m/Y'),
            'days_diff' => 120,
            'total_mount' => 300.00,
            'type_receipt_service' => 'Agua',
            'name_receipt_holder' => 'María López',
        ]),
    ]);

    $mockArchivo = Mockery::mock(ArchivoService::class);
    $mockArchivo->shouldReceive('url')
        ->once()
        ->andReturn('https://example.com/comprobante-viejo.jpg');

    app()->instance(OpenAIService::class, $mockOpenAI);
    app()->instance(ArchivoService::class, $mockArchivo);

    $service = app(DomicilioValidadorService::class);
    $resultado = $service->validar($candidato, 'comprobante_domicilio', 'companies/1/candidatos/1/comprobante.jpg');

    expect($resultado->isValid)->toBeTrue()
        ->and($resultado->dataIsValid)->toBeFalse()
        ->and($resultado->datosExtraidos['es_reciente'])->toBeFalse()
        ->and($resultado->datosExtraidos['dias_antiguedad'])->toBe(120);
});

test('retorna documento invalido si imagen no es comprobante', function (): void {
    $vacante = Vacante::factory()->paraEmpresa($this->empresa)->create();
    $candidato = CandidatoReclutamiento::factory()->paraVacante($vacante)->create();

    $mockOpenAI = Mockery::mock(OpenAIService::class);
    $mockOpenAI->shouldReceive('estaConfigurado')->andReturn(true);
    $mockOpenAI->shouldReceive('analizarImagen')->once()->andReturn([
        'success' => true,
        'content' => '{}',
    ]);

    $mockArchivo = Mockery::mock(ArchivoService::class);
    $mockArchivo->shouldReceive('url')
        ->once()
        ->andReturn('https://example.com/foto-random.jpg');

    app()->instance(OpenAIService::class, $mockOpenAI);
    app()->instance(ArchivoService::class, $mockArchivo);

    $service = app(DomicilioValidadorService::class);
    $resultado = $service->validar($candidato, 'comprobante_domicilio', 'companies/1/candidatos/1/foto.jpg');

    expect($resultado->isValid)->toBeTrue()
        ->and($resultado->dataIsValid)->toBeFalse();
});

test('retorna sin validar si openai no configurado', function (): void {
    $vacante = Vacante::factory()->paraEmpresa($this->empresa)->create();
    $candidato = CandidatoReclutamiento::factory()->paraVacante($vacante)->create();

    $mockOpenAI = Mockery::mock(OpenAIService::class);
    $mockOpenAI->shouldReceive('estaConfigurado')->andReturn(false);

    app()->instance(OpenAIService::class, $mockOpenAI);

    $service = app(DomicilioValidadorService::class);
    $resultado = $service->validar($candidato, 'comprobante_domicilio', 'companies/1/candidatos/1/comprobante.jpg');

    expect($resultado->isValid)->toBeFalse()
        ->and($resultado->error)->toBe('Validación no implementada');
});

test('retorna fallido si no se puede obtener url del archivo', function (): void {
    $vacante = Vacante::factory()->paraEmpresa($this->empresa)->create();
    $candidato = CandidatoReclutamiento::factory()->paraVacante($vacante)->create();

    $mockOpenAI = Mockery::mock(OpenAIService::class);
    $mockOpenAI->shouldReceive('estaConfigurado')->andReturn(true);

    $mockArchivo = Mockery::mock(ArchivoService::class);
    $mockArchivo->shouldReceive('url')
        ->once()
        ->andReturn('');

    app()->instance(OpenAIService::class, $mockOpenAI);
    app()->instance(ArchivoService::class, $mockArchivo);

    $service = app(DomicilioValidadorService::class);
    $resultado = $service->validar($candidato, 'comprobante_domicilio', 'ruta/inexistente.jpg');

    expect($resultado->isValid)->toBeFalse()
        ->and($resultado->error)->toContain('No se pudo obtener URL');
});

test('retorna fallido si openai devuelve error', function (): void {
    $vacante = Vacante::factory()->paraEmpresa($this->empresa)->create();
    $candidato = CandidatoReclutamiento::factory()->paraVacante($vacante)->create();

    $mockOpenAI = Mockery::mock(OpenAIService::class);
    $mockOpenAI->shouldReceive('estaConfigurado')->andReturn(true);
    $mockOpenAI->shouldReceive('analizarImagen')->once()->andReturn([
        'success' => false,
        'error' => 'Error de OpenAI: 500',
    ]);

    $mockArchivo = Mockery::mock(ArchivoService::class);
    $mockArchivo->shouldReceive('url')
        ->once()
        ->andReturn('https://example.com/comprobante.jpg');

    app()->instance(OpenAIService::class, $mockOpenAI);
    app()->instance(ArchivoService::class, $mockArchivo);

    $service = app(DomicilioValidadorService::class);
    $resultado = $service->validar($candidato, 'comprobante_domicilio', 'companies/1/candidatos/1/comprobante.jpg');

    expect($resultado->isValid)->toBeFalse()
        ->and($resultado->error)->toContain('500');
});
