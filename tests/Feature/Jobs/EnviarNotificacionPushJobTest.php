<?php

declare(strict_types=1);

use App\Enums\EstadoNotificacionPush;
use App\Jobs\EnviarNotificacionPushJob;
use App\Models\Colaborador;
use App\Models\ConfiguracionApp;
use App\Models\Empresa;
use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use App\Models\NotificacionPushEnvio;
use App\Models\User;
use App\Services\OneSignal\OneSignalService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);

    $this->app->forgetInstance(OneSignalService::class);

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

    $this->notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::BORRADOR,
    ]);
});

afterEach(function (): void {
    \Mockery::close();
});

/**
 * @return list<int> User IDs
 */
function crearColaboradoresConUsuarioParaPivote(Empresa $empresa, int $cantidad): array
{
    $userIds = [];
    for ($i = 0; $i < $cantidad; $i++) {
        $colaborador = Colaborador::factory()->create(['empresa_id' => $empresa->id]);
        $user = User::factory()->colaborador()->create([
            'empresa_id' => $empresa->id,
            'colaborador_id' => $colaborador->id,
            'email' => fake()->unique()->safeEmail(),
        ]);
        $userIds[] = $user->id;
    }

    return $userIds;
}

function crearFilasPivote(NotificacionPush $notificacion, array $userIds): void
{
    $now = now();
    $rows = collect($userIds)->map(fn (int $id): array => [
        'notificacion_push_id' => $notificacion->id,
        'user_id' => $id,
        'estado_lectura' => 'NO_LEIDA',
        'leida_at' => null,
        'enviado' => false,
        'onesignal_player_id' => null,
        'enviado_at' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ])->all();

    NotificacionPushDestinatario::query()->insert($rows);
}

test('job se encola correctamente', function (): void {
    Queue::fake();

    EnviarNotificacionPushJob::dispatch($this->notificacion);

    Queue::assertPushed(EnviarNotificacionPushJob::class, function (EnviarNotificacionPushJob $job): bool {
        return $job->notificacion->id === $this->notificacion->id;
    });
});

test('job se encola en cola notificaciones', function (): void {
    Queue::fake();

    EnviarNotificacionPushJob::dispatch($this->notificacion);

    Queue::assertPushedOn('notificaciones', EnviarNotificacionPushJob::class);
});

test('job no procesa notificación ya enviada', function (): void {
    $notificacion = NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $job = new EnviarNotificacionPushJob($notificacion);

    $mockOneSignal = \Mockery::mock(OneSignalService::class);
    $mockOneSignal->shouldNotReceive('paraEmpresa');

    $job->handle($mockOneSignal);

    expect($notificacion->fresh()->estado)->toBe(EstadoNotificacionPush::ENVIADA);
});

test('job marca notificación como enviada al enviar a pivote', function (): void {
    $ids = crearColaboradoresConUsuarioParaPivote($this->empresa, 2);
    crearFilasPivote($this->notificacion, $ids);

    $oneSignalService = app(OneSignalService::class);
    $oneSignalService->paraEmpresa($this->empresa)->simular();

    $job = new EnviarNotificacionPushJob($this->notificacion);
    $job->handle($oneSignalService);

    expect($this->notificacion->fresh()->estado)->toBe(EstadoNotificacionPush::ENVIADA);
});

test('job crea registros de envío por chunk', function (): void {
    $ids = crearColaboradoresConUsuarioParaPivote($this->empresa, 3);
    crearFilasPivote($this->notificacion, $ids);

    $oneSignalService = app(OneSignalService::class);
    $oneSignalService->paraEmpresa($this->empresa)->simular();

    $job = new EnviarNotificacionPushJob($this->notificacion);
    $job->handle($oneSignalService);

    expect(NotificacionPushEnvio::query()->where('notificacion_push_id', $this->notificacion->id)->count())
        ->toBe(1);
});

test('job actualiza estadísticas de envío', function (): void {
    $ids = crearColaboradoresConUsuarioParaPivote($this->empresa, 3);
    crearFilasPivote($this->notificacion, $ids);

    $oneSignalService = app(OneSignalService::class);
    $oneSignalService->paraEmpresa($this->empresa)->simular();

    $job = new EnviarNotificacionPushJob($this->notificacion);
    $job->handle($oneSignalService);

    $notificacion = $this->notificacion->fresh();

    expect($notificacion->total_enviados)->toBe(3)
        ->and($notificacion->total_fallidos)->toBe(0)
        ->and($notificacion->enviada_at)->not->toBeNull();

    expect(NotificacionPushDestinatario::query()
        ->where('notificacion_push_id', $notificacion->id)
        ->where('enviado', true)
        ->count())->toBe(3);
});
