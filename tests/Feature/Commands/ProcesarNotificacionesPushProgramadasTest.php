<?php

declare(strict_types=1);

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

    Queue::fake();

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

    $colaborador = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    User::factory()->colaborador()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $colaborador->id,
        'email' => fake()->unique()->safeEmail(),
    ]);
});

test('comando procesa notificaciones programadas cuya fecha ya pasó', function (): void {
    $notificacionPendiente = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::PROGRAMADA,
        'programada_para' => now()->subHour(),
    ]);

    $notificacionFutura = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::PROGRAMADA,
        'programada_para' => now()->addDay(),
    ]);

    $this->artisan('notificaciones:enviar-programadas')
        ->assertSuccessful();

    Queue::assertPushed(EnviarNotificacionPushJob::class, 1);
    Queue::assertPushed(EnviarNotificacionPushJob::class, function (EnviarNotificacionPushJob $job) use ($notificacionPendiente): bool {
        return $job->notificacion->id === $notificacionPendiente->id;
    });

    expect($notificacionPendiente->fresh()->estado)->toBe(EstadoNotificacionPush::ENVIANDO)
        ->and($notificacionFutura->fresh()->estado)->toBe(EstadoNotificacionPush::PROGRAMADA);
});

test('comando no procesa notificaciones en borrador', function (): void {
    NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::BORRADOR,
        'programada_para' => now()->subHour(),
    ]);

    $this->artisan('notificaciones:enviar-programadas')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

test('comando no procesa notificaciones ya enviadas', function (): void {
    NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
        'programada_para' => now()->subHour(),
    ]);

    $this->artisan('notificaciones:enviar-programadas')
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

test('comando respeta el límite de notificaciones', function (): void {
    NotificacionPush::factory()->count(10)->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::PROGRAMADA,
        'programada_para' => now()->subHour(),
    ]);

    $this->artisan('notificaciones:enviar-programadas', ['--limit' => 3])
        ->assertSuccessful();

    Queue::assertPushed(EnviarNotificacionPushJob::class, 3);
});

test('comando dry-run no despacha jobs', function (): void {
    NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::PROGRAMADA,
        'programada_para' => now()->subHour(),
    ]);

    $this->artisan('notificaciones:enviar-programadas', ['--dry-run' => true])
        ->assertSuccessful();

    Queue::assertNothingPushed();
});

test('comando marca como fallida si empresa no tiene OneSignal', function (): void {
    $empresaSinOneSignal = Empresa::factory()->create([
        'configuracion_app_id' => null,
    ]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $empresaSinOneSignal->id,
        'estado' => EstadoNotificacionPush::PROGRAMADA,
        'programada_para' => now()->subHour(),
    ]);

    $this->artisan('notificaciones:enviar-programadas')
        ->assertFailed();

    Queue::assertNothingPushed();
    expect($notificacion->fresh()->estado)->toBe(EstadoNotificacionPush::FALLIDA);
});

test('comando muestra mensaje cuando no hay pendientes', function (): void {
    $this->artisan('notificaciones:enviar-programadas')
        ->expectsOutput('No hay notificaciones programadas pendientes.')
        ->assertSuccessful();
});
