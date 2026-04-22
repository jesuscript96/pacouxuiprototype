<?php

declare(strict_types=1);

use App\Filament\Cliente\Resources\Candidatos\CandidatoReclutamientoResource;
use App\Models\CandidatoReclutamiento;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

require_once __DIR__.'/../../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    $this->empresa = crearEmpresaMinima();

    $permisos = [
        'ViewAny:CandidatoReclutamiento',
        'View:CandidatoReclutamiento',
        'Delete:CandidatoReclutamiento',
        'Update:CandidatoReclutamiento',
    ];
    foreach ($permisos as $nombre) {
        Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
    }

    $this->user = User::factory()->cliente()->create([
        'empresa_id' => $this->empresa->id,
    ]);
    $this->user->empresas()->attach($this->empresa->id);
    $this->user->givePermissionTo($permisos);
});

describe('CandidatoReclutamientoResource — permisos', function () {
    it('usuario con permiso puede ver listado', function () {
        $this->actingAs($this->user);

        expect(CandidatoReclutamientoResource::canViewAny())->toBeTrue();
    });

    it('usuario sin permiso no puede ver listado', function () {
        $userSinPermiso = User::factory()->cliente()->create([
            'empresa_id' => $this->empresa->id,
        ]);
        $this->actingAs($userSinPermiso);

        expect(CandidatoReclutamientoResource::canViewAny())->toBeFalse();
    });

    it('canCreate siempre retorna false', function () {
        $this->actingAs($this->user);

        expect(CandidatoReclutamientoResource::canCreate())->toBeFalse();
    });

    it('canEdit siempre retorna false', function () {
        $this->actingAs($this->user);
        $candidato = CandidatoReclutamiento::factory()->create();

        expect(CandidatoReclutamientoResource::canEdit($candidato))->toBeFalse();
    });

    it('canDelete retorna true con permiso', function () {
        $this->actingAs($this->user);
        $candidato = CandidatoReclutamiento::factory()->create();

        expect(CandidatoReclutamientoResource::canDelete($candidato))->toBeTrue();
    });

    it('canView retorna true con permiso', function () {
        $this->actingAs($this->user);
        $candidato = CandidatoReclutamiento::factory()->create();

        expect(CandidatoReclutamientoResource::canView($candidato))->toBeTrue();
    });
});

describe('CandidatoReclutamientoResource — listado', function () {
    it('usuario autenticado con permiso puede acceder al listado', function () {
        $vacante = Vacante::factory()->create(['empresa_id' => $this->empresa->id]);
        CandidatoReclutamiento::factory()->create(['vacante_id' => $vacante->id]);

        $url = CandidatoReclutamientoResource::getUrl(
            name: 'index',
            parameters: ['tenant' => $this->empresa],
            panel: 'cliente',
        );

        $this->actingAs($this->user)->get($url)->assertSuccessful();
    });
});

describe('CandidatoReclutamientoResource — detalle', function () {
    it('usuario autenticado con permiso puede ver detalle de candidato', function () {
        $vacante = Vacante::factory()->create(['empresa_id' => $this->empresa->id]);
        $candidato = CandidatoReclutamiento::factory()->create(['vacante_id' => $vacante->id]);

        $url = CandidatoReclutamientoResource::getUrl(
            name: 'view',
            parameters: ['record' => $candidato, 'tenant' => $this->empresa],
            panel: 'cliente',
        );

        $this->actingAs($this->user)->get($url)->assertSuccessful();
    });
});
