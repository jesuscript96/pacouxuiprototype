<?php

declare(strict_types=1);

use App\Models\Colaborador;
use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use App\Models\User;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    $this->empresa = crearEmpresaMinima();

    $this->notificacion = NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
    ]);
});

test('getEstadisticasLectura retorna estadísticas correctas', function (): void {
    $colaborador1 = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    $user1 = User::factory()->colaborador()->create(['empresa_id' => $this->empresa->id, 'colaborador_id' => $colaborador1->id, 'email' => fake()->unique()->safeEmail()]);
    $colaborador2 = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    $user2 = User::factory()->colaborador()->create(['empresa_id' => $this->empresa->id, 'colaborador_id' => $colaborador2->id, 'email' => fake()->unique()->safeEmail()]);
    $colaborador3 = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    $user3 = User::factory()->colaborador()->create(['empresa_id' => $this->empresa->id, 'colaborador_id' => $colaborador3->id, 'email' => fake()->unique()->safeEmail()]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $user1->id,
        'enviado' => true,
        'estado_lectura' => 'LEIDA',
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $user2->id,
        'enviado' => true,
        'estado_lectura' => 'LEIDA',
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $user3->id,
        'enviado' => true,
        'estado_lectura' => 'NO_LEIDA',
    ]);

    $stats = $this->notificacion->getEstadisticasLectura();

    expect($stats['total_destinatarios'])->toBe(3)
        ->and($stats['enviados'])->toBe(3)
        ->and($stats['pendientes_envio'])->toBe(0)
        ->and($stats['leidas'])->toBe(2)
        ->and($stats['no_leidas'])->toBe(1);

    expect((float) $stats['porcentaje_lectura'])->toEqualWithDelta(66.7, 0.05);
});

test('porcentaje_lectura attribute funciona', function (): void {
    $colaborador1 = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    $user1 = User::factory()->colaborador()->create(['empresa_id' => $this->empresa->id, 'colaborador_id' => $colaborador1->id, 'email' => fake()->unique()->safeEmail()]);
    $colaborador2 = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    $user2 = User::factory()->colaborador()->create(['empresa_id' => $this->empresa->id, 'colaborador_id' => $colaborador2->id, 'email' => fake()->unique()->safeEmail()]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $user1->id,
        'enviado' => true,
        'estado_lectura' => 'LEIDA',
    ]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $user2->id,
        'enviado' => true,
        'estado_lectura' => 'NO_LEIDA',
    ]);

    expect((float) $this->notificacion->fresh()->porcentaje_lectura)->toEqualWithDelta(50.0, 0.01);
});

test('estadísticas con cero enviados retorna porcentaje cero', function (): void {
    $colaborador = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    $user = User::factory()->colaborador()->create(['empresa_id' => $this->empresa->id, 'colaborador_id' => $colaborador->id, 'email' => fake()->unique()->safeEmail()]);

    NotificacionPushDestinatario::query()->create([
        'notificacion_push_id' => $this->notificacion->id,
        'user_id' => $user->id,
        'enviado' => false,
        'estado_lectura' => 'NO_LEIDA',
    ]);

    $stats = $this->notificacion->getEstadisticasLectura();

    expect($stats['total_destinatarios'])->toBe(1)
        ->and($stats['enviados'])->toBe(0)
        ->and($stats['pendientes_envio'])->toBe(1)
        ->and((float) $stats['porcentaje_lectura'])->toEqualWithDelta(0.0, 0.01);
});
