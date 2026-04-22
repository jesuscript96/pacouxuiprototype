<?php

declare(strict_types=1);

use App\Models\CampoFormularioVacante;
use App\Models\CandidatoReclutamiento;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

describe('Vacante Model', function () {

    describe('relaciones', function () {
        it('pertenece a una empresa', function () {
            $vacante = Vacante::factory()->create();

            expect($vacante->empresa)->toBeInstanceOf(Empresa::class);
        });

        it('pertenece a un creador (user)', function () {
            $user = User::factory()->create();
            $vacante = Vacante::factory()->create(['creado_por' => $user->id]);

            expect($vacante->creador)->toBeInstanceOf(User::class)
                ->and($vacante->creador->id)->toBe($user->id);
        });

        it('tiene muchos campos de formulario', function () {
            $vacante = Vacante::factory()->create();
            CampoFormularioVacante::factory()->count(3)->create(['vacante_id' => $vacante->id]);

            expect($vacante->camposFormulario)->toHaveCount(3);
        });

        it('tiene muchos candidatos', function () {
            $vacante = Vacante::factory()->create();
            CandidatoReclutamiento::factory()->count(2)->create(['vacante_id' => $vacante->id]);

            expect($vacante->candidatos)->toHaveCount(2);
        });
    });

    describe('slug', function () {
        it('genera slug automáticamente al crear', function () {
            $vacante = Vacante::factory()->create(['puesto' => 'Desarrollador Full Stack']);

            expect($vacante->slug)->toBe('desarrollador-full-stack');
        });

        it('genera slug único por empresa', function () {
            $empresa = Empresa::factory()->create();

            $vacante1 = Vacante::factory()->create([
                'empresa_id' => $empresa->id,
                'puesto' => 'Contador',
            ]);
            $vacante2 = Vacante::factory()->create([
                'empresa_id' => $empresa->id,
                'puesto' => 'Contador',
            ]);

            expect($vacante1->slug)->toBe('contador')
                ->and($vacante2->slug)->toBe('contador-1');
        });

        it('actualiza slug cuando cambia el puesto', function () {
            $vacante = Vacante::factory()->create(['puesto' => 'Analista']);

            $vacante->update(['puesto' => 'Analista Senior']);

            expect($vacante->fresh()->slug)->toBe('analista-senior');
        });
    });

    describe('tieneRegistrosAsociados', function () {
        it('retorna false si no tiene candidatos', function () {
            $vacante = Vacante::factory()->create();

            expect($vacante->tieneRegistrosAsociados())->toBeFalse();
        });

        it('retorna true si tiene candidatos', function () {
            $vacante = Vacante::factory()->create();
            CandidatoReclutamiento::factory()->create(['vacante_id' => $vacante->id]);

            expect($vacante->tieneRegistrosAsociados())->toBeTrue();
        });

        it('retorna true incluso con candidatos soft-deleted', function () {
            $vacante = Vacante::factory()->create();
            $candidato = CandidatoReclutamiento::factory()->create(['vacante_id' => $vacante->id]);
            $candidato->delete();

            expect($vacante->tieneRegistrosAsociados())->toBeTrue();
        });
    });

    describe('urlPublica', function () {
        it('genera URL con slug de la vacante', function () {
            $vacante = Vacante::factory()->create(['puesto' => 'Ingeniero QA']);

            $url = $vacante->urlPublica();

            expect($url)->toContain('/postular/')
                ->and($url)->toContain('ingeniero-qa');
        });
    });

    describe('protección de eliminación', function () {
        it('permite soft-delete de vacante sin candidatos', function () {
            $vacante = Vacante::factory()->create();

            $vacante->delete();

            expect($vacante->trashed())->toBeTrue();
        });

        it('lanza ValidationException al eliminar vacante con candidatos', function () {
            $vacante = Vacante::factory()->create();
            CandidatoReclutamiento::factory()->create(['vacante_id' => $vacante->id]);

            expect(fn () => $vacante->delete())
                ->toThrow(ValidationException::class, 'No se puede eliminar porque tiene candidatos asociados.');
        });
    });

});
