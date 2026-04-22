<?php

declare(strict_types=1);

use App\Enums\EstadoNotificacionPush;
use App\Enums\EstadoVerificacionCuenta;
use App\Jobs\EnviarNotificacionPushJob;
use App\Models\Banco;
use App\Models\Colaborador;
use App\Models\ConfiguracionApp;
use App\Models\CuentaBancaria;
use App\Models\Empresa;
use App\Models\Industria;
use App\Models\NotificacionPush;
use App\Models\Subindustria;
use App\Models\User;
use App\Services\VerificacionCuentas\VerificacionCuentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);

    Queue::fake();

    $this->service = app(VerificacionCuentaService::class);

    $industria = Industria::withoutEvents(fn (): Industria => Industria::query()->create([
        'nombre' => 'Industria Notificaciones',
    ]));

    $subindustria = Subindustria::withoutEvents(fn (): Subindustria => Subindustria::query()->create([
        'nombre' => 'Subindustria Notificaciones',
        'industria_id' => $industria->id,
    ]));

    $configuracionApp = ConfiguracionApp::query()->create([
        'nombre_app' => 'App Test',
        'android_app_id' => 'com.test.app',
        'ios_app_id' => 'com.test.app',
        'one_signal_app_id' => 'test-onesignal-app-id',
        'one_signal_rest_api_key' => 'test-onesignal-rest-api-key',
    ]);

    $this->empresa = Empresa::withoutEvents(fn (): Empresa => Empresa::factory()->create([
        'industria_id' => $industria->id,
        'sub_industria_id' => $subindustria->id,
        'configuracion_app_id' => $configuracionApp->id,
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
        'nombre' => 'Banco Notificaciones',
        'codigo' => fake()->unique()->numberBetween(100, 999),
        'comision' => 0.00,
    ]);
});

describe('Notificaciones al validar cuenta', function (): void {

    test('envía notificación push al validar cuenta si empresa tiene motivo incluido', function (): void {
        $this->empresa->notificacionesIncluidas()->attach(2);

        $cuenta = CuentaBancaria::query()->create([
            'numero' => '1234567890123456',
            'banco_id' => $this->banco->id,
            'colaborador_id' => $this->colaborador->id,
            'user_id' => $this->user->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
        ]);

        $this->service->validarCuenta($cuenta);

        $notificacion = NotificacionPush::query()->where('empresa_id', $this->empresa->id)->first();
        expect($notificacion)->not->toBeNull()
            ->and($notificacion->titulo)->toBe('Validación de cuenta EXITOSA')
            ->and($notificacion->mensaje)->toContain('3456')
            ->and($notificacion->estado)->toBe(EstadoNotificacionPush::ENVIANDO);

        Queue::assertPushed(EnviarNotificacionPushJob::class);
    });

    test('no envía notificación si empresa no tiene motivo incluido', function (): void {
        $cuenta = CuentaBancaria::query()->create([
            'numero' => '1234567890123456',
            'banco_id' => $this->banco->id,
            'colaborador_id' => $this->colaborador->id,
            'user_id' => $this->user->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
        ]);

        $this->service->validarCuenta($cuenta);

        expect(NotificacionPush::query()->where('empresa_id', $this->empresa->id)->exists())->toBeFalse();
        Queue::assertNotPushed(EnviarNotificacionPushJob::class);
    });

    test('no envía notificación si empresa no tiene credenciales OneSignal', function (): void {
        $this->empresa->notificacionesIncluidas()->attach(2);
        $this->empresa->update(['configuracion_app_id' => null]);

        $cuenta = CuentaBancaria::query()->create([
            'numero' => '1234567890123456',
            'banco_id' => $this->banco->id,
            'colaborador_id' => $this->colaborador->id,
            'user_id' => $this->user->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
        ]);

        $this->service->validarCuenta($cuenta);

        expect(NotificacionPush::query()->where('empresa_id', $this->empresa->id)->exists())->toBeFalse();
        Queue::assertNotPushed(EnviarNotificacionPushJob::class);
    });

});

describe('Notificaciones al rechazar cuenta', function (): void {

    test('envía notificación push al rechazar cuenta si empresa tiene motivo incluido', function (): void {
        $this->empresa->notificacionesIncluidas()->attach(3);

        $cuenta = CuentaBancaria::query()->create([
            'numero' => '9876543210987654',
            'banco_id' => $this->banco->id,
            'colaborador_id' => $this->colaborador->id,
            'user_id' => $this->user->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
            'es_nomina' => true,
        ]);

        $this->service->rechazarCuenta($cuenta, reenviar: false);

        $notificacion = NotificacionPush::query()->where('empresa_id', $this->empresa->id)->first();
        expect($notificacion)->not->toBeNull()
            ->and($notificacion->titulo)->toBe('RECHAZO en validación de cuenta')
            ->and($notificacion->mensaje)->toContain('7654');

        Queue::assertPushed(EnviarNotificacionPushJob::class);
    });

    test('no envía notificación al reenviar cuenta', function (): void {
        $this->empresa->notificacionesIncluidas()->attach(3);

        $cuenta = CuentaBancaria::query()->create([
            'numero' => '1234567890123456',
            'banco_id' => $this->banco->id,
            'colaborador_id' => $this->colaborador->id,
            'user_id' => $this->user->id,
            'estado' => EstadoVerificacionCuenta::RECHAZADA,
        ]);

        $this->service->rechazarCuenta($cuenta, reenviar: true);

        expect(NotificacionPush::query()->where('empresa_id', $this->empresa->id)->exists())->toBeFalse();
        Queue::assertNotPushed(EnviarNotificacionPushJob::class);
    });

});

describe('Casos edge de notificaciones', function (): void {

    test('no falla si cuenta no tiene colaborador', function (): void {
        $this->empresa->notificacionesIncluidas()->attach(2);

        $cuenta = CuentaBancaria::query()->create([
            'numero' => '1234567890123456',
            'banco_id' => $this->banco->id,
            'colaborador_id' => null,
            'user_id' => $this->user->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
        ]);

        $this->service->validarCuenta($cuenta);

        expect(NotificacionPush::query()->count())->toBe(0);
        Queue::assertNotPushed(EnviarNotificacionPushJob::class);
    });

    test('notificación contiene últimos 4 dígitos de la cuenta', function (): void {
        $this->empresa->notificacionesIncluidas()->attach(2);

        $cuenta = CuentaBancaria::query()->create([
            'numero' => 'CLABE12345678901234',
            'banco_id' => $this->banco->id,
            'colaborador_id' => $this->colaborador->id,
            'user_id' => $this->user->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
        ]);

        $this->service->validarCuenta($cuenta);

        $notificacion = NotificacionPush::query()->first();
        expect($notificacion->mensaje)->toContain('1234');
    });

    test('destinatario es solo el colaborador dueño de la cuenta', function (): void {
        $this->empresa->notificacionesIncluidas()->attach(2);

        $cuenta = CuentaBancaria::query()->create([
            'numero' => '1234567890123456',
            'banco_id' => $this->banco->id,
            'colaborador_id' => $this->colaborador->id,
            'user_id' => $this->user->id,
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
        ]);

        $this->service->validarCuenta($cuenta);

        $notificacion = NotificacionPush::query()->first();
        $filtros = $notificacion->filtros;

        expect($filtros['destinatarios']['select_all'])->toBeFalse()
            ->and($filtros['destinatarios']['manual_activation'])->toContain($this->colaborador->id)
            ->and($notificacion->total_destinatarios)->toBe(1);
    });

});
