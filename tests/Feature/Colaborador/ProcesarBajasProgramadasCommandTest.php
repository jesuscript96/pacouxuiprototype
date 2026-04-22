<?php

use App\Models\BajaColaborador;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\User;

require_once __DIR__.'/Helpers.php';

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
});

/**
 * @return array{0: User, 1: Colaborador}
 */
function usuarioYColaboradorParaComandoBajas(Empresa $empresa): array
{
    $user = crearUserColaborador($empresa);
    $user->refresh()->load('colaborador');
    $colaborador = $user->colaborador;
    if ($colaborador === null) {
        throw new RuntimeException('crearUserColaborador debe dejar colaborador asociado.');
    }

    return [$user, $colaborador];
}

it('procesa bajas programadas vencidas', function (): void {
    [$user, $colaborador] = usuarioYColaboradorParaComandoBajas($this->empresa);

    $baja = BajaColaborador::query()->create([
        'colaborador_id' => $colaborador->id,
        'user_id' => $user->id,
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
        'registrado_por' => null,
    ]);

    $this->artisan('bajas:procesar-programadas')
        ->assertSuccessful();

    $baja->refresh();
    expect($baja->estado)->toBe(BajaColaborador::ESTADO_EJECUTADA)
        ->and($baja->ejecutada_at)->not->toBeNull();

    expect(Colaborador::withTrashed()->find($colaborador->id)?->trashed())->toBeTrue();
    expect(User::withTrashed()->find($user->id)?->trashed())->toBeTrue();
});

it('no hace nada si no hay bajas vencidas', function (): void {
    $this->artisan('bajas:procesar-programadas')
        ->expectsOutputToContain('No hay bajas programadas pendientes de ejecutar.')
        ->assertSuccessful();
});

it('muestra bajas sin ejecutar en modo dry-run', function (): void {
    [, $colaborador] = usuarioYColaboradorParaComandoBajas($this->empresa);

    BajaColaborador::query()->create([
        'colaborador_id' => $colaborador->id,
        'user_id' => $colaborador->user?->id,
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
        'registrado_por' => null,
    ]);

    $this->artisan('bajas:procesar-programadas', ['--dry-run' => true])
        ->expectsOutputToContain('Modo dry-run: no se ejecutarán las bajas.')
        ->assertSuccessful();

    expect(BajaColaborador::query()->programadas()->count())->toBe(1);
});

it('ignora bajas con fecha futura', function (): void {
    [, $colaborador] = usuarioYColaboradorParaComandoBajas($this->empresa);

    BajaColaborador::query()->create([
        'colaborador_id' => $colaborador->id,
        'user_id' => $colaborador->user?->id,
        'empresa_id' => $colaborador->empresa_id,
        'fecha_baja' => now()->addWeek()->toDateString(),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
        'estado' => BajaColaborador::ESTADO_PROGRAMADA,
        'ubicacion_id' => $colaborador->ubicacion_id,
        'departamento_id' => $colaborador->departamento_id,
        'area_id' => $colaborador->area_id,
        'puesto_id' => $colaborador->puesto_id,
        'region_id' => $colaborador->region_id,
        'centro_pago_id' => $colaborador->centro_pago_id,
        'razon_social_id' => $colaborador->razon_social_id,
        'registrado_por' => null,
    ]);

    $this->artisan('bajas:procesar-programadas')
        ->expectsOutputToContain('No hay bajas programadas pendientes de ejecutar.')
        ->assertSuccessful();
});

it('ignora bajas ya ejecutadas', function (): void {
    [, $colaborador] = usuarioYColaboradorParaComandoBajas($this->empresa);

    BajaColaborador::query()->create([
        'colaborador_id' => $colaborador->id,
        'user_id' => $colaborador->user?->id,
        'empresa_id' => $colaborador->empresa_id,
        'fecha_baja' => now()->subDay()->toDateString(),
        'motivo' => BajaColaborador::MOTIVO_RENUNCIA,
        'estado' => BajaColaborador::ESTADO_EJECUTADA,
        'ejecutada_at' => now()->subDay(),
        'ubicacion_id' => $colaborador->ubicacion_id,
        'departamento_id' => $colaborador->departamento_id,
        'area_id' => $colaborador->area_id,
        'puesto_id' => $colaborador->puesto_id,
        'region_id' => $colaborador->region_id,
        'centro_pago_id' => $colaborador->centro_pago_id,
        'razon_social_id' => $colaborador->razon_social_id,
        'registrado_por' => null,
    ]);

    $this->artisan('bajas:procesar-programadas')
        ->expectsOutputToContain('No hay bajas programadas pendientes de ejecutar.')
        ->assertSuccessful();
});
