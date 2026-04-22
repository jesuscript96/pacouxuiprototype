<?php

declare(strict_types=1);

use App\Models\Colaborador;
use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use App\Models\User;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

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
});

test('obtiene lista de notificaciones del colaborador', function (): void {
    $notificacion = NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $notificacion->id,
        'user_id' => $this->user->id,
        'enviado' => true,
        'estado_lectura' => 'NO_LEIDA',
    ]);

    Sanctum::actingAs($this->user);

    $response = $this->getJson('/api/notificaciones-push');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $notificacion->id)
        ->assertJsonPath('data.0.leida', false);
});

test('filtra notificaciones por estado de lectura', function (): void {
    $notificacion1 = NotificacionPush::factory()->enviada()->create(['empresa_id' => $this->empresa->id]);
    $notificacion2 = NotificacionPush::factory()->enviada()->create(['empresa_id' => $this->empresa->id]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $notificacion1->id,
        'user_id' => $this->user->id,
        'enviado' => true,
        'estado_lectura' => 'NO_LEIDA',
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $notificacion2->id,
        'user_id' => $this->user->id,
        'enviado' => true,
        'estado_lectura' => 'LEIDA',
    ]);

    Sanctum::actingAs($this->user);

    $this->getJson('/api/notificaciones-push?estado=no_leidas')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->getJson('/api/notificaciones-push?estado=leidas')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->getJson('/api/notificaciones-push?estado=todas')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('obtiene conteo de no leídas', function (): void {
    $notificacion1 = NotificacionPush::factory()->enviada()->create(['empresa_id' => $this->empresa->id]);
    $notificacion2 = NotificacionPush::factory()->enviada()->create(['empresa_id' => $this->empresa->id]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $notificacion1->id,
        'user_id' => $this->user->id,
        'enviado' => true,
        'estado_lectura' => 'NO_LEIDA',
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $notificacion2->id,
        'user_id' => $this->user->id,
        'enviado' => true,
        'estado_lectura' => 'LEIDA',
    ]);

    Sanctum::actingAs($this->user);

    $this->getJson('/api/notificaciones-push/no-leidas/count')
        ->assertOk()
        ->assertJsonPath('count', 1);
});

test('obtiene una notificación específica', function (): void {
    $notificacion = NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
        'titulo' => 'Título de prueba',
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $notificacion->id,
        'user_id' => $this->user->id,
        'enviado' => true,
    ]);

    Sanctum::actingAs($this->user);

    $this->getJson("/api/notificaciones-push/{$notificacion->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $notificacion->id)
        ->assertJsonPath('data.titulo', 'Título de prueba');
});

test('marca notificación como leída', function (): void {
    $notificacion = NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $destinatario = NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $notificacion->id,
        'user_id' => $this->user->id,
        'enviado' => true,
        'estado_lectura' => 'NO_LEIDA',
    ]);

    Sanctum::actingAs($this->user);

    $this->postJson("/api/notificaciones-push/{$notificacion->id}/leer")
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($destinatario->fresh())
        ->estado_lectura->toBe('LEIDA')
        ->and($destinatario->fresh()->leida_at)->not->toBeNull();
});

test('marca todas las notificaciones como leídas', function (): void {
    $notificacion1 = NotificacionPush::factory()->enviada()->create(['empresa_id' => $this->empresa->id]);
    $notificacion2 = NotificacionPush::factory()->enviada()->create(['empresa_id' => $this->empresa->id]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $notificacion1->id,
        'user_id' => $this->user->id,
        'enviado' => true,
        'estado_lectura' => 'NO_LEIDA',
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $notificacion2->id,
        'user_id' => $this->user->id,
        'enviado' => true,
        'estado_lectura' => 'NO_LEIDA',
    ]);

    Sanctum::actingAs($this->user);

    $this->postJson('/api/notificaciones-push/leer-todas')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('count', 2);

    $noLeidas = NotificacionPushDestinatario::query()
        ->where('user_id', $this->user->id)
        ->where('estado_lectura', 'NO_LEIDA')
        ->count();

    expect($noLeidas)->toBe(0);
});

test('no puede ver notificaciones de otro colaborador', function (): void {
    $otroColaborador = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    $otroUser = User::factory()->colaborador()->create([
        'colaborador_id' => $otroColaborador->id,
        'empresa_id' => $this->empresa->id,
        'email' => fake()->unique()->safeEmail(),
    ]);

    $notificacion = NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $notificacion->id,
        'user_id' => $otroUser->id,
        'enviado' => true,
    ]);

    Sanctum::actingAs($this->user);

    $this->getJson('/api/notificaciones-push')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->getJson("/api/notificaciones-push/{$notificacion->id}")
        ->assertNotFound();
});

test('requiere autenticación', function (): void {
    $this->getJson('/api/notificaciones-push')
        ->assertUnauthorized();

    $this->getJson('/api/notificaciones-push/no-leidas/count')
        ->assertUnauthorized();

    $this->postJson('/api/notificaciones-push/1/leer')
        ->assertUnauthorized();
});
