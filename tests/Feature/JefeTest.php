<?php

declare(strict_types=1);

use App\Models\Colaborador;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Jefe;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
});

it('expone codigo_boss como concatenación legacy sin separadores', function (): void {
    /** @var Empresa $empresa */
    $empresa = crearEmpresaMinima();
    $ubicacion = crearUbicacion($empresa);
    $departamento = Departamento::query()->create([
        'nombre' => 'Depto test jefe',
        'empresa_id' => $empresa->id,
    ]);
    $area = crearArea($empresa);
    $puesto = crearPuesto($empresa);

    $colaborador = Colaborador::factory()->create([
        'empresa_id' => $empresa->id,
        'ubicacion_id' => $ubicacion->id,
        'departamento_id' => $departamento->id,
        'area_id' => $area->id,
        'puesto_id' => $puesto->id,
    ]);

    $esperado = (string) $ubicacion->id
        .(string) $departamento->id
        .(string) $area->id
        .(string) $puesto->id;

    expect($colaborador->codigo_boss)->toBe($esperado);
});

it('relaciona colaborador con jefe y persiste códigos por nivel', function (): void {
    /** @var Empresa $empresa */
    $empresa = crearEmpresaMinima();
    $ubicacion = crearUbicacion($empresa);
    $departamento = Departamento::query()->create([
        'nombre' => 'Depto test jefe 2',
        'empresa_id' => $empresa->id,
    ]);
    $area = crearArea($empresa);
    $puesto = crearPuesto($empresa);

    $colaborador = Colaborador::factory()->create([
        'empresa_id' => $empresa->id,
        'ubicacion_id' => $ubicacion->id,
        'departamento_id' => $departamento->id,
        'area_id' => $area->id,
        'puesto_id' => $puesto->id,
    ]);

    $jefe = Jefe::factory()->forColaborador($colaborador)->create([
        'codigo_nivel_2' => 'n2',
        'codigo_nivel_3' => 'n3',
        'codigo_nivel_4' => 'n4',
    ]);

    $colaborador->load('jefe');

    expect($colaborador->jefe)->not->toBeNull()
        ->and($colaborador->jefe->id)->toBe($jefe->id)
        ->and($jefe->codigo_nivel_1)->toBe($colaborador->codigo_boss)
        ->and($jefe->codigo_nivel_4)->toBe('n4');
});
