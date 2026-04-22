<?php

declare(strict_types=1);

use App\Enums\EstadoVerificacionCuenta;
use App\Models\Banco;
use App\Models\Colaborador;
use App\Models\CuentaBancaria;
use App\Models\Empresa;
use App\Models\Industria;
use App\Models\Subindustria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $industria = Industria::withoutEvents(fn (): Industria => Industria::query()->create([
        'nombre' => 'Industria Test Cuenta',
    ]));

    $subindustria = Subindustria::withoutEvents(fn (): Subindustria => Subindustria::query()->create([
        'nombre' => 'Subindustria Test Cuenta',
        'industria_id' => $industria->id,
    ]));

    $this->empresa = Empresa::withoutEvents(fn (): Empresa => Empresa::factory()->create([
        'industria_id' => $industria->id,
        'sub_industria_id' => $subindustria->id,
    ]));

    $this->colaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $this->user = User::factory()->colaborador()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    $this->banco = Banco::query()->create([
        'nombre' => 'Banco Test',
        'codigo' => fake()->numberBetween(100, 999),
        'comision' => 0.00,
    ]);
});

function crearCuentaBase(
    Banco $banco,
    User $user,
    Colaborador $colaborador,
    array $overrides = []
): CuentaBancaria {
    return CuentaBancaria::query()->create(array_merge([
        'numero' => (string) fake()->numberBetween(10000000, 99999999),
        'tipo' => 'DEBITO',
        'alias' => 'Cuenta test',
        'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
        'banco_id' => $banco->id,
        'user_id' => $user->id,
        'colaborador_id' => $colaborador->id,
        'es_nomina' => false,
        'enviado_verificacion' => false,
    ], $overrides));
}

describe('Estados de verificacion', function (): void {
    test('cuenta nueva tiene estado sin_verificar por defecto', function (): void {
        $cuenta = CuentaBancaria::query()->create([
            'numero' => (string) fake()->numberBetween(10000000, 99999999),
            'tipo' => 'DEBITO',
            'alias' => 'Cuenta nueva',
            'banco_id' => $this->banco->id,
            'user_id' => $this->user->id,
            'colaborador_id' => $this->colaborador->id,
            'es_nomina' => false,
            'enviado_verificacion' => false,
        ]);

        expect($cuenta->estado)->toBe(EstadoVerificacionCuenta::SIN_VERIFICAR);
    });

    test('cuenta sin verificar puede verificarse', function (): void {
        $cuenta = crearCuentaBase($this->banco, $this->user, $this->colaborador, [
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
        ]);

        expect($cuenta->puedeVerificarse())->toBeTrue();
    });

    test('cuenta verificada no puede verificarse nuevamente', function (): void {
        $cuenta = crearCuentaBase($this->banco, $this->user, $this->colaborador, [
            'estado' => EstadoVerificacionCuenta::VERIFICADA->value,
        ]);

        expect($cuenta->puedeVerificarse())->toBeFalse();
    });

    test('cuenta rechazada puede reenviarse', function (): void {
        $cuenta = crearCuentaBase($this->banco, $this->user, $this->colaborador, [
            'estado' => EstadoVerificacionCuenta::RECHAZADA->value,
        ]);

        expect($cuenta->puedeReenviarse())->toBeTrue();
    });
});

describe('Marcar como verificada', function (): void {
    test('al verificar cuenta se marca como nomina', function (): void {
        $cuenta = crearCuentaBase($this->banco, $this->user, $this->colaborador, [
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
            'es_nomina' => false,
        ]);

        $cuenta->marcarComoVerificada();

        expect($cuenta->fresh()->estado)->toBe(EstadoVerificacionCuenta::VERIFICADA);
        expect($cuenta->fresh()->es_nomina)->toBeTrue();
    });

    test('al verificar cuenta las demas del colaborador dejan de ser nomina', function (): void {
        $cuentaAnterior = crearCuentaBase($this->banco, $this->user, $this->colaborador, [
            'estado' => EstadoVerificacionCuenta::VERIFICADA->value,
            'es_nomina' => true,
        ]);

        $cuentaNueva = crearCuentaBase($this->banco, $this->user, $this->colaborador, [
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
            'es_nomina' => false,
        ]);

        $cuentaNueva->marcarComoVerificada();

        expect($cuentaAnterior->fresh()->es_nomina)->toBeFalse();
        expect($cuentaNueva->fresh()->es_nomina)->toBeTrue();
    });
});

describe('Reenvio a verificacion', function (): void {
    test('reenviar cuenta rechazada la pone sin verificar y no enviada', function (): void {
        $cuenta = crearCuentaBase($this->banco, $this->user, $this->colaborador, [
            'estado' => EstadoVerificacionCuenta::RECHAZADA->value,
            'enviado_verificacion' => true,
        ]);

        $cuenta->reenviarAVerificacion();

        expect($cuenta->fresh()->estado)->toBe(EstadoVerificacionCuenta::SIN_VERIFICAR);
        expect($cuenta->fresh()->enviado_verificacion)->toBeFalse();
    });
});

describe('Scopes', function (): void {
    test('scope sinVerificar filtra correctamente', function (): void {
        crearCuentaBase($this->banco, $this->user, $this->colaborador, [
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
        ]);

        crearCuentaBase($this->banco, $this->user, $this->colaborador, [
            'estado' => EstadoVerificacionCuenta::VERIFICADA->value,
        ]);

        expect(CuentaBancaria::query()->sinVerificar()->count())->toBe(1);
    });
});
