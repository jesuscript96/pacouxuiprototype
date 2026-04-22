<?php

declare(strict_types=1);

use App\Enums\EstadoVerificacionCuenta;
use App\Models\Banco;
use App\Models\Colaborador;
use App\Models\CuentaBancaria;
use App\Models\Empresa;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);

    $this->empresa = Empresa::factory()->create();

    $this->colaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
        'fecha_ingreso' => now()->subMonths(6),
    ]);

    $this->user = User::factory()->colaborador()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    $this->banco = Banco::query()->create([
        'nombre' => 'Banco Verificación Cmd',
        'codigo' => fake()->unique()->numberBetween(100, 999),
        'comision' => 0.00,
    ]);
});

function crearCuentaParaComando(array $overrides = []): CuentaBancaria
{
    return CuentaBancaria::query()->create(array_merge([
        'numero' => (string) fake()->unique()->numberBetween(10000000, 99999999),
        'tipo' => 'DEBITO',
        'banco_id' => test()->banco->id,
        'user_id' => test()->user->id,
        'colaborador_id' => test()->colaborador->id,
        'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
        'enviado_verificacion' => false,
    ], $overrides));
}

describe('Comando verificacion:enviar-pendientes', function (): void {

    test('muestra mensaje cuando no hay cuentas pendientes', function (): void {
        $this->artisan('verificacion:enviar-pendientes')
            ->expectsOutput('Iniciando envío de cuentas a verificación...')
            ->expectsOutput('No hay cuentas pendientes de envío.')
            ->assertSuccessful();
    });

    test('encuentra y marca cuentas como enviadas', function (): void {
        crearCuentaParaComando();

        $this->artisan('verificacion:enviar-pendientes')
            ->expectsOutputToContain('1 cuentas pendientes de envío')
            ->expectsOutputToContain('1 cuentas marcadas como enviadas')
            ->assertSuccessful();

        expect(CuentaBancaria::first()->enviado_verificacion)->toBeTrue();
    });

    test('dry-run no marca cuentas como enviadas', function (): void {
        crearCuentaParaComando();

        $this->artisan('verificacion:enviar-pendientes', ['--dry-run' => true])
            ->expectsOutput('Modo dry-run activado. No se marcarán cuentas como enviadas.')
            ->expectsOutputToContain('se habrían marcado como enviadas')
            ->assertSuccessful();

        expect(CuentaBancaria::first()->enviado_verificacion)->toBeFalse();
    });

    test('respeta opción de límite', function (): void {
        for ($i = 1; $i <= 5; $i++) {
            crearCuentaParaComando(['numero' => "100000000{$i}"]);
        }

        $this->artisan('verificacion:enviar-pendientes', ['--limit' => 2])
            ->expectsOutput('Limitando a 2 cuentas.')
            ->expectsOutputToContain('2 cuentas marcadas como enviadas')
            ->assertSuccessful();

        expect(CuentaBancaria::query()->where('enviado_verificacion', true)->count())->toBe(2);
    });

    test('excluye cuentas de banco temporal (ID 23)', function (): void {
        $bancoTemporal = Banco::withoutEvents(function (): Banco {
            $banco = new Banco(['nombre' => 'Banco Temporal Cmd', 'codigo' => '023', 'comision' => 0.00]);
            $banco->id = 23;
            $banco->save();

            return $banco;
        });

        CuentaBancaria::query()->create([
            'numero' => '0000000000',
            'banco_id' => $bancoTemporal->id,
            'user_id' => $this->user->id,
            'colaborador_id' => $this->colaborador->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
            'enviado_verificacion' => false,
        ]);

        $this->artisan('verificacion:enviar-pendientes')
            ->expectsOutput('No hay cuentas pendientes de envío.')
            ->assertSuccessful();
    });

    test('excluye colaboradores con antigüedad menor a 3 meses', function (): void {
        $colaboradorNuevo = Colaborador::factory()->create([
            'empresa_id' => $this->empresa->id,
            'fecha_ingreso' => now()->subMonth(),
        ]);

        $userNuevo = User::factory()->colaborador()->create([
            'empresa_id' => $this->empresa->id,
            'colaborador_id' => $colaboradorNuevo->id,
        ]);

        CuentaBancaria::query()->create([
            'numero' => '1234567890',
            'banco_id' => $this->banco->id,
            'user_id' => $userNuevo->id,
            'colaborador_id' => $colaboradorNuevo->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
            'enviado_verificacion' => false,
        ]);

        $this->artisan('verificacion:enviar-pendientes')
            ->expectsOutput('No hay cuentas pendientes de envío.')
            ->assertSuccessful();
    });

    test('no envía cuentas a las 18:00 hora Guatemala', function (): void {
        Carbon::setTestNow(Carbon::create(2026, 3, 31, 18, 30, 0, 'America/Guatemala'));

        crearCuentaParaComando();

        $this->artisan('verificacion:enviar-pendientes')
            ->expectsOutput('No hay cuentas pendientes de envío.')
            ->assertSuccessful();

        Carbon::setTestNow();
    });

    test('excluye cuentas ya enviadas', function (): void {
        crearCuentaParaComando(['enviado_verificacion' => true]);

        $this->artisan('verificacion:enviar-pendientes')
            ->expectsOutput('No hay cuentas pendientes de envío.')
            ->assertSuccessful();
    });

    test('excluye cuentas verificadas', function (): void {
        crearCuentaParaComando(['estado' => EstadoVerificacionCuenta::VERIFICADA->value]);

        $this->artisan('verificacion:enviar-pendientes')
            ->expectsOutput('No hay cuentas pendientes de envío.')
            ->assertSuccessful();
    });

});
