<?php

declare(strict_types=1);

use App\Enums\EstadoVerificacionCuenta;
use App\Models\Banco;
use App\Models\Colaborador;
use App\Models\CuentaBancaria;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\EstadoCuenta;
use App\Models\Industria;
use App\Models\IntentoCobro;
use App\Models\Subindustria;
use App\Models\User;
use App\Services\VerificacionCuentas\VerificacionCuentaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(VerificacionCuentaService::class);

    $industria = Industria::withoutEvents(fn (): Industria => Industria::query()->create([
        'nombre' => 'Industria Verificación',
    ]));

    $subindustria = Subindustria::withoutEvents(fn (): Subindustria => Subindustria::query()->create([
        'nombre' => 'Subindustria Verificación',
        'industria_id' => $industria->id,
    ]));

    $this->empresa = Empresa::withoutEvents(fn (): Empresa => Empresa::factory()->create([
        'industria_id' => $industria->id,
        'sub_industria_id' => $subindustria->id,
    ]));

    $this->colaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
        'fecha_ingreso' => now()->subMonths(6),
    ]);

    $this->user = User::factory()->colaborador()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $this->colaborador->id,
    ]);

    $this->banco = Banco::query()->create([
        'nombre' => 'Banco Verificación',
        'codigo' => fake()->unique()->numberBetween(100, 999),
        'comision' => 0.00,
    ]);
});

function crearCuentaParaVerificacion(array $overrides = []): CuentaBancaria
{
    return CuentaBancaria::query()->create(array_merge([
        'numero' => (string) fake()->unique()->numberBetween(10000000, 99999999),
        'tipo' => 'DEBITO',
        'alias' => 'Cuenta test verificación',
        'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
        'banco_id' => test()->banco->id,
        'user_id' => test()->user->id,
        'colaborador_id' => test()->colaborador->id,
        'es_nomina' => false,
        'enviado_verificacion' => false,
    ], $overrides));
}

describe('Validar cuenta', function (): void {

    test('valida cuenta correctamente y la marca como nómina', function (): void {
        $cuenta = crearCuentaParaVerificacion();

        $this->service->validarCuenta($cuenta);

        $cuenta->refresh();
        expect($cuenta->estado)->toBe(EstadoVerificacionCuenta::VERIFICADA);
        expect($cuenta->es_nomina)->toBeTrue();
    });

    test('al validar elimina cuenta temporal del colaborador con forceDelete', function (): void {
        $bancoTemporal = Banco::withoutEvents(function (): Banco {
            $banco = new Banco(['nombre' => 'Banco Temporal', 'codigo' => '023', 'comision' => 0.00]);
            $banco->id = 23;
            $banco->save();

            return $banco;
        });

        $cuentaTemporal = CuentaBancaria::query()->create([
            'numero' => '0000000000',
            'banco_id' => $bancoTemporal->id,
            'user_id' => $this->user->id,
            'colaborador_id' => $this->colaborador->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
        ]);

        $cuentaReal = crearCuentaParaVerificacion();

        $this->service->validarCuenta($cuentaReal);

        expect(CuentaBancaria::withTrashed()->find($cuentaTemporal->id))->toBeNull();
    });

    test('no puede validar cuenta ya verificada', function (): void {
        $cuenta = crearCuentaParaVerificacion([
            'estado' => EstadoVerificacionCuenta::VERIFICADA->value,
        ]);

        expect(fn () => $this->service->validarCuenta($cuenta))
            ->toThrow(ValidationException::class);
    });

    test('al validar desmarca otras cuentas como nómina', function (): void {
        $cuentaAnterior = crearCuentaParaVerificacion([
            'estado' => EstadoVerificacionCuenta::VERIFICADA->value,
            'es_nomina' => true,
        ]);

        $cuentaNueva = crearCuentaParaVerificacion();

        $this->service->validarCuenta($cuentaNueva);

        expect($cuentaAnterior->fresh()->es_nomina)->toBeFalse();
        expect($cuentaNueva->fresh()->es_nomina)->toBeTrue();
    });

});

