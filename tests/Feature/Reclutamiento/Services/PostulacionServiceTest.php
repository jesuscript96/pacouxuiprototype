<?php

declare(strict_types=1);

use App\Models\CandidatoReclutamiento;
use App\Models\Vacante;
use App\Services\PostulacionService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

describe('PostulacionService', function () {

    describe('crearCandidato', function () {
        it('crea candidato con estatus Sin atender', function () {
            $vacante = Vacante::factory()->create();

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, [
                'campos' => ['nombre' => 'Test', 'email' => 'test@test.com'],
            ]);

            expect($candidato)->toBeInstanceOf(CandidatoReclutamiento::class)
                ->and($candidato->estatus)->toBe(CandidatoReclutamiento::ESTATUS_SIN_ATENDER)
                ->and($candidato->vacante_id)->toBe($vacante->id);
        });

        it('guarda valores_formulario como JSON', function () {
            $vacante = Vacante::factory()->create();
            $campos = ['nombre' => 'Juan', 'email' => 'juan@test.com', 'telefono' => '5551234567'];

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, ['campos' => $campos]);

            expect($candidato->valores_formulario)->toBe($campos);
        });

        it('extrae CURP y lo guarda en mayúsculas', function () {
            $vacante = Vacante::factory()->create();

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, [
                'campos' => ['curp' => 'garc850101hdfrrl09'],
            ]);

            expect($candidato->curp)->toBe('GARC850101HDFRRL09');
        });

        it('extrae nombre completo de campos separados', function () {
            $vacante = Vacante::factory()->create();

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, [
                'campos' => [
                    'nombre' => 'María',
                    'apellido_paterno' => 'López',
                    'apellido_materno' => 'García',
                ],
            ]);

            expect($candidato->nombre_completo)->toBe('María López García');
        });

        it('extrae email de variantes de nombre de campo', function () {
            $vacante = Vacante::factory()->create();

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, [
                'campos' => ['correo_electronico' => 'test@example.com'],
            ]);

            expect($candidato->email)->toBe('test@example.com');
        });

        it('extrae teléfono de variantes de nombre de campo', function () {
            $vacante = Vacante::factory()->create();

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, [
                'campos' => ['celular' => '5551234567'],
            ]);

            expect($candidato->telefono)->toBe('5551234567');
        });

        it('crea primer registro de historial de estatus', function () {
            $vacante = Vacante::factory()->create();

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, ['campos' => []]);

            expect($candidato->historialEstatus()->count())->toBe(1);

            $historial = $candidato->historialEstatus()->first();
            expect($historial->estatus)->toBe(CandidatoReclutamiento::ESTATUS_SIN_ATENDER)
                ->and($historial->fecha_inicio)->not->toBeNull();
        });

        it('guarda archivos como array vacío por defecto', function () {
            $vacante = Vacante::factory()->create();

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, ['campos' => []]);

            expect($candidato->archivos)->toBe([]);
        });

        it('guarda archivos subidos y registra metadata', function () {
            \Illuminate\Support\Facades\Storage::fake('s3');
            config(['filesystems.archivos_disk' => 's3']);

            $vacante = Vacante::factory()->create();
            $archivo = \Illuminate\Http\UploadedFile::fake()->create('curriculum.pdf', 100);

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, [
                'campos' => ['nombre' => 'Test'],
                'archivos' => ['cv' => $archivo],
            ]);

            $candidato->refresh();
            $archivos = $candidato->archivos;

            expect($archivos)->toHaveKey('cv')
                ->and($archivos['cv']['nombre_original'])->toBe('curriculum.pdf')
                ->and($archivos['cv']['path'])->toContain('candidatos/')
                ->and($archivos['cv']['is_valid'])->toBeNull()
                ->and($archivos['cv']['uploaded_at'])->not->toBeNull();

            \Illuminate\Support\Facades\Storage::disk('s3')->assertExists($archivos['cv']['path']);
        });

        it('maneja campos vacíos gracefully', function () {
            $vacante = Vacante::factory()->create();

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, ['campos' => []]);

            expect($candidato->curp)->toBeNull()
                ->and($candidato->nombre_completo)->toBeNull()
                ->and($candidato->email)->toBeNull()
                ->and($candidato->telefono)->toBeNull();
        });

        it('usa nombre_completo directo si no hay campos separados', function () {
            $vacante = Vacante::factory()->create();

            $candidato = app(PostulacionService::class)->crearCandidato($vacante, [
                'campos' => ['nombre_completo' => 'Juan Pérez López'],
            ]);

            expect($candidato->nombre_completo)->toBe('Juan Pérez López');
        });
    });

});
