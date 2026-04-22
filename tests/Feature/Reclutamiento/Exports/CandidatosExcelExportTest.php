<?php

declare(strict_types=1);

use App\Exports\Reclutamiento\CandidatosVacanteExport;
use App\Models\CampoFormularioVacante;
use App\Models\CandidatoReclutamiento;
use App\Models\Vacante;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

describe('CandidatosVacanteExport', function () {

    it('genera export sin errores', function () {
        $vacante = Vacante::factory()->create();
        CampoFormularioVacante::factory()->create([
            'vacante_id' => $vacante->id,
            'tipo' => 'text',
            'etiqueta' => 'Nombre',
            'nombre' => 'nombre',
            'orden' => 1,
        ]);
        CandidatoReclutamiento::factory()->count(3)->create([
            'vacante_id' => $vacante->id,
        ]);

        Excel::fake();

        Excel::download(
            new CandidatosVacanteExport($vacante),
            'test.xlsx',
        );

        Excel::assertDownloaded('test.xlsx');
    });

    it('incluye solo candidatos de la vacante seleccionada', function () {
        $vacante1 = Vacante::factory()->create();
        $vacante2 = Vacante::factory()->create();
        CandidatoReclutamiento::factory()->count(2)->create(['vacante_id' => $vacante1->id]);
        CandidatoReclutamiento::factory()->count(3)->create(['vacante_id' => $vacante2->id]);

        $export = new CandidatosVacanteExport($vacante1);
        $sheets = $export->sheets();
        $collection = $sheets[0]->collection();

        expect($collection)->toHaveCount(2);
    });

    it('filtra por estatus cuando se proporciona', function () {
        $vacante = Vacante::factory()->create();
        CandidatoReclutamiento::factory()->count(2)->create([
            'vacante_id' => $vacante->id,
            'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
        ]);
        CandidatoReclutamiento::factory()->enProceso()->create([
            'vacante_id' => $vacante->id,
        ]);

        $export = new CandidatosVacanteExport($vacante, CandidatoReclutamiento::ESTATUS_SIN_ATENDER);
        $sheets = $export->sheets();
        $collection = $sheets[0]->collection();

        expect($collection)->toHaveCount(2);
    });

    it('genera cabeceras dinámicas basadas en campos del formulario', function () {
        $vacante = Vacante::factory()->create();
        CampoFormularioVacante::factory()->create([
            'vacante_id' => $vacante->id,
            'tipo' => 'text',
            'etiqueta' => 'Nombre Completo',
            'nombre' => 'nombre',
            'orden' => 1,
        ]);
        CampoFormularioVacante::factory()->tipoEmail()->create([
            'vacante_id' => $vacante->id,
            'orden' => 2,
        ]);

        $export = new CandidatosVacanteExport($vacante);
        $sheets = $export->sheets();
        $headings = $sheets[0]->headings();

        expect($headings)->toContain('#')
            ->and($headings)->toContain('Fecha de postulación')
            ->and($headings)->toContain('Estatus')
            ->and($headings)->toContain('Nombre Completo')
            ->and($headings)->toContain('Correo electrónico')
            ->and($headings)->toContain('CURP')
            ->and($headings)->toContain('Evaluación CV');
    });

    it('excluye campos tipo file de las cabeceras', function () {
        $vacante = Vacante::factory()->create();
        CampoFormularioVacante::factory()->create([
            'vacante_id' => $vacante->id,
            'tipo' => 'text',
            'etiqueta' => 'Nombre',
            'nombre' => 'nombre',
            'orden' => 1,
        ]);
        CampoFormularioVacante::factory()->tipoArchivo()->create([
            'vacante_id' => $vacante->id,
            'etiqueta' => 'CV',
            'nombre' => 'cv',
            'orden' => 2,
        ]);

        $export = new CandidatosVacanteExport($vacante);
        $sheets = $export->sheets();
        $headings = $sheets[0]->headings();

        expect($headings)->toContain('Nombre')
            ->and($headings)->not->toContain('CV');
    });

    it('mapea valores dinámicos del formulario correctamente', function () {
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
            'curp' => 'GARC850101HDFRRL09',
        ]);

        $export = new CandidatosVacanteExport($vacante);
        $sheets = $export->sheets();
        $mapped = $sheets[0]->map($candidato);

        expect($mapped)->toContain('Juan Pérez')
            ->and($mapped)->toContain('GARC850101HDFRRL09');
    });

    it('retorna todos los candidatos cuando no se filtra por estatus', function () {
        $vacante = Vacante::factory()->create();
        CandidatoReclutamiento::factory()->create([
            'vacante_id' => $vacante->id,
            'estatus' => CandidatoReclutamiento::ESTATUS_SIN_ATENDER,
        ]);
        CandidatoReclutamiento::factory()->enProceso()->create([
            'vacante_id' => $vacante->id,
        ]);
        CandidatoReclutamiento::factory()->contratado()->create([
            'vacante_id' => $vacante->id,
        ]);

        $export = new CandidatosVacanteExport($vacante);
        $sheets = $export->sheets();
        $collection = $sheets[0]->collection();

        expect($collection)->toHaveCount(3);
    });

});
