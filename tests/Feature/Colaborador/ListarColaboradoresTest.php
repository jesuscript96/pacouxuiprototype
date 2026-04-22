<?php

/**
 * Listados equivalentes al recurso Colaboradores: consultan User con tipo colaborador (JSON + pivot).
 */
use App\Models\Departamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
});

function queryColaboradoresEmpresa(int $empresaId): \Illuminate\Database\Eloquent\Builder
{
    return User::query()->colaboradoresDeEmpresa($empresaId);
}

it('solo cuenta colaboradores (users) de la empresa solicitada', function (): void {
    $empresa1 = crearEmpresaMinima();
    $empresa2 = crearEmpresaMinima();
    $empresa2->update(['nombre' => 'Segunda Empresa']);
    crearUserColaborador($empresa1, [
        'name' => 'Colab',
        'apellido_paterno' => 'Uno',
        'apellido_materno' => 'Empresa1',
        'email' => 'c1@test.com',
    ]);
    crearUserColaborador($empresa2, [
        'name' => 'Colab',
        'apellido_paterno' => 'Dos',
        'apellido_materno' => 'Empresa2',
        'email' => 'c2@test.com',
    ]);

    $resultados = queryColaboradoresEmpresa($empresa1->id)->get();

    expect($resultados)->toHaveCount(1)
        ->and($resultados->first()->empresa_id)->toBe($empresa1->id)
        ->and($resultados->first()->email)->toBe('c1@test.com');
});

it('hay dos colaboradores users en total en el escenario de dos empresas', function (): void {
    $empresa1 = crearEmpresaMinima();
    $empresa2 = crearEmpresaMinima();
    $empresa2->update(['nombre' => 'Otra Empresa']);
    crearUserColaborador($empresa1, ['email' => 'a@test.com']);
    crearUserColaborador($empresa2, ['email' => 'b@test.com']);

    $total = User::query()->whereJsonContains('tipo', 'colaborador')->count();

    expect($total)->toBe(2);
});

it('filtra por departamento correctamente', function (): void {
    $empresa = crearEmpresaMinima();
    $dept1 = Departamento::create(['empresa_id' => $empresa->id, 'nombre' => 'Ventas']);
    $dept2 = Departamento::create(['empresa_id' => $empresa->id, 'nombre' => 'TI']);
    crearUserColaborador($empresa, [
        'departamento_id' => $dept1->id,
        'email' => 'v@test.com',
    ]);
    crearUserColaborador($empresa, [
        'departamento_id' => $dept2->id,
        'email' => 't@test.com',
    ]);

    $filtrados = queryColaboradoresEmpresa($empresa->id)
        ->whereHas('colaborador', fn ($q) => $q->where('departamento_id', $dept1->id))
        ->get();

    expect($filtrados)->toHaveCount(1)
        ->and($filtrados->first()->colaborador?->departamento_id)->toBe($dept1->id)
        ->and($filtrados->first()->email)->toBe('v@test.com');
});

it('búsqueda por nombre funciona', function (): void {
    $empresa = crearEmpresaMinima();
    crearUserColaborador($empresa, [
        'name' => 'Enrique',
        'apellido_paterno' => 'Búsqueda',
        'apellido_materno' => 'Test',
        'email' => 'enrique@test.com',
    ]);
    crearUserColaborador($empresa, [
        'name' => 'Otro',
        'apellido_paterno' => 'Nombre',
        'apellido_materno' => 'Distinto',
        'email' => 'otro@test.com',
    ]);

    $resultados = queryColaboradoresEmpresa($empresa->id)
        ->where(function ($q): void {
            $q->where('name', 'like', '%Enrique%')
                ->orWhere('apellido_paterno', 'like', '%Enrique%')
                ->orWhere('apellido_materno', 'like', '%Enrique%');
        })
        ->get();

    expect($resultados)->toHaveCount(1)
        ->and($resultados->first()->name)->toBe('Enrique');
});
