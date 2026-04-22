<?php

declare(strict_types=1);

use App\Models\Colaborador;
use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use App\Models\User;
use Database\Seeders\Inicial;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    $this->empresa = crearEmpresaMinima();

    $this->colaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $this->user = User::factory()->colaborador()->create([
        'colaborador_id' => $this->colaborador->id,
        'empresa_id' => $this->empresa->id,
        'email' => fake()->unique()->safeEmail(),
    ]);

    $this->notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);
});

test('puede crear destinatario de notificación', function (): void {
    $destinatario = NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $this->user->id,
    ]);

    expect($destinatario)->toBeInstanceOf(NotificacionPushDestinatario::class);
    expect($destinatario->fresh())
        ->estado_lectura->toBe('NO_LEIDA')
        ->enviado->toBeFalse();
});

test('puede marcar como leída', function (): void {
    $destinatario = NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $this->user->id,
    ]);

    $destinatario->marcarComoLeida();

    expect($destinatario->fresh())
        ->estado_lectura->toBe('LEIDA')
        ->leida_at->not->toBeNull();
});

test('puede marcar como enviado', function (): void {
    $destinatario = NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $this->user->id,
    ]);

    $destinatario->marcarComoEnviado('player-id-123');

    expect($destinatario->fresh())
        ->enviado->toBeTrue()
        ->onesignal_player_id->toBe('player-id-123')
        ->enviado_at->not->toBeNull();
});

test('notificación tiene relación con destinatarios', function (): void {
    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $this->user->id,
    ]);

    expect($this->notificacion->destinatarios)->toHaveCount(1)
        ->and($this->notificacion->usersDestinatarios)->toHaveCount(1);
});

test('usuario tiene relación con notificaciones recibidas', function (): void {
    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $this->user->id,
    ]);

    expect($this->user->notificacionesPushRecibidas)->toHaveCount(1);
});

test('no permite duplicar usuario en misma notificación', function (): void {
    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $this->user->id,
    ]);

    expect(fn () => NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $this->user->id,
    ]))->toThrow(QueryException::class);
});

test('métodos helper de conteo funcionan', function (): void {
    $colaborador2 = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    $user2 = User::factory()->colaborador()->create([
        'empresa_id' => $this->empresa->id,
        'colaborador_id' => $colaborador2->id,
        'email' => fake()->unique()->safeEmail(),
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $this->user->id,
        'estado_lectura' => 'LEIDA',
        'enviado' => true,
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $user2->id,
        'estado_lectura' => 'NO_LEIDA',
        'enviado' => false,
    ]);

    expect($this->notificacion->cantidadDestinatarios())->toBe(2)
        ->and($this->notificacion->cantidadLeidas())->toBe(1)
        ->and($this->notificacion->cantidadEnviados())->toBe(1);
});
