<?php

declare(strict_types=1);

use App\Filament\Cliente\Resources\Vacantes\VacanteResource;
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
        'ViewAny:Vacante',
        'View:Vacante',
        'Create:Vacante',
        'Update:Vacante',
        'Delete:Vacante',
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

describe('VacanteResource — permisos', function () {
    it('usuario con permiso puede ver listado', function () {
        $this->actingAs($this->user);

        expect(VacanteResource::canViewAny())->toBeTrue();
    });

    it('usuario sin permiso no puede ver listado', function () {
        $userSinPermiso = User::factory()->cliente()->create([
            'empresa_id' => $this->empresa->id,
        ]);
        $this->actingAs($userSinPermiso);

        expect(VacanteResource::canViewAny())->toBeFalse();
    });

    it('canCreate retorna true con permiso', function () {
        $this->actingAs($this->user);

        expect(VacanteResource::canCreate())->toBeTrue();
    });

    it('canDelete retorna true con permiso', function () {
        $this->actingAs($this->user);
        $vacante = Vacante::factory()->create(['empresa_id' => $this->empresa->id]);

        expect(VacanteResource::canDelete($vacante))->toBeTrue();
    });
});

describe('VacanteResource — listado', function () {
    it('usuario autenticado con permiso puede acceder al listado', function () {
        Vacante::factory()->create(['empresa_id' => $this->empresa->id]);

        $url = VacanteResource::getUrl(
            name: 'index',
            parameters: ['tenant' => $this->empresa],
            panel: 'cliente',
        );

        $this->actingAs($this->user)->get($url)->assertSuccessful();
    });
});

describe('VacanteResource — detalle', function () {
    it('usuario autenticado con permiso puede ver detalle', function () {
        $vacante = Vacante::factory()->create(['empresa_id' => $this->empresa->id]);

        $url = VacanteResource::getUrl(
            name: 'view',
            parameters: ['record' => $vacante, 'tenant' => $this->empresa],
            panel: 'cliente',
        );

        $this->actingAs($this->user)->get($url)->assertSuccessful();
    });
});

describe('VacanteResource — protección eliminación', function () {
    it('modelo no permite eliminar vacante con candidatos', function () {
        $vacante = Vacante::factory()->create(['empresa_id' => $this->empresa->id]);
        CandidatoReclutamiento::factory()->create(['vacante_id' => $vacante->id]);

        expect(fn () => $vacante->delete())
            ->toThrow(\Illuminate\Validation\ValidationException::class);
    });

    it('modelo permite eliminar vacante sin candidatos', function () {
        $vacante = Vacante::factory()->create(['empresa_id' => $this->empresa->id]);

        $vacante->delete();

        expect($vacante->trashed())->toBeTrue();
    });
});
