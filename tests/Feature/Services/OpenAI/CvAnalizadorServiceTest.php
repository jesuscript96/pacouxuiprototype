<?php

declare(strict_types=1);

use App\Models\CandidatoReclutamiento;
use App\Models\Vacante;
use App\Services\OpenAI\OpenAIService;
use App\Services\ValidacionDocumental\CvAnalizadorService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    $this->empresa = crearEmpresaMinima();
});

test('analiza cv correctamente con openai mock', function (): void {
    $vacante = Vacante::factory()->paraEmpresa($this->empresa)->create();
    $candidato = CandidatoReclutamiento::factory()->paraVacante($vacante)->create([
        'nombre_completo' => 'Juan Pérez García',
        'email' => 'juan@test.com',
    ]);

    $mockOpenAI = Mockery::mock(OpenAIService::class);
    $mockOpenAI->shouldReceive('estaConfigurado')->andReturn(true);
    $mockOpenAI->shouldReceive('chat')->once()->andReturn([
        'success' => true,
        'content' => json_encode([
            'profile_summary' => 'Candidato con buen perfil técnico',
            'strengths' => ['Experiencia en PHP'],
            'weaknesses' => ['Sin liderazgo'],
            'hard_skills' => ['PHP', 'Laravel'],
            'soft_skills' => ['Comunicación'],
            'highlight_experience' => [],
            'education' => [],
            'languages' => ['Español'],
            'recommendations' => [],
            'job_comparison' => [
                'requirements' => [],
                'score' => 8,
                'compatibility' => 'Alto',
            ],
        ]),
    ]);

    app()->instance(OpenAIService::class, $mockOpenAI);

    $service = app(CvAnalizadorService::class);
    $resultado = $service->validar($candidato, 'cv', '/fake/path.pdf');

    expect($resultado->isValid)->toBeTrue()
        ->and($resultado->score)->toBe(8.0)
        ->and($resultado->datosExtraidos)->toHaveKey('profile_summary')
        ->and($resultado->datosExtraidos['job_comparison']['compatibility'])->toBe('Alto');
});

test('retorna sin validar si openai no configurado', function (): void {
    $candidato = CandidatoReclutamiento::factory()->create();

    $mockOpenAI = Mockery::mock(OpenAIService::class);
    $mockOpenAI->shouldReceive('estaConfigurado')->andReturn(false);

    app()->instance(OpenAIService::class, $mockOpenAI);

    $service = app(CvAnalizadorService::class);
    $resultado = $service->validar($candidato, 'cv', '/fake/path.pdf');

    expect($resultado->isValid)->toBeFalse()
        ->and($resultado->error)->toContain('no implementada');
});

test('retorna fallido si openai devuelve error', function (): void {
    $candidato = CandidatoReclutamiento::factory()->create();

    $mockOpenAI = Mockery::mock(OpenAIService::class);
    $mockOpenAI->shouldReceive('estaConfigurado')->andReturn(true);
    $mockOpenAI->shouldReceive('chat')->once()->andReturn([
        'success' => false,
        'error' => 'Error de OpenAI: 429',
    ]);

    app()->instance(OpenAIService::class, $mockOpenAI);

    $service = app(CvAnalizadorService::class);
    $resultado = $service->validar($candidato, 'cv', '/fake/path.pdf');

    expect($resultado->isValid)->toBeFalse()
        ->and($resultado->error)->toContain('429');
});

test('retorna fallido si respuesta no es json valido', function (): void {
    $candidato = CandidatoReclutamiento::factory()->create();

    $mockOpenAI = Mockery::mock(OpenAIService::class);
    $mockOpenAI->shouldReceive('estaConfigurado')->andReturn(true);
    $mockOpenAI->shouldReceive('chat')->once()->andReturn([
        'success' => true,
        'content' => 'Esto no es JSON válido',
    ]);

    app()->instance(OpenAIService::class, $mockOpenAI);

    $service = app(CvAnalizadorService::class);
    $resultado = $service->validar($candidato, 'cv', '/fake/path.pdf');

    expect($resultado->isValid)->toBeFalse()
        ->and($resultado->error)->toContain('parsear');
});