describe('Rechazar cuenta', function (): void {

    test('rechaza cuenta no-nómina y la elimina (soft delete)', function (): void {
        $cuenta = crearCuentaParaVerificacion(['es_nomina' => false]);

        $this->service->rechazarCuenta($cuenta, reenviar: false);

        expect(CuentaBancaria::find($cuenta->id))->toBeNull();
        expect(CuentaBancaria::withTrashed()->find($cuenta->id)?->estado)
            ->toBe(EstadoVerificacionCuenta::RECHAZADA);
    });

    test('rechaza cuenta de nómina sin eliminarla', function (): void {
        $cuenta = crearCuentaParaVerificacion(['es_nomina' => true]);

        $this->service->rechazarCuenta($cuenta, reenviar: false);

        expect(CuentaBancaria::find($cuenta->id))->not->toBeNull();
        expect($cuenta->fresh()->estado)->toBe(EstadoVerificacionCuenta::RECHAZADA);
    });

    test('reenviar cuenta rechazada la pone sin verificar', function (): void {
        $cuenta = crearCuentaParaVerificacion([
            'estado' => EstadoVerificacionCuenta::RECHAZADA->value,
            'enviado_verificacion' => true,
        ]);

        $this->service->rechazarCuenta($cuenta, reenviar: true);

        $cuenta->refresh();
        expect($cuenta->estado)->toBe(EstadoVerificacionCuenta::SIN_VERIFICAR);
        expect($cuenta->enviado_verificacion)->toBeFalse();
    });

    test('reasigna adeudos pendientes a cuenta verificada alternativa', function (): void {
        $cuentaRechazada = crearCuentaParaVerificacion(['es_nomina' => false]);
        $cuentaVerificada = crearCuentaParaVerificacion([
            'estado' => EstadoVerificacionCuenta::VERIFICADA->value,
            'es_nomina' => true,
        ]);

        $estadoCuenta = EstadoCuenta::query()->create([
            'desde' => now()->subMonth(),
            'hasta' => now(),
            'saldo' => 100.00,
            'estado' => 'ACTIVO',
            'user_id' => $this->user->id,
        ]);

        $adeudo = CuentaPorCobrar::query()->create([
            'estado' => 'PENDIENTE',
            'debe' => 500.00,
            'estado_cuenta_id' => $estadoCuenta->id,
            'cuenta_bancaria_id' => $cuentaRechazada->id,
            'empresa_id' => $this->empresa->id,
            'user_id' => $this->user->id,
        ]);

        // BL: Crear intento con codigo_razon 1 (STP 01)
        IntentoCobro::query()->create([
            'codigo_razon' => 1,
            'cuenta_bancaria_id' => $cuentaRechazada->id,
            'cuenta_por_cobrar_id' => $adeudo->id,
            'monto' => 500.00,
        ]);

        $this->service->rechazarCuenta($cuentaRechazada, reenviar: false);

        expect($adeudo->fresh()->cuenta_bancaria_id)->toBe($cuentaVerificada->id);
    });

});

describe('Obtener cuentas pendientes de envío', function (): void {

    test('obtiene cuentas pendientes correctamente', function (): void {
        crearCuentaParaVerificacion();

        $cuentas = $this->service->obtenerCuentasPendientesDeEnvio();

        expect($cuentas)->toHaveCount(1);
    });

    test('excluye cuentas de banco temporal (ID 23)', function (): void {
        $bancoTemporal = Banco::withoutEvents(function (): Banco {
            $banco = new Banco(['nombre' => 'Banco Temporal', 'codigo' => '023', 'comision' => 0.00]);
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

        $cuentas = $this->service->obtenerCuentasPendientesDeEnvio();

        expect($cuentas)->toHaveCount(0);
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
            'numero' => (string) fake()->unique()->numberBetween(10000000, 99999999),
            'banco_id' => $this->banco->id,
            'user_id' => $userNuevo->id,
            'colaborador_id' => $colaboradorNuevo->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
            'enviado_verificacion' => false,
        ]);

        $cuentas = $this->service->obtenerCuentasPendientesDeEnvio();

        expect($cuentas)->toHaveCount(0);
    });

    test('no envía cuentas a las 18:00 hora Guatemala', function (): void {
        Carbon::setTestNow(Carbon::create(2026, 3, 31, 18, 30, 0, 'America/Guatemala'));

        crearCuentaParaVerificacion();

        $cuentas = $this->service->obtenerCuentasPendientesDeEnvio();

        expect($cuentas)->toHaveCount(0);

        Carbon::setTestNow();
    });

    test('excluye cuentas ya enviadas', function (): void {
        crearCuentaParaVerificacion(['enviado_verificacion' => true]);

        $cuentas = $this->service->obtenerCuentasPendientesDeEnvio();

        expect($cuentas)->toHaveCount(0);
    });

});

