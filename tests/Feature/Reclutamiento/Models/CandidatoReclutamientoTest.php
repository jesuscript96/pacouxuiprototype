<?php

declare(strict_types=1);

use App\Models\CandidatoReclutamiento;
use App\Models\HistorialEstatusCandidato;
use App\Models\MensajeCandidato;
use App\Models\Vacante;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

describe('CandidatoReclutamiento Model', function () {

    describe('relaciones', function () {
        it('pertenece a una vacante', function () {
            $candidato = CandidatoReclutamiento::factory()->create();

            expect($candidato->vacante)->toBeInstanceOf(Vacante::class);
        });

        it('tiene muchos registros de historial de estatus', function () {
            $candidato = CandidatoReclutamiento::factory()->create();
            HistorialEstatusCandidato::factory()->count(3)->create(['candidato_id' => $candidato->id]);

            expect($candidato->historialEstatus)->toHaveCount(3);
        });

        it('tiene muchos mensajes', function () {
            $candidato = CandidatoReclutamiento::factory()->create();
            MensajeCandidato::factory()->count(2)->create(['candidato_id' => $candidato->id]);

            expect($candidato->mensajes)->toHaveCount(2);
        });
    });

    describe('casts', function () {
        it('castea valores_formulario como array', function () {
            $candidato = CandidatoReclutamiento::factory()->create([
                'valores_formulario' => ['nombre' => 'Juan', 'email' => 'juan@test.com'],
            ]);

            expect($candidato->valores_formulario)->toBeArray()
                ->and($candidato->valores_formulario['nombre'])->toBe('Juan');
        });

        it('castea archivos como array', function () {
            $candidato = CandidatoReclutamiento::factory()->create([
                'archivos' => ['cv' => ['path' => 'candidatos/1/cv.pdf']],
            ]);

            expect($candidato->archivos)->toBeArray()
                ->and($candidato->archivos)->toHaveKey('cv');
        });
    });

    describe('tieneEstatus', function () {
        it('retorna true si el estatus existe en historial', function () {
            $candidato = CandidatoReclutamiento::factory()->create();
            HistorialEstatusCandidato::factory()->create([
                'candidato_id' => $candidato->id,
                'estatus' => CandidatoReclutamiento::ESTATUS_EN_PROCESO,
            ]);

            expect($candidato->tieneEstatus(CandidatoReclutamiento::ESTATUS_EN_PROCESO))->toBeTrue();
        });

        it('retorna false si el estatus no existe en historial', function () {
            $candidato = CandidatoReclutamiento::factory()->create();

            expect($candidato->tieneEstatus(CandidatoReclutamiento::ESTATUS_CONTRATADO))->toBeFalse();
        });
    });

    describe('colorEstatus', function () {
        it('retorna el color correcto para cada estatus', function () {
            $candidato = CandidatoReclutamiento::factory()->create(['estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER]);
            expect($candidato->colorEstatus())->toBe('gray');

            $candidato->estatus = CandidatoReclutamiento::ESTATUS_EN_PROCESO;
            expect($candidato->colorEstatus())->toBe('warning');

            $candidato->estatus = CandidatoReclutamiento::ESTATUS_CONTRATADO;
            expect($candidato->colorEstatus())->toBe('success');

            $candidato->estatus = CandidatoReclutamiento::ESTATUS_RECHAZADO;
            expect($candidato->colorEstatus())->toBe('danger');

            $candidato->estatus = CandidatoReclutamiento::ESTATUS_NO_SE_PRESENTO;
            expect($candidato->colorEstatus())->toBe('info');
        });
    });

    describe('estatusDisponibles', function () {
        it('retorna todos los estatus definidos', function () {
            $disponibles = CandidatoReclutamiento::estatusDisponibles();

            expect($disponibles)->toContain('Sin atender')
                ->and($disponibles)->toContain('En proceso')
                ->and($disponibles)->toContain('Contratado')
                ->and($disponibles)->toContain('Rechazado')
                ->and($disponibles)->toContain('No se presentó')
                ->and($disponibles)->toHaveCount(5);
        });
    });

    describe('estatusActual', function () {
        it('retorna el historial sin fecha_fin', function () {
            $candidato = CandidatoReclutamiento::factory()->create();
            HistorialEstatusCandidato::factory()->cerrado()->create([
                'candidato_id' => $candidato->id,
                'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
            ]);
            $actual = HistorialEstatusCandidato::factory()->create([
                'candidato_id' => $candidato->id,
                'estatus' => CandidatoReclutamiento::ESTATUS_EN_PROCESO,
                'fecha_fin' => null,
            ]);

            expect($candidato->estatusActual()->id)->toBe($actual->id);
        });

        it('retorna null si no hay historial abierto', function () {
            $candidato = CandidatoReclutamiento::factory()->create();
            HistorialEstatusCandidato::factory()->cerrado()->create([
                'candidato_id' => $candidato->id,
            ]);

            expect($candidato->estatusActual())->toBeNull();
        });
    });

});
