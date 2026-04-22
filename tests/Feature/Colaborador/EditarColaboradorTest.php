<?php

/**
 * Actualización de colaboradores: User + ColaboradorService::actualizarColaborador.
 */
use App\Models\Departamento;
use App\Services\ColaboradorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    $this->service = app(ColaboradorService::class);
});

it('actualiza datos básicos del colaborador', function (): void {
    $colaborador = crearUserColaborador($this->empresa, [
        'name' => 'Juan',
        'apellido_paterno' => 'Pérez',
        'apellido_materno' => 'García',
        'email' => 'juan@test.com',
        'fecha_nacimiento' => '1990-01-01',
        'fecha_ingreso' => '2024-01-01',
        'periodicidad_pago' => 'QUINCENAL',
    ]);

    $this->service->actualizarColaborador($colaborador->refresh(), [
        'name' => 'Juan Carlos',
        'apellido_paterno' => 'Pérez',
        'apellido_materno' => 'López',
    ]);

    $colaborador->refresh();
    expect($colaborador->name)->toBe('Juan Carlos')
        ->and($colaborador->apellido_materno)->toBe('López');
});

it('genera historial al cambiar departamento', function (): void {
    $dept1 = Departamento::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Ventas']);
    $dept2 = Departamento::create(['empresa_id' => $this->empresa->id, 'nombre' => 'TI']);
    $colaborador = crearUserColaborador($this->empresa, [
        'departamento_id' => $dept1->id,
        'email' => 'colab.dep@test.com',
    ]);
    expect($colaborador->refresh()->historialDepartamentos()->count())->toBe(1);

    $this->service->actualizarColaborador($colaborador->refresh(), [
        'departamento_id' => $dept2->id,
    ]);

    $colaborador->refresh()->load('colaborador');
    expect($colaborador->historialDepartamentos()->count())->toBe(2)
        ->and($colaborador->colaborador->departamento_id)->toBe($dept2->id)
        ->and($colaborador->historialDepartamentos()->whereNull('fecha_fin')->first()->departamento_id)->toBe($dept2->id);
});

it('genera historial al cambiar ubicación', function (): void {
    $ubic1 = crearUbicacion($this->empresa, ['nombre' => 'Oficina A']);
    $ubic2 = crearUbicacion($this->empresa, ['nombre' => 'Oficina B']);
    $colaborador = crearUserColaborador($this->empresa, [
        'ubicacion_id' => $ubic1->id,
        'email' => 'colab.ubic@test.com',
    ]);
    expect($colaborador->refresh()->historialUbicaciones()->count())->toBe(1);

    $this->service->actualizarColaborador($colaborador->refresh(), [
        'ubicacion_id' => $ubic2->id,
    ]);

    $colaborador->refresh()->load('colaborador');
    expect($colaborador->historialUbicaciones()->count())->toBe(2)
        ->and($colaborador->colaborador->ubicacion_id)->toBe($ubic2->id);
});

it('genera historial al cambiar puesto', function (): void {
    $puesto1 = crearPuesto($this->empresa, ['nombre' => 'Analista']);
    $puesto2 = crearPuesto($this->empresa, ['nombre' => 'Senior']);
    $colaborador = crearUserColaborador($this->empresa, [
        'puesto_id' => $puesto1->id,
        'email' => 'colab.puesto@test.com',
    ]);
    expect($colaborador->refresh()->historialPuestos()->count())->toBe(1);

    $this->service->actualizarColaborador($colaborador->refresh(), [
        'puesto_id' => $puesto2->id,
    ]);

    $colaborador->refresh()->load('colaborador');
    expect($colaborador->historialPuestos()->count())->toBe(2)
        ->and($colaborador->colaborador->puesto_id)->toBe($puesto2->id);
});

it('no genera historial si el catálogo no cambió', function (): void {
    $dept = Departamento::create(['empresa_id' => $this->empresa->id, 'nombre' => 'RH']);
    $colaborador = crearUserColaborador($this->empresa, [
        'departamento_id' => $dept->id,
        'email' => 'colab.same@test.com',
    ]);
    $countAntes = $colaborador->refresh()->historialDepartamentos()->count();

    $this->service->actualizarColaborador($colaborador->refresh(), [
        'departamento_id' => $dept->id,
        'name' => 'Nombre Actualizado',
    ]);

    $colaborador->refresh();
    expect($colaborador->historialDepartamentos()->count())->toBe($countAntes)
        ->and($colaborador->name)->toBe('Nombre Actualizado');
});

it('regenera codigo_jefe al cambiar catálogos', function (): void {
    $ubic1 = crearUbicacion($this->empresa, ['nombre' => 'U1']);
    $dept1 = Departamento::create(['empresa_id' => $this->empresa->id, 'nombre' => 'D1']);
    $area1 = crearArea($this->empresa, ['nombre' => 'A1']);
    $puesto1 = crearPuesto($this->empresa, ['nombre' => 'P1']);
    $colaborador = crearUserColaborador($this->empresa, [
        'ubicacion_id' => $ubic1->id,
        'departamento_id' => $dept1->id,
        'area_id' => $area1->id,
        'puesto_id' => $puesto1->id,
        'email' => 'colab.codigo@test.com',
    ]);
    $codigoAntes = $colaborador->refresh()->codigo_jefe;

    $dept2 = Departamento::create(['empresa_id' => $this->empresa->id, 'nombre' => 'D2']);
    $this->service->actualizarColaborador($colaborador->refresh(), [
        'departamento_id' => $dept2->id,
    ]);

    $colaborador->refresh();
    $codigoEsperado = implode('.', [$ubic1->id, $dept2->id, $area1->id, $puesto1->id]);
    expect($colaborador->codigo_jefe)->toBe($codigoEsperado)
        ->and($colaborador->codigo_jefe)->not->toBe($codigoAntes);
});
