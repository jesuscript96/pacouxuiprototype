<?php

declare(strict_types=1);

use App\Models\CandidatoReclutamiento;
use App\Models\HistorialEstatusCandidato;
use App\Models\MensajeCandidato;
use App\Models\User;
use App\Services\CandidatoEstatusService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

describe('CandidatoEstatusService', function () {

    describe('cambiarEstatus', function () {
        it('cambia el estatus del candidato', function () {
            $candidato = CandidatoReclutamiento::factory()->create([
                'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
            ]);
            HistorialEstatusCandidato::factory()->create([
                'candidato_id' => $candidato->id,
                'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
                'fecha_inicio' => now(),
            ]);
            $usuario = User::factory()->create();

            app(CandidatoEstatusService::class)->cambiarEstatus(
                $candidato,
                CandidatoReclutamiento::ESTATUS_EN_PROCESO,
                $usuario,
                'Iniciando revisión',
            );

            expect($candidato->fresh()->estatus)->toBe(CandidatoReclutamiento::ESTATUS_EN_PROCESO);
        });

        it('cierra el estatus anterior con fecha_fin y duración', function () {
            $candidato = CandidatoReclutamiento::factory()->create([
                'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
            ]);
            $historialAnterior = HistorialEstatusCandidato::factory()->create([
                'candidato_id' => $candidato->id,
                'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
                'fecha_inicio' => now()->subDays(2),
                'fecha_fin' => null,
            ]);
            $usuario = User::factory()->create();

            app(CandidatoEstatusService::class)->cambiarEstatus(
                $candidato,
                CandidatoReclutamiento::ESTATUS_EN_PROCESO,
                $usuario,
                'Comentario',
            );

            $historialAnterior->refresh();
            expect($historialAnterior->fecha_fin)->not->toBeNull()
                ->and($historialAnterior->duracion)->not->toBeNull();
        });

        it('crea nuevo registro de historial', function () {
            $candidato = CandidatoReclutamiento::factory()->create();
            HistorialEstatusCandidato::factory()->create([
                'candidato_id' => $candidato->id,
                'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
            ]);
            $usuario = User::factory()->create();

            app(CandidatoEstatusService::class)->cambiarEstatus(
                $candidato,
                CandidatoReclutamiento::ESTATUS_EN_PROCESO,
                $usuario,
                'Comentario',
            );

            expect($candidato->historialEstatus()->count())->toBe(2);

            $ultimo = $candidato->historialEstatus()->latest('id')->first();
            expect($ultimo->estatus)->toBe(CandidatoReclutamiento::ESTATUS_EN_PROCESO)
                ->and($ultimo->creado_por)->toBe($usuario->id);
        });

        it('crea mensaje/comentario al cambiar estatus', function () {
            $candidato = CandidatoReclutamiento::factory()->create();
            HistorialEstatusCandidato::factory()->create(['candidato_id' => $candidato->id]);
            $usuario = User::factory()->create();

            app(CandidatoEstatusService::class)->cambiarEstatus(
                $candidato,
                CandidatoReclutamiento::ESTATUS_EN_PROCESO,
                $usuario,
                'Mi comentario',
            );

            $mensaje = $candidato->mensajes()->first();
            expect($mensaje)->not->toBeNull()
                ->and($mensaje->comentario)->toBe('Mi comentario')
                ->and($mensaje->user_id)->toBe($usuario->id);
        });

        it('lanza excepción si el estatus ya existe en historial (RN-05)', function () {
            $candidato = CandidatoReclutamiento::factory()->create([
                'estatus' => CandidatoReclutamiento::ESTATUS_EN_PROCESO,
            ]);
            HistorialEstatusCandidato::factory()->create([
                'candidato_id' => $candidato->id,
                'estatus' => CandidatoReclutamiento::ESTATUS_EN_PROCESO,
            ]);
            $usuario = User::factory()->create();

            expect(fn () => app(CandidatoEstatusService::class)->cambiarEstatus(
                $candidato,
                CandidatoReclutamiento::ESTATUS_EN_PROCESO,
                $usuario,
                'Comentario',
            ))->toThrow(InvalidArgumentException::class);
        });

        it('no modifica la base de datos si falla por RN-05', function () {
            $candidato = CandidatoReclutamiento::factory()->create([
                'estatus' => CandidatoReclutamiento::ESTATUS_EN_PROCESO,
            ]);
            HistorialEstatusCandidato::factory()->create([
                'candidato_id' => $candidato->id,
                'estatus' => CandidatoReclutamiento::ESTATUS_EN_PROCESO,
            ]);
            $usuario = User::factory()->create();

            try {
                app(CandidatoEstatusService::class)->cambiarEstatus(
                    $candidato,
                    CandidatoReclutamiento::ESTATUS_EN_PROCESO,
                    $usuario,
                    'Comentario',
                );
            } catch (InvalidArgumentException) {
            }

            expect($candidato->fresh()->estatus)->toBe(CandidatoReclutamiento::ESTATUS_EN_PROCESO)
                ->and($candidato->historialEstatus()->count())->toBe(1)
                ->and(MensajeCandidato::where('candidato_id', $candidato->id)->count())->toBe(0);
        });
    });

    describe('agregarComentario', function () {
        it('crea mensaje sin cambiar estatus', function () {
            $candidato = CandidatoReclutamiento::factory()->create([
                'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
            ]);
            $usuario = User::factory()->create();

            $mensaje = app(CandidatoEstatusService::class)->agregarComentario(
                $candidato,
                $usuario,
                'Solo un comentario',
            );

            expect($mensaje)->toBeInstanceOf(MensajeCandidato::class)
                ->and($mensaje->comentario)->toBe('Solo un comentario')
                ->and($candidato->fresh()->estatus)->toBe(CandidatoReclutamiento::ESTATUS_SIN_ATENDER);
        });
    });

});
