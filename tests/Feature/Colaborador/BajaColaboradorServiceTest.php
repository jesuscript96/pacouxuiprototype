<?php

use App\Models\BajaColaborador;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\User;
use App\Services\ColaboradorBajaService;
use App\Services\ColaboradorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

require_once __DIR__.'/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
    $this->bajaService = app(ColaboradorBajaService::class);
    $this->authUser = User::query()->where('email', 'admin@paco.com')->firstOrFail();
    $this->actingAs($this->authUser);
});

function crearFichaParaBaja(Empresa $empresa, string $fechaIngreso = '2024-06-01'): Colaborador
{
    $user = app(ColaboradorService::class)->crearColaborador([
        'name' => 'Ana',
        'apellido_paterno' => 'López',
        'apellido_materno' => 'Ruiz',
        'email' => 'ana.baja.'.uniqid().'@test.com',
        'fecha_nacimiento' => '1990-01-01',
        'fecha_ingreso' => $fechaIngreso,
        'periodicidad_pago' => 'QUINCENAL',
    ], $empresa);

    $user->refresh()->load('colaborador');

    return $user->colaborador;
}

it('registra baja programada sin ejecutar efectos', function (): void {
    $colaborador = crearFichaParaBaja($this->empresa);
    $user = $colaborador->user;

    $baja = $this->bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->addWeek()->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
        'comentarios' => 'Test',
    ]);

    if (Schema::hasTable(config('activitylog.table_name', 'activity_log'))) {
        expect(
            Activity::query()
                ->where('subject_type', BajaColaborador::class)
                ->where('subject_id', $baja->id)
                ->exists()
        )->toBeTrue();
    }

    expect($baja->esProgramada())->toBeTrue()
        ->and($baja->registrado_por)->toBe($this->authUser->id)
        ->and($user->fresh())->not->toBeNull()
        ->and($user->fresh()->trashed())->toBeFalse()
        ->and($colaborador->fresh()->trashed())->toBeFalse();
});

it('registra baja inmediata y aplica soft delete a user y colaborador', function (): void {
    $colaborador = crearFichaParaBaja($this->empresa);
    $userId = $colaborador->user->id;
    $colabId = $colaborador->id;

    $baja = $this->bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_DESPIDO,
    ]);

    expect($baja->estaEjecutada())->toBeTrue()
        ->and($baja->ejecutada_at)->not->toBeNull();

    expect(User::withTrashed()->find($userId)?->trashed())->toBeTrue();
    expect(Colaborador::withTrashed()->find($colabId)?->trashed())->toBeTrue();
});

it('rechaza segunda baja si ya hay una programada', function (): void {
    $colaborador = crearFichaParaBaja($this->empresa);

    $this->bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->addDays(10)->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
    ]);

    $this->bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->addDays(20)->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_ABANDONO,
    ]);
})->throws(ValidationException::class);

it('rechaza baja si ya existe una ejecutada', function (): void {
    $colaborador = crearFichaParaBaja($this->empresa);

    $this->bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
    ]);

    $colab = Colaborador::withTrashed()->find($colaborador->id);
    expect($colab)->not->toBeNull();

    $this->bajaService->registrarBaja($colab, [
        'fecha_baja' => now()->addWeek()->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_ABANDONO,
    ]);
})->throws(ValidationException::class);

it('rechaza fecha de baja anterior o igual a fecha de ingreso', function (): void {
    $colaborador = crearFichaParaBaja($this->empresa, '2024-06-15');

    $this->bajaService->registrarBaja($colaborador, [
        'fecha_baja' => '2024-06-15',
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
    ]);
})->throws(ValidationException::class);

it('cancela una baja programada', function (): void {
    $colaborador = crearFichaParaBaja($this->empresa);

    $baja = $this->bajaService->registrarBaja($colaborador, [
        'fecha_baja' => now()->addMonth()->format('Y-m-d'),
        'motivo' => BajaColaborador::MOTIVO_TERMINO_CONTRATO,
    ]);

    $this->bajaService->cancelarBaja($baja->fresh());

    expect($baja->fresh()->estaCancelada())->toBeTrue();
});

it('procesa bajas programadas vencidas', function (): void {
    $colaborador = crearFichaParaBaja($this->empresa);
    $userId = $colaborador->user->id;

    BajaColaborador::query()->create([
        'colaborador_id' => $colaborador->id,
        'user_id' => $userId,
        'empresa_id' => $colaborador->empresa_id,
        'fecha_baja' => now()->subDay()->toDateString(),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
        'estado' => BajaColaborador::ESTADO_PROGRAMADA,
        'ubicacion_id' => $colaborador->ubicacion_id,
        'departamento_id' => $colaborador->departamento_id,
        'area_id' => $colaborador->area_id,
        'puesto_id' => $colaborador->puesto_id,
        'region_id' => $colaborador->region_id,
        'centro_pago_id' => $colaborador->centro_pago_id,
        'razon_social_id' => $colaborador->razon_social_id,
        'registrado_por' => $this->authUser->id,
    ]);

    $n = $this->bajaService->procesarBajasProgramadasVencidas();

    expect($n)->toBe(1);
    expect(User::withTrashed()->find($userId)?->trashed())->toBeTrue();
});
