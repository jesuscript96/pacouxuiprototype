<?php

declare(strict_types=1);

use App\Models\Colaborador;
use App\Models\Departamento;
use App\Models\Jefe;
use App\Models\User;
use App\Services\TipoSolicitudAutorizacionOpciones;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

it('lista por nombre solo usuarios con colaborador área, puesto y fila en jefes', function (): void {
    $empresa = crearEmpresaMinima();
    $eid = (int) $empresa->id;
    $ubicacion = crearUbicacion($empresa);
    $departamento = Departamento::query()->create(['nombre' => 'RH', 'empresa_id' => $eid]);
    $area = crearArea($empresa, ['nombre' => 'Operaciones']);
    $puesto = crearPuesto($empresa, ['nombre' => 'Director']);

    $emailCompleto = fake()->unique()->safeEmail();
    $userCompleto = User::factory()->create([
        'name' => 'Ana',
        'apellido_paterno' => 'García',
        'empresa_id' => $eid,
        'email' => $emailCompleto,
    ]);
    $colCompleto = Colaborador::factory()->create([
        'empresa_id' => $eid,
        'email' => $emailCompleto,
        'ubicacion_id' => $ubicacion->id,
        'departamento_id' => $departamento->id,
        'area_id' => $area->id,
        'puesto_id' => $puesto->id,
    ]);
    $userCompleto->update(['colaborador_id' => $colCompleto->id]);

    Jefe::query()->create([
        'colaborador_id' => $colCompleto->id,
        'codigo_nivel_1' => $colCompleto->codigo_boss !== '' ? $colCompleto->codigo_boss : '1',
        'codigo_nivel_2' => null,
        'codigo_nivel_3' => null,
        'codigo_nivel_4' => null,
    ]);

    $emailSinJefe = fake()->unique()->safeEmail();
    $userSinJefe = User::factory()->create([
        'empresa_id' => $eid,
        'email' => $emailSinJefe,
    ]);
    $colSinJefe = Colaborador::factory()->create([
        'empresa_id' => $eid,
        'email' => $emailSinJefe,
        'ubicacion_id' => $ubicacion->id,
        'departamento_id' => $departamento->id,
        'area_id' => $area->id,
        'puesto_id' => $puesto->id,
    ]);
    $userSinJefe->update(['colaborador_id' => $colSinJefe->id]);

    $emailSinArea = fake()->unique()->safeEmail();
    $userSinArea = User::factory()->create([
        'empresa_id' => $eid,
        'email' => $emailSinArea,
    ]);
    $colSinArea = Colaborador::factory()->create([
        'empresa_id' => $eid,
        'email' => $emailSinArea,
        'area_id' => null,
        'puesto_id' => null,
    ]);
    $userSinArea->update(['colaborador_id' => $colSinArea->id]);

    $opts = TipoSolicitudAutorizacionOpciones::opcionesAutorizadoresPorNombre($eid);

    expect($opts)->toHaveKey($userCompleto->id)
        ->and($opts[$userCompleto->id])->toContain('Operaciones')
        ->and($opts[$userCompleto->id])->toContain('Director')
        ->not->toHaveKey($userSinJefe->id)
        ->not->toHaveKey($userSinArea->id);
});

it('lista niveles de jerarquía según códigos en jefes', function (): void {
    $empresa = crearEmpresaMinima();
    $eid = (int) $empresa->id;
    $ubicacion = crearUbicacion($empresa);
    $departamento = Departamento::query()->create(['nombre' => 'RH', 'empresa_id' => $eid]);
    $area = crearArea($empresa);
    $puesto = crearPuesto($empresa);

    $col = Colaborador::factory()->create([
        'empresa_id' => $eid,
        'ubicacion_id' => $ubicacion->id,
        'departamento_id' => $departamento->id,
        'area_id' => $area->id,
        'puesto_id' => $puesto->id,
    ]);

    Jefe::query()->create([
        'colaborador_id' => $col->id,
        'codigo_nivel_1' => $col->codigo_boss,
        'codigo_nivel_2' => null,
        'codigo_nivel_3' => null,
        'codigo_nivel_4' => null,
    ]);

    $opts = TipoSolicitudAutorizacionOpciones::opcionesNivelesJerarquia($eid);

    expect($opts)->toHaveKey('1')
        ->not->toHaveKey('2');
});
