<?php

declare(strict_types=1);

use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\NotificacionPush;
use App\Models\User;
use App\Services\NotificacionesPush\ResolverDestinatariosService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    $this->empresa = crearEmpresaMinima();
    $this->servicio = app(ResolverDestinatariosService::class);
});

test('resuelve todos los colaboradores cuando select_all es true sin exclusiones', function (): void {
    $colaborador1 = crearColaboradorConUsuarioPush($this->empresa);
    $colaborador2 = crearColaboradorConUsuarioPush($this->empresa);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [
            'destinatarios' => [
                'select_all' => true,
                'manual_activation' => [],
                'manual_deactivation' => [],
            ],
        ],
    ]);

    $ids = $this->servicio->resolverColaboradorIds($notificacion);

    expect($ids)->toHaveCount(2)
        ->and($ids->toArray())->toContain($colaborador1->id, $colaborador2->id);
});

test('excluye colaboradores en manual_deactivation cuando select_all es true', function (): void {
    $colaborador1 = crearColaboradorConUsuarioPush($this->empresa);
    $colaborador2 = crearColaboradorConUsuarioPush($this->empresa);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [
            'destinatarios' => [
                'select_all' => true,
                'manual_activation' => [],
                'manual_deactivation' => [$colaborador2->id],
            ],
        ],
    ]);

    $ids = $this->servicio->resolverColaboradorIds($notificacion);

    expect($ids)->toHaveCount(1)
        ->and($ids->first())->toBe($colaborador1->id);
});

test('solo incluye manual_activation cuando select_all es false', function (): void {
    $colaborador1 = crearColaboradorConUsuarioPush($this->empresa);
    $colaborador2 = crearColaboradorConUsuarioPush($this->empresa);
    $colaborador3 = crearColaboradorConUsuarioPush($this->empresa);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [
            'destinatarios' => [
                'select_all' => false,
                'manual_activation' => [$colaborador1->id, $colaborador3->id],
                'manual_deactivation' => [],
            ],
        ],
    ]);

    $ids = $this->servicio->resolverColaboradorIds($notificacion);

    expect($ids)->toHaveCount(2)
        ->and($ids->toArray())->toContain($colaborador1->id, $colaborador3->id)
        ->and($ids->toArray())->not->toContain($colaborador2->id);
});

test('persistirDestinatarios crea registros en el pivote', function (): void {
    crearColaboradorConUsuarioPush($this->empresa);
    crearColaboradorConUsuarioPush($this->empresa);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [
            'destinatarios' => ['select_all' => true],
        ],
    ]);

    $total = $this->servicio->persistirDestinatarios($notificacion);

    $notificacion->refresh()->load('destinatarios');

    expect($total)->toBe(2)
        ->and($notificacion->total_destinatarios)->toBe(2)
        ->and($notificacion->destinatarios)->toHaveCount(2);

    $dest = $notificacion->destinatarios->first();
    expect($dest->estado_lectura)->toBe('NO_LEIDA')
        ->and($dest->enviado)->toBeFalse();
});

test('persistirDestinatarios actualiza correctamente con sync', function (): void {
    crearColaboradorConUsuarioPush($this->empresa);
    $colaborador2 = crearColaboradorConUsuarioPush($this->empresa);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [
            'destinatarios' => ['select_all' => true],
        ],
    ]);

    $this->servicio->persistirDestinatarios($notificacion);
    expect($notificacion->fresh()->destinatarios)->toHaveCount(2);

    $notificacion->update([
        'filtros' => [
            'destinatarios' => [
                'select_all' => true,
                'manual_deactivation' => [$colaborador2->id],
            ],
        ],
    ]);

    $total = $this->servicio->persistirDestinatarios($notificacion->fresh());

    expect($total)->toBe(1)
        ->and($notificacion->fresh()->destinatarios)->toHaveCount(1);
});

test('recalcularDestinatarios preserva estado de lectura', function (): void {
    $colaborador1 = crearColaboradorConUsuarioPush($this->empresa);
    crearColaboradorConUsuarioPush($this->empresa);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => ['destinatarios' => ['select_all' => true]],
    ]);

    $this->servicio->persistirDestinatarios($notificacion);

    $notificacion->destinatarios()
        ->where('user_id', $colaborador1->user->id)
        ->update(['estado_lectura' => 'LEIDA', 'leida_at' => now()]);

    crearColaboradorConUsuarioPush($this->empresa);

    $this->servicio->recalcularDestinatarios($notificacion->fresh());

    $destColaborador1 = $notificacion->fresh()
        ->destinatarios()
        ->where('user_id', $colaborador1->user->id)
        ->first();

    expect($destColaborador1)->not->toBeNull()
        ->and($destColaborador1->estado_lectura)->toBe('LEIDA')
        ->and($notificacion->fresh()->destinatarios)->toHaveCount(3);
});

test('retorna cero cuando no hay destinatarios', function (): void {
    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => ['destinatarios' => ['select_all' => true]],
    ]);

    $total = $this->servicio->persistirDestinatarios($notificacion);

    expect($total)->toBe(0);
});

function crearColaboradorConUsuarioPush(Empresa $empresa, array $overrides = []): Colaborador
{
    $colaborador = Colaborador::factory()->create(array_merge(
        ['empresa_id' => $empresa->id],
        $overrides
    ));

    User::factory()->colaborador()->create([
        'colaborador_id' => $colaborador->id,
        'empresa_id' => $empresa->id,
        'email' => fake()->unique()->safeEmail(),
    ]);

    return $colaborador;
}