describe('Marcar como enviadas', function (): void {

    test('marca cuentas como enviadas a verificación', function (): void {
        $cuenta1 = crearCuentaParaVerificacion();
        $cuenta2 = crearCuentaParaVerificacion();

        $this->service->marcarComoEnviadas(collect([$cuenta1, $cuenta2]));

        expect($cuenta1->fresh()->enviado_verificacion)->toBeTrue();
        expect($cuenta2->fresh()->enviado_verificacion)->toBeTrue();
    });

});

describe('Procesamiento masivo', function (): void {

    test('procesa resultados masivos correctamente', function (): void {
        crearCuentaParaVerificacion(['numero' => '1111111111']);
        crearCuentaParaVerificacion(['numero' => '2222222222', 'es_nomina' => false]);

        $resultados = [
            ['numero' => '1111111111', 'resultado' => 'Valida', 'reenviar' => false],
            ['numero' => '2222222222', 'resultado' => 'No valida', 'reenviar' => false],
        ];

        $resumen = $this->service->procesarResultadosMasivos($resultados);

        expect($resumen['validadas'])->toBe(1);
        expect($resumen['rechazadas'])->toBe(1);
        expect($resumen['errores'])->toBeEmpty();
    });

    test('reporta error para cuentas no encontradas', function (): void {
        $resultados = [
            ['numero' => '9999999999', 'resultado' => 'Valida'],
        ];

        $resumen = $this->service->procesarResultadosMasivos($resultados);

        expect($resumen['validadas'])->toBe(0);
        expect($resumen['errores'])->toHaveCount(1);
    });

    test('cuenta reenviada en masivo se contabiliza correctamente', function (): void {
        crearCuentaParaVerificacion(['numero' => '3333333333']);

        $resultados = [
            ['numero' => '3333333333', 'resultado' => 'No valida', 'reenviar' => true],
        ];

        $resumen = $this->service->procesarResultadosMasivos($resultados);

        expect($resumen['reenviadas'])->toBe(1);
        expect($resumen['rechazadas'])->toBe(0);
    });

});

describe('Payload STP', function (): void {

    test('prepara payload con formato correcto', function (): void {
        $cuenta = crearCuentaParaVerificacion();
        $cuenta->load('banco');

        $payload = $this->service->prepararPayloadSTP(collect([$cuenta]));

        expect($payload)->toHaveCount(1);
        expect($payload[0])->toHaveKeys(['date', 'transferId', 'institucionContraparte', 'bank_code', 'account', 'amount']);
        expect($payload[0]['amount'])->toBe(0);
        expect($payload[0]['institucionContraparte'])->toBe(90646);
        expect($payload[0]['account'])->toBe($cuenta->numero);
    });

});

describe('Contadores', function (): void {

    test('cuenta pendientes correctamente', function (): void {
        crearCuentaParaVerificacion();
        crearCuentaParaVerificacion(['estado' => EstadoVerificacionCuenta::VERIFICADA->value]);

        expect($this->service->contarPendientes())->toBe(1);
    });

    test('cuenta pendientes de envío excluyendo banco temporal', function (): void {
        crearCuentaParaVerificacion();

        $bancoTemporal = Banco::withoutEvents(function (): Banco {
            $banco = new Banco(['nombre' => 'Banco Temporal 3', 'codigo' => '323', 'comision' => 0.00]);
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

        expect($this->service->contarPendientesDeEnvio())->toBe(1);
    });

});
