<?php

declare(strict_types=1);

use App\Models\CandidatoReclutamiento;
use App\Models\HistorialEstatusCandidato;
use App\Models\User;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

describe('HistorialEstatusCandidato Model', function () {

    describe('relaciones', function () {
        it('pertenece a un candidato', function () {
            $historial = HistorialEstatusCandidato::factory()->create();

            expect($historial->candidato)->toBeInstanceOf(CandidatoReclutamiento::class);
        });

        it('pertenece a un usuario creador', function () {
            $user = User::factory()->create();
            $historial = HistorialEstatusCandidato::factory()->creadoPor($user)->create();

            expect($historial->creadoPor)->toBeInstanceOf(User::class)
                ->and($historial->creadoPor->id)->toBe($user->id);
        });
    });

    describe('cerrarActual', function () {
        it('cierra el estatus abierto del candidato', function () {
            $candidato = CandidatoReclutamiento::factory()->create();
            $historial = HistorialEstatusCandidato::factory()->create([
                'candidato_id' => $candidato->id,
                'fecha_inicio' => now()->subDays(3),
                'fecha_fin' => null,
            ]);

            HistorialEstatusCandidato::cerrarActual($candidato);

            $historial->refresh();
            expect($historial->fecha_fin)->not->toBeNull()
                ->and($historial->duracion)->not->toBeNull();
        });

        it('no hace nada si no hay estatus abierto', function () {
            $candidato = CandidatoReclutamiento::factory()->create();
            HistorialEstatusCandidato::factory()->cerrado()->create([
                'candidato_id' => $candidato->id,
            ]);

            HistorialEstatusCandidato::cerrarActual($candidato);

            expect(HistorialEstatusCandidato::where('candidato_id', $candidato->id)->count())->toBe(1);
        });
    });

    describe('calcularDuracion', function () {
        it('calcula duración en días', function () {
            $inicio = now()->subDays(5);
            $fin = now();

            $duracion = HistorialEstatusCandidato::calcularDuracion($inicio, $fin);

            expect($duracion)->toBe('5 días');
        });

        it('calcula duración en meses y días', function () {
            $inicio = now()->subMonths(2)->subDays(10);
            $fin = now();

            $duracion = HistorialEstatusCandidato::calcularDuracion($inicio, $fin);

            expect($duracion)->toContain('2 meses')
                ->and($duracion)->toContain('días');
        });

        it('retorna menos de 1 hora para duraciones muy cortas', function () {
            $inicio = now();
            $fin = now()->addMinutes(30);

            $duracion = HistorialEstatusCandidato::calcularDuracion($inicio, $fin);

            expect($duracion)->toBe('menos de 1 hora');
        });

        it('calcula duración en horas', function () {
            $inicio = now()->subHours(5);
            $fin = now();

            $duracion = HistorialEstatusCandidato::calcularDuracion($inicio, $fin);

            expect($duracion)->toBe('5 horas');
        });
    });

    describe('casts', function () {
        it('castea fecha_inicio como datetime', function () {
            $historial = HistorialEstatusCandidato::factory()->create();

            expect($historial->fecha_inicio)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        });

        it('castea fecha_fin como datetime', function () {
            $historial = HistorialEstatusCandidato::factory()->cerrado()->create();

            expect($historial->fecha_fin)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        });
    });

});
