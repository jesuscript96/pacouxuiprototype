<?php

declare(strict_types=1);

use App\Models\Colaborador;
use App\Models\ConfiguracionApp;
use App\Models\Empresa;
use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use App\Models\User;
use App\Services\Pumble\PumbleNotificationService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

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

    $this->notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'titulo' => 'Aviso importante',
        'mensaje' => 'Cuerpo del mensaje de prueba.',
        'url' => 'https://ejemplo.test/documento/1',
    ]);
});

/**
 * @return list<int> User IDs
 */
function pumbleCrearColaboradoresConUsuario(Empresa $empresa, int $cantidad): array
{
    $userIds = [];
    for ($i = 0; $i < $cantidad; $i++) {
        $colaborador = Colaborador::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Nombre'.$i,
            'apellido_paterno' => 'Apellido'.$i,
        ]);
        $user = User::factory()->colaborador()->create([
            'empresa_id' => $empresa->id,
            'colaborador_id' => $colaborador->id,
            'email' => "user{$i}_".fake()->unique()->safeEmail(),
        ]);
        $userIds[] = $user->id;
    }

    return $userIds;
}

function pumbleCrearFilasDestinatarios(NotificacionPush $notificacion, array $userIds): void
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

test('puede formatear mensaje correctamente', function (): void {
    $ids = pumbleCrearColaboradoresConUsuario($this->empresa, 2);
    pumbleCrearFilasDestinatarios($this->notificacion, $ids);

    $this->notificacion->refresh()->load(['empresa', 'destinatarios.user.colaborador']);

    $payload = app(PumbleNotificationService::class)->construirPayloadParaPruebas($this->notificacion);

    expect($payload['text'])
        ->toContain('Nueva Notificación Push Enviada')
        ->and($payload['text'])->toContain($this->empresa->nombre);

    expect($payload['attachments'][0]['title'])->toBe('Aviso importante')
        ->and($payload['attachments'][0]['text'])->toBe('Cuerpo del mensaje de prueba.')
        ->and($payload['attachments'][0]['footer'])->toBe('Notificación Push #'.$this->notificacion->id);

    expect($payload['attachments'][1]['pretext'])->toContain('2 colaboradores');
});

test('trunca destinatarios cuando son muchos', function (): void {
    $ids = pumbleCrearColaboradoresConUsuario($this->empresa, 25);
    pumbleCrearFilasDestinatarios($this->notificacion, $ids);

    $this->notificacion->refresh()->load(['empresa', 'destinatarios.user.colaborador']);

    $payload = app(PumbleNotificationService::class)->construirPayloadParaPruebas($this->notificacion);

    $textoDest = $payload['attachments'][1]['text'];
    expect(substr_count($textoDest, '• '))->toBe(20)
        ->and($textoDest)->toContain('…y 5 más');
});

test('no envia si pumble deshabilitado', function (): void {
    Config::set('services.pumble.enabled', false);
    Config::set('services.pumble.webhook_url', 'https://hooks.pumble.test/webhook');

    Http::fake();

    $ids = pumbleCrearColaboradoresConUsuario($this->empresa, 1);
    pumbleCrearFilasDestinatarios($this->notificacion, $ids);

    $ok = app(PumbleNotificationService::class)->enviarNotificacionPush($this->notificacion->fresh());

    expect($ok)->toBeFalse();
    Http::assertNothingSent();
});

test('maneja error de conexion sin romper', function (): void {
    Config::set('services.pumble.enabled', true);
    Config::set('services.pumble.webhook_url', 'https://hooks.pumble.test/webhook');

    Http::fake([
        'https://hooks.pumble.test/*' => function (): never {
            throw new \RuntimeException('fallo de red simulado');
        },
    ]);

    $ids = pumbleCrearColaboradoresConUsuario($this->empresa, 1);
    pumbleCrearFilasDestinatarios($this->notificacion, $ids);

    $ok = app(PumbleNotificationService::class)->enviarNotificacionPush($this->notificacion->fresh());

    expect($ok)->toBeFalse();
});

test('incluye todos los campos requeridos', function (): void {
    $ids = pumbleCrearColaboradoresConUsuario($this->empresa, 1);
    pumbleCrearFilasDestinatarios($this->notificacion, $ids);

    $this->notificacion->refresh()->load(['empresa', 'destinatarios.user.colaborador']);

    $payload = app(PumbleNotificationService::class)->construirPayloadParaPruebas($this->notificacion);

    expect($payload)->toHaveKeys(['text', 'attachments'])
        ->and($payload['attachments'])->toHaveCount(2);

    $a0 = $payload['attachments'][0];
    expect($a0)->toHaveKeys(['title', 'title_link', 'text', 'color', 'footer'])
        ->and($a0['color'])->toBe('#2563eb');

    $a1 = $payload['attachments'][1];
    expect($a1)->toHaveKeys(['pretext', 'text', 'color'])
        ->and($a1['color'])->toBe('#10b981')
        ->and($a1['title_link'] ?? null)->toBeNull();

    expect($a0['title_link'])->toBe('https://ejemplo.test/documento/1');
});
