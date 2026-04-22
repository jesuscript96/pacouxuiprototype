<?php

declare(strict_types=1);

use App\Models\CampoFormularioVacante;
use App\Models\CandidatoReclutamiento;
use App\Models\Empresa;
use App\Models\Vacante;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

describe('Formulario Público de Postulación', function () {

    describe('GET /postular/{empresa}/{vacante}', function () {
        it('muestra el formulario para vacante válida', function () {
            $vacante = Vacante::factory()->create(['puesto' => 'Analista de Datos']);
            CampoFormularioVacante::factory()->requerido()->create([
                'vacante_id' => $vacante->id,
                'tipo' => 'text',
                'etiqueta' => 'Nombre completo',
                'nombre' => 'nombre',
            ]);

            $response = $this->get(route('postulacion.formulario', [
                'empresa' => $vacante->empresa_id,
                'vacante' => $vacante->slug,
            ]));

            $response->assertSuccessful();
            $response->assertSee('Analista de Datos');
            $response->assertSee('Nombre completo');
        });

        it('retorna 404 para vacante eliminada (soft-deleted)', function () {
            $vacante = Vacante::factory()->create();
            $empresaId = $vacante->empresa_id;
            $slug = $vacante->slug;
            $vacante->forceDelete();

            $response = $this->get(route('postulacion.formulario', [
                'empresa' => $empresaId,
                'vacante' => $slug,
            ]));

            $response->assertNotFound();
        });

        it('retorna 404 si vacante no pertenece a la empresa', function () {
            $vacante = Vacante::factory()->create();
            $otraEmpresa = Empresa::factory()->create();

            $response = $this->get(route('postulacion.formulario', [
                'empresa' => $otraEmpresa->id,
                'vacante' => $vacante->slug,
            ]));

            $response->assertNotFound();
        });
    });

    describe('POST /postular/{empresa}/{vacante}', function () {
        it('crea candidato con datos válidos', function () {
            $vacante = Vacante::factory()->create();
            CampoFormularioVacante::factory()->requerido()->create([
                'vacante_id' => $vacante->id,
                'tipo' => 'text',
                'nombre' => 'nombre',
                'etiqueta' => 'Nombre',
            ]);
            CampoFormularioVacante::factory()->requerido()->tipoEmail()->create([
                'vacante_id' => $vacante->id,
            ]);

            $response = $this->post(route('postulacion.enviar', [
                'empresa' => $vacante->empresa_id,
                'vacante' => $vacante->slug,
            ]), [
                'campos' => [
                    'nombre' => 'Juan Pérez',
                    'email' => 'juan@test.com',
                ],
            ]);

            $response->assertRedirect(route('postulacion.confirmacion'));
            expect(CandidatoReclutamiento::count())->toBe(1);

            $candidato = CandidatoReclutamiento::first();
            expect($candidato->vacante_id)->toBe($vacante->id)
                ->and($candidato->estatus)->toBe(CandidatoReclutamiento::ESTATUS_SIN_ATENDER);
        });

        it('valida campos requeridos', function () {
            $vacante = Vacante::factory()->create();
            CampoFormularioVacante::factory()->requerido()->create([
                'vacante_id' => $vacante->id,
                'tipo' => 'text',
                'nombre' => 'nombre',
                'etiqueta' => 'Nombre',
            ]);

            $response = $this->post(route('postulacion.enviar', [
                'empresa' => $vacante->empresa_id,
                'vacante' => $vacante->slug,
            ]), [
                'campos' => ['nombre' => ''],
            ]);

            $response->assertSessionHasErrors('campos.nombre');
            expect(CandidatoReclutamiento::count())->toBe(0);
        });

        it('valida formato de email', function () {
            $vacante = Vacante::factory()->create();
            CampoFormularioVacante::factory()->requerido()->tipoEmail()->create([
                'vacante_id' => $vacante->id,
            ]);

            $response = $this->post(route('postulacion.enviar', [
                'empresa' => $vacante->empresa_id,
                'vacante' => $vacante->slug,
            ]), [
                'campos' => ['email' => 'no-es-email'],
            ]);

            $response->assertSessionHasErrors('campos.email');
        });

        it('valida opciones de select', function () {
            $vacante = Vacante::factory()->create();
            CampoFormularioVacante::factory()->requerido()->tipoSelect()->create([
                'vacante_id' => $vacante->id,
                'nombre' => 'genero',
                'etiqueta' => 'Género',
                'opciones' => ['Masculino', 'Femenino', 'Otro'],
            ]);

            $response = $this->post(route('postulacion.enviar', [
                'empresa' => $vacante->empresa_id,
                'vacante' => $vacante->slug,
            ]), [
                'campos' => ['genero' => 'Invalido'],
            ]);

            $response->assertSessionHasErrors('campos.genero');
        });

        it('acepta campos opcionales vacíos', function () {
            $vacante = Vacante::factory()->create();
            CampoFormularioVacante::factory()->create([
                'vacante_id' => $vacante->id,
                'tipo' => 'text',
                'nombre' => 'notas',
                'etiqueta' => 'Notas',
                'requerido' => false,
            ]);

            $response = $this->post(route('postulacion.enviar', [
                'empresa' => $vacante->empresa_id,
                'vacante' => $vacante->slug,
            ]), [
                'campos' => ['notas' => ''],
            ]);

            $response->assertRedirect(route('postulacion.confirmacion'));
            expect(CandidatoReclutamiento::count())->toBe(1);
        });

        it('ignora campos tipo file en validación', function () {
            $vacante = Vacante::factory()->create();
            CampoFormularioVacante::factory()->tipoArchivo()->create([
                'vacante_id' => $vacante->id,
                'nombre' => 'cv',
                'etiqueta' => 'CV',
                'requerido' => true,
            ]);
            CampoFormularioVacante::factory()->create([
                'vacante_id' => $vacante->id,
                'tipo' => 'text',
                'nombre' => 'nombre',
                'etiqueta' => 'Nombre',
                'requerido' => false,
            ]);

            $response = $this->post(route('postulacion.enviar', [
                'empresa' => $vacante->empresa_id,
                'vacante' => $vacante->slug,
            ]), [
                'campos' => ['nombre' => 'Test'],
            ]);

            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('postulacion.confirmacion'));
        });

        it('valida longitud mínima y máxima de texto', function () {
            $vacante = Vacante::factory()->create();
            CampoFormularioVacante::factory()->requerido()->create([
                'vacante_id' => $vacante->id,
                'tipo' => 'text',
                'nombre' => 'nombre',
                'etiqueta' => 'Nombre',
                'longitud_minima' => 3,
                'longitud_maxima' => 50,
            ]);

            $response = $this->post(route('postulacion.enviar', [
                'empresa' => $vacante->empresa_id,
                'vacante' => $vacante->slug,
            ]), [
                'campos' => ['nombre' => 'Ab'],
            ]);

            $response->assertSessionHasErrors('campos.nombre');
        });
    });

    describe('GET /postular/confirmacion', function () {
        it('muestra página de confirmación después de envío exitoso', function () {
            $response = $this->withSession([
                'success' => true,
                'vacante' => 'Desarrollador',
                'empresa' => 'Tech Corp',
            ])->get(route('postulacion.confirmacion'));

            $response->assertSuccessful();
            $response->assertSee('Desarrollador');
            $response->assertSee('Tech Corp');
        });

        it('redirige si no viene de envío exitoso', function () {
            $response = $this->get(route('postulacion.confirmacion'));

            $response->assertRedirect('/');
        });
    });

});
