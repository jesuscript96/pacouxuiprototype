<?php

use App\Models\BajaColaborador;
use App\Models\Colaborador;
use App\Models\ReingresoColaborador;
use App\Models\User;
use App\Services\ColaboradorBajaService;
use App\Services\ReingresoColaboradorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    $this->service = app(ReingresoColaboradorService::class);
    $this->authUser = User::query()->where('email', 'admin@paco.com')->firstOrFail();
    $this->actingAs($this->authUser);
});

it('reingresa colaborador dado de baja con nuevo usuario', function (): void {
    $user = crearUserColaborador($this->empresa);
    $colaborador = $user->refresh()->colaborador;
    expect($colaborador)->not->toBeNull();

    $bajaService = app(ColaboradorBajaService::class);
    $baja = $bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
    ]);

    $reingreso = $this->service->reingresar($baja, [
        'fecha_ingreso' => now()->addDay()->format('Y-m-d'),
        'nombre' => $colaborador->nombre,
        'apellido_paterno' => $colaborador->apellido_paterno,
        'email' => 'reingreso.'.uniqid().'@test.com',
        'periodicidad_pago' => 'QUINCENAL',
        'crear_usuario' => true,
    ]);

    expect($reingreso)->toBeInstanceOf(ReingresoColaborador::class)
        ->and($reingreso->colaborador_anterior_id)->toBe($colaborador->id)
        ->and($reingreso->colaborador_nuevo_id)->not->toBe($colaborador->id);

    $reingreso->loadMissing('colaboradorNuevo', 'userNuevo');
    expect($reingreso->colaboradorNuevo)->toBeInstanceOf(Colaborador::class)
        ->and($reingreso->userNuevo)->toBeInstanceOf(User::class);

    expect($baja->fresh()->trashed())->toBeTrue();
});

it('no permite reingresar baja programada', function (): void {
    $user = crearUserColaborador($this->empresa);
    $colaborador = $user->refresh()->colaborador;
    expect($colaborador)->not->toBeNull();

    $bajaService = app(ColaboradorBajaService::class);
    $baja = $bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->addWeek()->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
    ]);

    expect($baja->esProgramada())->toBeTrue();

    $this->service->reingresar($baja, [
        'fecha_ingreso' => now()->addWeeks(2)->format('Y-m-d'),
        'periodicidad_pago' => 'QUINCENAL',
    ]);
})->throws(ValidationException::class);

it('no permite reingresar dos veces la misma baja', function (): void {
    $user = crearUserColaborador($this->empresa);
    $colaborador = $user->refresh()->colaborador;
    expect($colaborador)->not->toBeNull();

    $bajaService = app(ColaboradorBajaService::class);
    $baja = $bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
    ]);

    $this->service->reingresar($baja, [
        'fecha_ingreso' => now()->addDay()->format('Y-m-d'),
        'nombre' => $colaborador->nombre,
        'apellido_paterno' => $colaborador->apellido_paterno,
        'email' => 'primero.'.uniqid().'@test.com',
        'periodicidad_pago' => 'QUINCENAL',
    ]);

    $this->service->reingresar($baja, [
        'fecha_ingreso' => now()->addDays(2)->format('Y-m-d'),
        'nombre' => $colaborador->nombre,
        'apellido_paterno' => $colaborador->apellido_paterno,
        'email' => 'segundo.'.uniqid().'@test.com',
        'periodicidad_pago' => 'QUINCENAL',
    ]);
})->throws(ValidationException::class);

it('genera nuevo número de colaborador con prefijo RE- en reingreso', function (): void {
    $user = crearUserColaborador($this->empresa);
    $colaborador = $user->refresh()->colaborador;
    expect($colaborador)->not->toBeNull();

    $bajaService = app(ColaboradorBajaService::class);
    $baja = $bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
    ]);

    $reingreso = $this->service->reingresar($baja, [
        'fecha_ingreso' => now()->addDay()->format('Y-m-d'),
        'nombre' => $colaborador->nombre,
        'apellido_paterno' => $colaborador->apellido_paterno,
        'email' => 're.'.uniqid().'@test.com',
        'periodicidad_pago' => 'QUINCENAL',
        'crear_usuario' => true,
    ]);

    expect($reingreso->colaboradorNuevo->numero_colaborador)
        ->not->toBe($colaborador->numero_colaborador)
        ->and(str_starts_with((string) $reingreso->colaboradorNuevo->numero_colaborador, 'RE-'))->toBeTrue();
});

it('obtiene datos pre-llenados para formulario', function (): void {
    $user = crearUserColaborador($this->empresa);
    $colaborador = $user->refresh()->colaborador;
    expect($colaborador)->not->toBeNull();

    $bajaService = app(ColaboradorBajaService::class);
    $baja = $bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
    ]);

    $datos = $this->service->obtenerDatosParaReingreso($baja);

    expect($datos['nombre'])->toBe($colaborador->nombre)
        ->and($datos['email'])->toBe($colaborador->email)
        ->and($datos['empresa_id'])->toBe($colaborador->empresa_id);
});
