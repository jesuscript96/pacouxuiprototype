<?php

declare(strict_types=1);

use App\Actions\NotificacionesPush\EnviarNotificacionPushAction;
use App\Enums\EstadoNotificacionPush;
use App\Jobs\EnviarNotificacionPushJob;
use App\Models\Colaborador;
use App\Models\ConfiguracionApp;
use App\Models\Empresa;
use App\Models\NotificacionPush;
use App\Models\User;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);

    $this->configuracionApp = ConfiguracionApp::query()->create([
        'nombre_app' => 'Test App',
        'android_app_id' => 'com.test.app',
        'ios_app_id' => 'com.test.app',
        'one_signal_app_id' => 'test-app-id',
        'one_signal_rest_api_key' => 'test-api-key',
    ]);

    $this->empresa = Empresa::factory()->create([
        'configuracion_app_id' => $this->configuracionApp->id,
    ]);
    $this->action = app(EnviarNotificacionPushAction::class);
});

test('enviar ahora encola el job', function (): void {
    Queue::fake();

    $colaborador = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    User::factory()->colaborador()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $colaborador->id,
        'email' => fake()->unique()->safeEmail(),
    ]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::BORRADOR,
    ]);

    $resultado = $this->action->enviarAhora($notificacion);

    expect($resultado['success'])->toBeTrue();
    Queue::assertPushed(EnviarNotificacionPushJob::class);
});

test('no puede enviar notificación ya enviada', function (): void {
    $notificacion = NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $resultado = $this->action->enviarAhora($notificacion);

    expect($resultado['success'])->toBeFalse()
        ->and($resultado['message'])->toContain('no puede enviarse');
});

test('programar actualiza estado y fecha', function (): void {
    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::BORRADOR,
    ]);

    $fechaFutura = now()->addDay();
    $resultado = $this->action->programar($notificacion, $fechaFutura);

    expect($resultado['success'])->toBeTrue();

    $notificacion->refresh();

    expect($notificacion->estado)->toBe(EstadoNotificacionPush::PROGRAMADA)
        ->and($notificacion->programada_para->toDateString())->toBe($fechaFutura->toDateString());
});

test('no puede programar para fecha pasada', function (): void {
    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $fechaPasada = now()->subHour();
    $resultado = $this->action->programar($notificacion, $fechaPasada);

    expect($resultado['success'])->toBeFalse()
        ->and($resultado['message'])->toContain('futuro');
});

test('cancelar cambia estado a cancelada', function (): void {
    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::BORRADOR,
    ]);

    $resultado = $this->action->cancelar($notificacion);

    expect($resultado['success'])->toBeTrue()
        ->and($notificacion->fresh()->estado)->toBe(EstadoNotificacionPush::CANCELADA);
});
