<?php

declare(strict_types=1);

use App\Enums\EstadoNotificacionPush;
use App\Models\Empresa;
use App\Models\NotificacionPush;
use App\Models\User;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);

    $this->empresa = Empresa::factory()->create();
    $this->user = User::factory()->create(['empresa_id' => $this->empresa->id]);
});

test('puede crear notificación push', function (): void {
    $notificacion = NotificacionPush::query()->create([
        'empresa_id' => $this->empresa->id,
        'titulo' => 'Título de prueba',
        'mensaje' => 'Mensaje de prueba',
        'creado_por' => $this->user->id,
    ]);
    $notificacion->refresh();

    expect($notificacion)->toBeInstanceOf(NotificacionPush::class)
        ->and($notificacion->estado)->toBe(EstadoNotificacionPush::BORRADOR)
        ->and($notificacion->titulo)->toBe('Título de prueba');
});

test('estado borrador es editable', function (): void {
    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::BORRADOR,
    ]);

    expect($notificacion->esEditable())->toBeTrue()
        ->and($notificacion->puedeEnviarse())->toBeTrue();
});

test('estado enviada no es editable', function (): void {
    $notificacion = NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    expect($notificacion->esEditable())->toBeFalse()
        ->and($notificacion->puedeEnviarse())->toBeFalse();
});

test('puede programar notificación', function (): void {
    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $fechaProgramada = now()->addDay();
    $notificacion->programarPara($fechaProgramada);

    expect($notificacion->fresh()->estado)->toBe(EstadoNotificacionPush::PROGRAMADA)
        ->and($notificacion->fresh()->programada_para->toDateString())->toBe($fechaProgramada->toDateString());
});

test('puede cancelar notificación en borrador', function (): void {
    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::BORRADOR,
    ]);

    $notificacion->cancelar();

    expect($notificacion->fresh()->estado)->toBe(EstadoNotificacionPush::CANCELADA);
});

test('no puede cancelar notificación ya enviada', function (): void {
    $notificacion = NotificacionPush::factory()->enviada()->create([
        'empresa_id' => $this->empresa->id,
    ]);

    $notificacion->cancelar();

    expect($notificacion->fresh()->estado)->toBe(EstadoNotificacionPush::ENVIADA);
});

test('scope pendientes de envio funciona', function (): void {
    NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::BORRADOR,
    ]);

    NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::PROGRAMADA,
        'programada_para' => now()->subHour(),
    ]);

    NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'estado' => EstadoNotificacionPush::PROGRAMADA,
        'programada_para' => now()->addHour(),
    ]);

    $pendientes = NotificacionPush::query()->pendientesDeEnvio()->get();

    expect($pendientes)->toHaveCount(1);
});

test('filtros se guardan como JSON', function (): void {
    $filtros = [
        'ubicaciones' => [1, 2, 3],
        'departamentos' => [5],
        'generos' => ['M'],
    ];

    $notificacion = NotificacionPush::factory()->conFiltros($filtros)->create([
        'empresa_id' => $this->empresa->id,
    ]);

    expect($notificacion->filtros)->toBeArray()
        ->and($notificacion->filtros['ubicaciones'])->toBe([1, 2, 3])
        ->and($notificacion->filtros['generos'])->toBe(['M']);
});
