<?php

declare(strict_types=1);

use App\Models\CampoFormularioVacante;
use App\Models\CandidatoReclutamiento;
use App\Models\HistorialEstatusCandidato;
use App\Models\MensajeCandidato;
use App\Models\Vacante;
use App\Services\CandidatoPdfService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

describe('CandidatoPdfService', function () {

    it('genera reporte PDF sin errores', function () {
        $vacante = Vacante::factory()->create();
        CampoFormularioVacante::factory()->create([
            'vacante_id' => $vacante->id,
            'tipo' => 'text',
            'etiqueta' => 'Nombre',
            'nombre' => 'nombre',
            'orden' => 1,
        ]);
        $candidato = CandidatoReclutamiento::factory()->create([
            'vacante_id' => $vacante->id,
            'valores_formulario' => ['nombre' => 'Juan Pérez'],
        ]);
        HistorialEstatusCandidato::factory()->create([
            'candidato_id' => $candidato->id,
        ]);

        $response = app(CandidatoPdfService::class)->generarReporte($candidato);

        expect($response->getStatusCode())->toBe(200);
        expect($response->headers->get('content-type'))->toContain('pdf');
    });

    it('incluye nombre del candidato en el nombre de archivo', function () {
        $candidato = CandidatoReclutamiento::factory()->create([
            'nombre_completo' => 'Test Candidato',
        ]);

        $response = app(CandidatoPdfService::class)->generarReporte($candidato);

        $contentDisposition = $response->headers->get('content-disposition');
        expect($contentDisposition)->toContain('candidato-'.$candidato->id);
    });

    it('genera PDF con candidato sin historial ni mensajes', function () {
        $candidato = CandidatoReclutamiento::factory()->create();

        $response = app(CandidatoPdfService::class)->generarReporte($candidato);

        expect($response->getStatusCode())->toBe(200);
    });

    it('genera PDF con historial de estatus completo', function () {
        $candidato = CandidatoReclutamiento::factory()->create();
        HistorialEstatusCandidato::factory()->cerrado()->create([
            'candidato_id' => $candidato->id,
            'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
        ]);
        HistorialEstatusCandidato::factory()->create([
            'candidato_id' => $candidato->id,
            'estatus' => CandidatoReclutamiento::ESTATUS_EN_PROCESO,
        ]);

        $response = app(CandidatoPdfService::class)->generarReporte($candidato);

        expect($response->getStatusCode())->toBe(200);
    });

    it('genera PDF con mensajes/comentarios', function () {
        $candidato = CandidatoReclutamiento::factory()->create();
        MensajeCandidato::factory()->count(3)->create([
            'candidato_id' => $candidato->id,
        ]);

        $response = app(CandidatoPdfService::class)->generarReporte($candidato);

        expect($response->getStatusCode())->toBe(200);
    });

    it('genera PDF con candidato sin CURP ni evaluación', function () {
        $candidato = CandidatoReclutamiento::factory()->sinCurp()->create([
            'evaluacion_cv' => null,
        ]);

        $response = app(CandidatoPdfService::class)->generarReporte($candidato);

        expect($response->getStatusCode())->toBe(200);
    });

});
